<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\MediaService;
use App\Http\Controllers\Controller;
use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Models\Media;
use App\Models\Post;
use App\Models\Project;
use App\Models\Property;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MediaController extends Controller
{
    private const OWNERS = ['property' => Property::class, 'project' => Project::class, 'post' => Post::class, 'cms_page' => CmsPage::class, 'cms_block' => CmsBlock::class];

    public function store(Request $request, MediaService $media): JsonResponse|RedirectResponse
    {
        $maxKb = max((int) config('urbanhaven.media.max_image_kb'), (int) config('urbanhaven.media.max_brochure_kb'));
        $validated = $request->validate([
            'file' => ['required_without:files', 'file', 'max:'.$maxKb],
            'files' => ['required_without:file', 'array', 'max:20'],
            'files.*' => ['file', 'max:'.$maxKb],
            'files_meta' => ['sometimes', 'array', 'max:20'],
            'files_meta.*.alt_text' => ['nullable', 'string', 'max:200'],
            'files_meta.*.is_public' => ['sometimes', 'boolean'],
            'files_meta.*.make_cover' => ['sometimes', 'boolean'],
            'owner_type' => ['required', Rule::in(array_keys(self::OWNERS))],
            'owner_id' => ['required', 'integer'],
            'collection' => [
                'nullable',
                Rule::in($request->input('owner_type') === 'cms_block' ? ['gallery'] : [...Media::IMAGE_COLLECTIONS, ...Media::DOCUMENT_COLLECTIONS]),
            ],
            'alt_text' => ['nullable', 'string', 'max:200'],
        ]);

        $owner = $this->owner($validated['owner_type'], (int) $validated['owner_id']);
        $this->authorize('update', $owner);

        $files = collect($request->hasFile('file') ? [$request->file('file')] : $request->file('files'))->values();
        $metadata = $validated['files_meta'] ?? [];

        foreach ($files as $index => $file) {
            $itemMetadata = $metadata[$index] ?? null;
            if ($itemMetadata && ($itemMetadata['is_public'] ?? false) && blank($itemMetadata['alt_text'] ?? null)) {
                throw ValidationException::withMessages([
                    "files_meta.{$index}.alt_text" => 'Add alt text before making this image public.',
                ]);
            }
        }

        $records = $files->map(function (UploadedFile $file, int $index) use ($media, $owner, $validated, $metadata, $request): Media {
            $itemMetadata = $metadata[$index] ?? null;
            $record = $media->store(
                $owner,
                $file,
                $validated['collection'] ?? 'gallery',
                $itemMetadata['alt_text'] ?? ($validated['alt_text'] ?? null),
            );

            if ($itemMetadata !== null) {
                $media->updateDetails(
                    $record,
                    $itemMetadata['alt_text'] ?? null,
                    (bool) ($itemMetadata['is_public'] ?? false),
                    $request->user(),
                );

                if (! empty($itemMetadata['make_cover']) && $record->collection === 'gallery' && in_array('featured_media_id', $owner->getFillable(), true)) {
                    $owner->forceFill(['featured_media_id' => $record->id])->save();
                }
            }

            return $record;
        });
        $record = $records->first();

        if (! $request->expectsJson()) {
            $uploaded = $records->count() > 1 ? $records->count().' files uploaded.' : 'Uploaded.';

            return back()->with('status', $records->every(fn (Media $item): bool => $item->is_public) ? $uploaded : $uploaded.' Add alt text to make them public.');
        }

        return response()->json([
            'id' => $record->id,
            'url' => $record->isDocument() ? null : $record->url(),
            'thumb_url' => $record->isDocument() ? null : $record->thumbUrl(),
            'is_public' => $record->is_public,
            'records' => $records->map(fn (Media $item): array => [
                'id' => $item->id,
                'url' => $item->isDocument() ? null : $item->url(),
                'thumb_url' => $item->isDocument() ? null : $item->thumbUrl(),
                'is_public' => $item->is_public,
            ])->all(),
        ], 201);
    }

    public function update(Request $request, Media $medium, MediaService $media): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $medium->mediable);

        $validated = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:200'],
            'is_public' => ['required', 'boolean'],
            'make_cover' => ['sometimes', 'boolean'],
        ]);

        $media->updateDetails($medium, $validated['alt_text'] ?? null, (bool) $validated['is_public'], $request->user());

        if (! empty($validated['make_cover']) && $medium->collection === 'gallery' && in_array('featured_media_id', $medium->mediable->getFillable(), true)) {
            $medium->mediable->forceFill(['featured_media_id' => $medium->id])->save();
        }

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back()->with('status', 'Image details saved.');
    }

    public function batchUpdate(Request $request, MediaService $media): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'max:100'],
            'items.*.id' => ['required', 'integer', 'exists:media,id'],
            'items.*.alt_text' => ['nullable', 'string', 'max:200'],
            'items.*.is_public' => ['required', 'boolean'],
            'items.*.make_cover' => ['sometimes', 'boolean'],
        ]);

        $savedCount = 0;
        foreach ($validated['items'] as $itemData) {
            /** @var Media|null $medium */
            $medium = Media::query()->find($itemData['id']);
            if (! $medium) {
                continue;
            }

            $this->authorize('update', $medium->mediable);
            $media->updateDetails($medium, $itemData['alt_text'] ?? null, (bool) $itemData['is_public'], $request->user());

            if (! empty($itemData['make_cover']) && $medium->collection === 'gallery' && in_array('featured_media_id', $medium->mediable->getFillable(), true)) {
                $medium->mediable->forceFill(['featured_media_id' => $medium->id])->save();
            }

            $savedCount++;
        }

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'updated' => $savedCount])
            : back()->with('status', $savedCount === 1 ? 'Image details saved.' : "{$savedCount} image details saved.");
    }

    public function reorder(Request $request, MediaService $media): JsonResponse
    {
        $validated = $request->validate([
            'owner_type' => ['required', Rule::in(array_keys(self::OWNERS))],
            'owner_id' => ['required', 'integer'],
            'collection' => ['nullable', Rule::in(Media::IMAGE_COLLECTIONS)],
            'ordered_ids' => ['required', 'array', 'max:200'],
            'ordered_ids.*' => ['integer'],
        ]);
        $owner = $this->owner($validated['owner_type'], (int) $validated['owner_id']);
        $this->authorize('update', $owner);
        $media->reorder($owner, $validated['collection'] ?? 'gallery', $validated['ordered_ids']);

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, Media $medium, MediaService $media): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $medium->mediable);
        $media->delete($medium, $request->user());

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', 'File removed.');
    }

    private function owner(string $type, int $id): Model
    {
        $owner = self::OWNERS[$type]::query()->findOrFail($id);
        abort_if($owner instanceof CmsBlock && ! $owner->acceptsImage(), 403, 'This block does not take images.');

        return $owner;
    }
}
