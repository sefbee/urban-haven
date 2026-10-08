<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\InventoryService;
use App\Contracts\MediaService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Amenity;
use App\Models\LocationArea;
use App\Models\Project;
use App\Support\SeoFields;
use Illuminate\Http\JsonResponse;
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

    public function store(StoreProjectRequest $request, InventoryService $inventory, MediaService $media): RedirectResponse|JsonResponse
    {
        $project = $inventory->createProject($this->attributes($request), $request->user());
        SeoFields::sync($project, $request->validated());

        if ($request->hasFile('photograph')) {
            $media->store($project, $request->file('photograph'), 'gallery', $request->validated('photograph_alt'));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $project->id,
                'label' => $project->name,
            ], 201);
        }

        return redirect()->route('admin.projects.edit', $project)->with('status', 'Project created.');
    }

    public function edit(Project $project, InventoryService $inventory): View
    {
        $this->authorize('view', $project);
        $project->load(['publicationState', 'seoOverride', 'media']);

        return view('admin.projects.edit', [
            ...$this->formData(),
            'project' => $project,
            'checklist' => $inventory->publishChecklist($project),
            'canEdit' => request()->user()->can('update', $project),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project, InventoryService $inventory): RedirectResponse
    {
        $project = $inventory->updateProject($project, $this->attributes($request), $request->user());
        SeoFields::sync($project, $request->validated());

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
    private function attributes(StoreProjectRequest $request): array
    {
        $attributes = $request->safe()->except([...SeoFields::inputNames(), 'photograph', 'photograph_alt']);

        if (blank($attributes['slug'] ?? null)) {
            unset($attributes['slug']);
        }

        return $attributes;
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
