<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CanPublishProperty;
use App\Contracts\InventoryService;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    public function submitProperty(Property $property, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('submit', $property);
        $inventory->submitForReview($property, request()->user());

        return back()->with('status', 'Submitted for review. An administrator will publish it or return it with notes.');
    }

    public function approveProperty(Property $property, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $property);
        $inventory->approve($property, request()->user());

        return back()->with('status', 'Approved.');
    }

    public function publishProperty(Property $property, InventoryService $inventory, CanPublishProperty $canPublish): JsonResponse|RedirectResponse
    {
        $this->authorize('publish', $property);

        $result = $canPublish($property);

        if (! $result['canPublish']) {
            if (request()->expectsJson()) {
                return response()->json(['failures' => $result['failures']], 422);
            }

            return back()->withErrors(['publish' => $result['failures']])->withInput();
        }

        $inventory->publishProperty($property, request()->user());

        return back()->with('status', 'Published.');
    }

    public function unpublishProperty(Request $request, Property $property, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $property);
        $validated = $request->validate(['unpublish_reason' => ['required', 'string', 'min:3', 'max:500']]);
        $inventory->unpublishProperty($property, $validated['unpublish_reason'], $request->user());

        return back()->with('status', 'Archived. The listing has left search, listings and the sitemap.');
    }

    public function returnProperty(Request $request, Property $property, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $property);
        $validated = $request->validate(['review_note' => ['required', 'string', 'min:3', 'max:2000']]);
        $inventory->returnToDraft($property, $validated['review_note'], $request->user());

        return back()->with('status', 'Returned to the editor with your note.');
    }

    public function submitProject(Project $project, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('submit', $project);
        $inventory->submitForReview($project, request()->user());

        return back()->with('status', 'Submitted for review. An administrator will publish it or return it with notes.');
    }

    public function approveProject(Project $project, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $project);
        $inventory->approve($project, request()->user());

        return back()->with('status', 'Approved.');
    }

    public function publishProject(Project $project, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $project);
        $inventory->publishProject($project, request()->user());

        return back()->with('status', 'Published.');
    }

    public function unpublishProject(Request $request, Project $project, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $project);
        $validated = $request->validate(['unpublish_reason' => ['required', 'string', 'min:3', 'max:500']]);
        $inventory->unpublishProject($project, $validated['unpublish_reason'], $request->user());

        return back()->with('status', 'Archived.');
    }

    public function returnProject(Request $request, Project $project, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $project);
        $validated = $request->validate(['review_note' => ['required', 'string', 'min:3', 'max:2000']]);
        $inventory->returnToDraft($project, $validated['review_note'], $request->user());

        return back()->with('status', 'Returned to the editor with your note.');
    }
}
