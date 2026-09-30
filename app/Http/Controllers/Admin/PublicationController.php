<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\InventoryService;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    public function submitProperty(Property $property, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $property);
        $inventory->submitForReview($property, request()->user());

        return back()->with('status', 'Submitted for review.');
    }

    public function approveProperty(Property $property, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $property);
        $inventory->approve($property, request()->user());

        return back()->with('status', 'Approved.');
    }

    public function publishProperty(Property $property, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $property);
        $inventory->publishProperty($property, request()->user());

        return back()->with('status', 'Published.');
    }

    public function unpublishProperty(Request $request, Property $property, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $property);
        $validated = $request->validate(['unpublish_reason' => ['required', 'string', 'min:3']]);
        $inventory->unpublishProperty($property, $validated['unpublish_reason'], $request->user());

        return back()->with('status', 'Unpublished.');
    }

    public function submitProject(Project $project, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('publish', $project);
        $inventory->submitForReview($project, request()->user());

        return back()->with('status', 'Submitted for review.');
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
        $validated = $request->validate(['unpublish_reason' => ['required', 'string', 'min:3']]);
        $inventory->unpublishProject($project, $validated['unpublish_reason'], $request->user());

        return back()->with('status', 'Unpublished.');
    }
}
