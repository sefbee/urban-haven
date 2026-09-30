<?php

namespace App\Services\Media;

use App\Contracts\AuditLogger;
use App\Contracts\MediaService as MediaServiceContract;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaService implements MediaServiceContract
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function store(Model $owner, UploadedFile $file, string $collection): Media
    {
        $maxKb = (int) config('urbanhaven.media.max_image_kb', 8192);
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages([
                'file' => 'The image must be '.$maxKb.' KB or smaller.',
            ]);
        }

        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());

        if (! in_array($mime, $allowed, true) || str_contains($mime, 'php') || str_contains($mime, 'executable')) {
            throw ValidationException::withMessages([
                'file' => 'That file type is not allowed.',
            ]);
        }

        $disk = 'public';
        $directory = 'media/'.now()->format('Y/m');
        $basename = Str::uuid()->toString();
        $originalPath = $directory.'/'.$basename.'.'.($file->guessExtension() ?: 'jpg');

        $manager = new ImageManager(new Driver);
        $image = $manager->read($file->getRealPath());
        $encoded = $image->toJpeg(quality: 88);
        Storage::disk($disk)->put($originalPath, (string) $encoded);

        $widths = config('urbanhaven.media.derivative_widths', [480, 768, 1280, 1920]);

        foreach ($widths as $width) {
            $derivative = $manager->read($file->getRealPath())->scaleDown(width: (int) $width);
            Storage::disk($disk)->put(
                $directory.'/'.$basename.'-'.$width.'.webp',
                (string) $derivative->toWebp(quality: 82),
            );
        }

        try {
            return DB::transaction(function () use ($owner, $collection, $disk, $originalPath, $file, $mime, $image) {
                return Media::query()->create([
                    'mediable_type' => $owner::class,
                    'mediable_id' => $owner->getKey(),
                    'collection' => $collection,
                    'disk' => $disk,
                    'path' => $originalPath,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $mime,
                    'size_bytes' => Storage::disk($disk)->size($originalPath),
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'sort_order' => (int) $owner->media()->max('sort_order') + 1,
                    'alt_texts' => ['en' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)],
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk($disk)->delete($originalPath);
            foreach (config('urbanhaven.media.derivative_widths', []) as $width) {
                Storage::disk($disk)->delete($directory.'/'.$basename.'-'.$width.'.webp');
            }

            throw $e;
        }
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
    }

    public function delete(Media $media, User $actor): void
    {
        DB::transaction(function () use ($media, $actor): void {
            $paths = [$media->path];
            foreach (config('urbanhaven.media.derivative_widths', []) as $width) {
                $paths[] = $media->derivativePath((int) $width);
            }

            $media->delete();
            Storage::disk($media->disk)->delete($paths);

            $this->auditLogger->record(
                $actor->id,
                'media.deleted',
                Media::class,
                $media->id,
                ['path' => $media->path],
                null,
                request()->ip(),
            );
        });
    }
}
