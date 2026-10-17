<?php

namespace App\Services\Media;

use App\Contracts\AuditLogger;
use App\Contracts\MediaService as MediaServiceContract;
use App\Models\Media;
use App\Models\Property;
use App\Models\User;
use App\Support\PropertyMediaAltTextGenerator;
use App\Support\TaggedCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Untouched uploads live on the private disk. Visitors only ever receive re-encoded
 * copies (metadata stripped) and the responsive WebP derivatives.
 */
class MediaService implements MediaServiceContract
{
    private const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    private const PUBLIC_DISK = 'public';

    private const PRIVATE_DISK = 'local';

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly PropertyMediaAltTextGenerator $propertyAltText,
    ) {}

    public function store(Model $owner, UploadedFile $file, string $collection, ?string $altText = null): Media
    {
        if (! in_array($collection, [...Media::IMAGE_COLLECTIONS, ...Media::DOCUMENT_COLLECTIONS], true)) {
            throw ValidationException::withMessages(['collection' => 'Unknown media collection.']);
        }

        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());

        return in_array($collection, Media::DOCUMENT_COLLECTIONS, true)
            ? $this->storeDocument($owner, $file, $collection, $mime, $altText)
            : $this->storeImage($owner, $file, $collection, $mime, $altText);
    }

    public function updateDetails(Media $media, ?string $altText, bool $isPublic, User $actor): Media
    {
        $altTexts = $media->alt_texts ?? [];
        $requestedAltText = filled($altText) ? trim($altText) : ($altTexts['en'] ?? null);

        if (blank($requestedAltText) && $media->mediable instanceof Property && in_array($media->collection, Media::IMAGE_COLLECTIONS, true)) {
            $requestedAltText = $this->propertyAltText->forMedia($media->mediable, $media);
        }

        $old = ['alt' => $media->alt(), 'is_public' => $media->is_public];

        $media->forceFill([
            'alt_texts' => $requestedAltText !== null ? [...$altTexts, 'en' => $requestedAltText] : $altTexts,
            'is_public' => $isPublic,
        ])->save();

        $this->auditLogger->record($actor->id, 'media.updated', Media::class, $media->id, $old, ['alt' => $requestedAltText, 'is_public' => $isPublic], request()->ip());
        $this->flushOwner($media);

        return $media;
    }

    public function completePropertyAltText(Property $property): int
    {
        $property->loadMissing(['propertyType', 'locationArea', 'media']);
        $completed = 0;

        foreach ($property->media as $media) {
            if (! in_array($media->collection, Media::IMAGE_COLLECTIONS, true) || $media->hasAltText()) {
                continue;
            }

            $media->forceFill([
                'alt_texts' => [...($media->alt_texts ?? []), 'en' => $this->propertyAltText->forMedia($property, $media)],
            ])->save();
            $completed++;
        }

        if ($completed > 0) {
            TaggedCache::flush(['properties', 'property:'.$property->id, 'homepage', 'sitemap', 'search']);
        }

        return $completed;
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(Model $owner, string $collection, array $orderedIds): void
    {
        DB::transaction(function () use ($owner, $collection, $orderedIds): void {
            foreach (array_values($orderedIds) as $index => $id) {
                $owner->media()
                    ->where('collection', $collection)
                    ->whereKey($id)
                    ->update(['sort_order' => $index]);
            }
        });

        TaggedCache::flush(['properties', 'projects', 'homepage']);
    }

    public function delete(Media $media, User $actor): void
    {
        DB::transaction(function () use ($media, $actor): void {
            $media->delete();
            $this->auditLogger->record($actor->id, 'media.deleted', Media::class, $media->id, ['path' => $media->path, 'collection' => $media->collection], null, request()->ip());
        });

        $this->cleanupDeletedFiles($media);
        $this->flushOwner($media);
    }

    public function cleanupDeletedFiles(Media $media): void
    {
        try {
            if (! $this->isPathReferenced($media->disk, $media->path)) {
                $paths = [$media->path];

                if (! $media->isDocument()) {
                    foreach (config('urbanhaven.media.derivative_widths', [480, 768, 1280, 1920]) as $width) {
                        $derivative = $media->derivativePath((int) $width);

                        if (! $this->isPathReferenced($media->disk, $derivative)) {
                            $paths[] = $derivative;
                        }
                    }
                }

                Storage::disk($media->disk)->delete($paths);
            }

            if (filled($media->original_path) && ! $this->isPathReferenced($media->original_disk, $media->original_path)) {
                Storage::disk($media->original_disk)->delete($media->original_path);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function storeImage(Model $owner, UploadedFile $file, string $collection, string $mime, ?string $altText): Media
    {
        $maxKb = (int) config('urbanhaven.media.max_image_kb', 8192);

        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages(['file' => 'The image must be '.$maxKb.' KB or smaller.']);
        }

        if (! in_array($mime, self::IMAGE_MIMES, true)) {
            throw ValidationException::withMessages(['file' => 'That file type is not allowed. Upload a JPEG, PNG, WebP or GIF image.']);
        }

        $directory = now()->format('Y/m');
        $basename = Str::uuid()->toString();
        $extension = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
        $originalPath = 'media-originals/'.$directory.'/'.$basename.'.'.$extension;
        $displayPath = 'media/'.$directory.'/'.$basename.'.jpg';
        $written = [];

        try {
            Storage::disk(self::PRIVATE_DISK)->put($originalPath, (string) file_get_contents($file->getRealPath()));
            $written[] = [self::PRIVATE_DISK, $originalPath];

            $manager = new ImageManager(new Driver);
            $image = $manager->read($file->getRealPath());
            $width = $image->width();
            $height = $image->height();

            Storage::disk(self::PUBLIC_DISK)->put($displayPath, (string) $image->scaleDown(width: 2400)->toJpeg(quality: 85));
            $written[] = [self::PUBLIC_DISK, $displayPath];

            foreach (config('urbanhaven.media.derivative_widths', [480, 768, 1280, 1920]) as $derivativeWidth) {
                $path = 'media/'.$directory.'/'.$basename.'-'.$derivativeWidth.'.webp';
                Storage::disk(self::PUBLIC_DISK)->put($path, (string) $manager->read($file->getRealPath())->scaleDown(width: (int) $derivativeWidth)->toWebp(quality: 82));
                $written[] = [self::PUBLIC_DISK, $path];
            }
        } catch (Throwable $exception) {
            $this->cleanup($written);

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            throw ValidationException::withMessages(['file' => 'We could not process that image. Try another file.']);
        }

        $altText = filled($altText) ? trim($altText) : null;

        try {
            $media = DB::transaction(function () use ($owner, $file, $collection, $mime, $altText, $originalPath, $displayPath, $width, $height): Media {
                $sortOrder = (int) $owner->media()->where('collection', $collection)->max('sort_order') + 1;

                return Media::query()->create([
                    'mediable_type' => $owner::class,
                    'mediable_id' => $owner->getKey(),
                    'collection' => $collection,
                    'disk' => self::PUBLIC_DISK,
                    'path' => $displayPath,
                    'original_disk' => self::PRIVATE_DISK,
                    'original_path' => $originalPath,
                    'original_filename' => mb_strimwidth($file->getClientOriginalName(), 0, 250),
                    'mime_type' => $mime,
                    'size_bytes' => $file->getSize(),
                    'checksum' => hash_file('sha256', $file->getRealPath()) ?: null,
                    'width' => $width,
                    'height' => $height,
                    'sort_order' => $sortOrder,
                    'is_public' => true,
                    'alt_texts' => filled($altText) ? ['en' => trim($altText)] : ($owner instanceof Property
                        ? ['en' => $this->propertyAltText->forUpload($owner, $collection, $sortOrder)]
                        : []),
                ]);
            });
        } catch (Throwable $exception) {
            $this->cleanup($written);

            throw $exception;
        }

        $this->flushOwner($media);

        return $media;
    }

    private function storeDocument(Model $owner, UploadedFile $file, string $collection, string $mime, ?string $title): Media
    {
        $maxKb = (int) config('urbanhaven.media.max_brochure_kb', 20480);

        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages(['file' => 'The brochure must be '.(int) ($maxKb / 1024).' MB or smaller.']);
        }

        if ($mime !== 'application/pdf') {
            throw ValidationException::withMessages(['file' => 'Brochures must be PDF files.']);
        }

        $path = 'brochures/'.now()->format('Y/m').'/'.Str::uuid()->toString().'.pdf';
        Storage::disk(self::PRIVATE_DISK)->put($path, (string) file_get_contents($file->getRealPath()));

        try {
            $media = Media::query()->create([
                'mediable_type' => $owner::class,
                'mediable_id' => $owner->getKey(),
                'collection' => $collection,
                'disk' => self::PRIVATE_DISK,
                'path' => $path,
                'original_disk' => self::PRIVATE_DISK,
                'original_path' => $path,
                'original_filename' => mb_strimwidth($file->getClientOriginalName(), 0, 250),
                'mime_type' => $mime,
                'size_bytes' => $file->getSize(),
                'checksum' => hash_file('sha256', $file->getRealPath()) ?: null,
                'sort_order' => (int) $owner->media()->where('collection', $collection)->max('sort_order') + 1,
                'is_public' => true,
                'alt_texts' => ['en' => filled($title) ? trim($title) : 'Brochure'],
            ]);
        } catch (Throwable $exception) {
            Storage::disk(self::PRIVATE_DISK)->delete($path);

            throw $exception;
        }

        $this->flushOwner($media);

        return $media;
    }

    /**
     * @param  list<array{0: string, 1: string}>  $written
     */
    private function cleanup(array $written): void
    {
        foreach ($written as [$disk, $path]) {
            Storage::disk($disk)->delete($path);
        }
    }

    private function flushOwner(Media $media): void
    {
        $type = strtolower(class_basename((string) $media->mediable_type));
        TaggedCache::flush([$type.'s', $type.':'.$media->mediable_id, 'homepage', 'sitemap']);
    }

    private function isPathReferenced(string $disk, string $path): bool
    {
        $referencedByMedia = Media::query()->where(fn ($query) => $query
            ->where(fn ($display) => $display->where('disk', $disk)->where('path', $path))
            ->orWhere(fn ($original) => $original->where('original_disk', $disk)->where('original_path', $path))
        )->exists();

        return $referencedByMedia || DB::table('seo_overrides')->where('og_image_path', $path)->exists();
    }
}
