<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Project;
use App\Models\Property;
use App\Support\SeoMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $stage = $request->string('development_stage')->toString();

        $projects = Project::query()
            ->published()
            ->when($stage === 'available', fn ($query) => $query->whereIn('development_stage', ['ongoing', 'upcoming']))
            ->when(
                in_array($stage, ['ongoing', 'completed', 'upcoming'], true),
                fn ($query) => $query->where('development_stage', $stage),
            )
            ->with(['locationArea', 'media'])
            ->withCount(['properties' => fn ($query) => $query->published()])
            ->addSelect([
                'starting_price' => Property::query()
                    ->selectRaw('min(price)')
                    ->whereColumn('properties.project_id', 'projects.id')
                    ->published()
                    ->whereNotNull('price'),
            ])
            ->orderBy('name')
            ->get();

        return view('public.projects.index', [
            'projects' => $projects,
            'seo' => SeoMeta::for(null, 'Projects', 'Ongoing and completed Urban Haven developments.'),
        ]);
    }

    public function show(string $slug): View
    {
        $project = Project::query()->published()->with(['locationArea', 'media', 'properties' => fn ($query) => $query->published()->with(['propertyType', 'locationArea', 'media'])])->where('slug', $slug)->firstOrFail();

        return view('public.projects.show', [
            'project' => $project,
            'amenities' => Amenity::query()->whereIn('id', $project->amenity_ids ?? [])->orderBy('label')->get(),
            'seo' => SeoMeta::for($project, $project->name, Str::limit(strip_tags((string) $project->description), 150)),
            'whatsapp' => $this->whatsappUrl($project),
        ]);
    }

    private function whatsappUrl(Project $project): string
    {
        $number = preg_replace('/\D+/', '', (string) config('urbanhaven.whatsapp.number'));
        $text = rawurlencode('Hello Urban Haven, I would like to know more about the '.$project->name.' project.');

        return 'https://wa.me/'.$number.'?text='.$text;
    }
}
