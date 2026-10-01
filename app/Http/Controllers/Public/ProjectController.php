<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Project;
use App\Models\Property;
use App\Support\SeoMeta;
use App\Support\StructuredData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $stage = $request->string('development_stage')->toString();
        $category = $request->string('category')->toString();

        $base = fn (): Builder => Project::query()
            ->published()
            ->when(in_array($category, ['residential', 'commercial', 'mixed'], true), fn (Builder $query) => $query->where('property_category', $category))
            ->with(['locationArea', 'media'])
            ->withCount(['properties' => fn (Builder $query) => $query->published()])
            ->addSelect([
                'starting_price' => Property::query()
                    ->selectRaw('min(price)')
                    ->whereColumn('properties.project_id', 'projects.id')
                    ->published()
                    ->whereNotIn('availability', Property::UNAVAILABLE)
                    ->whereNotNull('price'),
            ])
            ->orderBy('name');

        $projects = $base()
            ->when($stage === 'available' || $stage === '', fn (Builder $query) => $query->whereIn('development_stage', ['ongoing', 'upcoming']))
            ->when(in_array($stage, ['ongoing', 'upcoming', 'completed'], true), fn (Builder $query) => $query->where('development_stage', $stage))
            ->get();

        $completed = in_array($stage, ['', 'available'], true)
            ? $base()->where('development_stage', 'completed')->get()
            : collect();

        return view('public.projects.index', [
            'projects' => $projects,
            'completedProjects' => $completed,
            'stage' => $stage,
            'category' => $category,
            'seo' => SeoMeta::for(null, 'Projects', 'Ongoing, upcoming and completed Urban Haven developments in Dhaka.', [
                'noindex' => $category !== '' || ! in_array($stage, ['', 'available', 'completed'], true),
                'canonical' => SeoMeta::canonical(['development_stage']),
            ]),
        ]);
    }

    public function show(string $slug): View
    {
        $project = Project::query()
            ->published()
            ->with(['locationArea', 'media', 'seoOverride', 'properties' => fn ($query) => $query->published()->with(['propertyType', 'locationArea', 'media', 'units'])])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('public.projects.show', [
            'project' => $project,
            'availability' => $project->availabilitySummary(),
            'amenities' => Amenity::query()->whereIn('id', $project->amenity_ids ?? [])->orderBy('label')->get(),
            'seo' => SeoMeta::for($project, $project->name, Str::limit(strip_tags((string) $project->description), 150), [
                'json_ld' => [
                    StructuredData::project($project),
                    StructuredData::breadcrumbs([[__('Home'), route('home')], [__('Projects'), route('projects.index')], [$project->name, route('projects.show', $project->slug)]]),
                ],
            ]),
            'whatsapp' => $project->whatsappEnquiryUrl(),
            'analyticsEvents' => [['event' => 'project_view', 'project_id' => $project->id, 'stage' => $project->development_stage]],
        ]);
    }
}
