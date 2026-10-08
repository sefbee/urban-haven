<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Project;
use App\Models\Property;
use App\Models\Setting;
use App\Support\SeoMeta;
use App\Support\StructuredData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->published()
            ->whereDoesntHave('seoOverride', fn (Builder $query) => $query->where('noindex', true))
            ->with(['locationArea', 'media', 'publicationState'])
            ->withCount(['properties' => fn (Builder $query) => $query->published()->whereIn('listing_type', Setting::enabledPurposes() ?: ['__none__'])])
            ->orderByDesc('is_featured')
            ->orderByDesc('last_updated_at')
            ->paginate(12);

        return view('public.projects.index', [
            'projects' => $projects,
            'seo' => SeoMeta::for(null, __('Property projects'), __('Explore current and upcoming property developments from Urban Haven.'), [
                'canonical' => route('projects.index'),
            ]),
        ]);
    }

    public function show(Project $project): View
    {
        abort_unless($project->isPublished(), 404);

        $project->load([
            'locationArea',
            'media',
            'seoOverride',
            'publicationState',
            'properties' => fn (Builder $query) => $query->published()
                ->whereIn('listing_type', Setting::enabledPurposes() ?: ['__none__'])
                ->with(['propertyType', 'locationArea', 'media', 'units']),
        ]);
        $project->setAttribute('properties_count', $project->properties->count());

        $amenities = Amenity::query()->active()->whereIn('id', $project->amenity_ids ?? [])->orderBy('label')->get();

        return view('public.projects.show', [
            'project' => $project,
            'publicProperties' => $project->properties->whereNotIn('availability', Property::UNAVAILABLE)->values(),
            'amenities' => $amenities,
            'availability' => $project->availabilitySummary(),
            'seo' => SeoMeta::for($project, $project->name, $project->description, [
                'canonical' => route('projects.show', $project->slug),
                'json_ld' => [StructuredData::breadcrumbs([[__('Home'), route('home')], [__('Projects'), route('projects.index')], [$project->name, route('projects.show', $project->slug)]])],
            ]),
        ]);
    }
}
