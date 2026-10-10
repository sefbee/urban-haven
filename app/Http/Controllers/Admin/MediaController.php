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

        $records = collect($request->hasFile('file') ? [$request->file('file')] : $request->file('files'))
            ->map(fn (UploadedFile $file): Media => $media->store($owner, $file, $validated['collection'] ?? 'gallery', $validated['alt_text'] ?? null));
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
