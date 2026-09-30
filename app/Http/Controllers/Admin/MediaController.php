<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\MediaService;
use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Project;
use App\Models\Property;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function store(Request $request, MediaService $media): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:8192'],
            'owner_type' => ['required', 'in:property,project'],
            'owner_id' => ['required', 'integer'],
            'collection' => ['nullable', 'string'],
        ]);

        $owner = $this->owner($validated['owner_type'], (int) $validated['owner_id']);
        $this->authorize('update', $owner);

        $record = $media->store($owner, $request->file('file'), $validated['collection'] ?? 'gallery');

        return response()->json([
            'id' => $record->id,
            'url' => $record->url(),
            'thumb_url' => $record->thumbUrl(),
        ], 201);
    }

    public function reorder(Request $request, MediaService $media): JsonResponse
    {
        $validated = $request->validate([
            'owner_type' => ['required', 'in:property,project'],
            'owner_id' => ['required', 'integer'],
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer'],
        ]);
        $owner = $this->owner($validated['owner_type'], (int) $validated['owner_id']);
        $this->authorize('update', $owner);
        $media->reorder($owner, 'gallery', $validated['ordered_ids']);

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, Media $medium, MediaService $media): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $medium->mediable);
        $media->delete($medium, $request->user());

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', 'Photograph removed.');
    }

    private function owner(string $type, int $id): Model
    {
        return $type === 'project'
            ? Project::query()->findOrFail($id)
            : Property::query()->findOrFail($id);
    }
}
