<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\InventoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Amenity;
use App\Models\LocationArea;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Project::class);

        return view('admin.projects.index', [
            'projects' => Project::query()->with(['locationArea', 'publicationState'])->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('admin.projects.create', $this->formData());
    }

    public function store(StoreProjectRequest $request, InventoryService $inventory): RedirectResponse
    {
        $project = $inventory->createProject($request->validated(), $request->user());

        return redirect()->route('admin.projects.edit', $project)->with('status', 'Project created.');
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('admin.projects.edit', array_merge($this->formData(), [
            'project' => $project->load(['publicationState', 'seoOverride']),
        ]));
    }

    public function update(UpdateProjectRequest $request, Project $project, InventoryService $inventory): RedirectResponse
    {
        $inventory->updateProject($project, $request->validated(), $request->user());

        return redirect()->route('admin.projects.edit', $project)->with('status', 'Project updated.');
    }

    public function destroy(Project $project, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('delete', $project);
        $inventory->deleteProject($project, request()->user());

        return redirect()->route('admin.projects.index')->with('status', 'Project deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'areas' => LocationArea::query()->active()->orderBy('name')->get(),
            'amenities' => Amenity::query()->active()->orderBy('label')->get(),
        ];
    }
}
