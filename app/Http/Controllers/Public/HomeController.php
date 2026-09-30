<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsBlock;
use App\Models\LocationArea;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyType;
use App\Support\SeoMeta;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $blocks = CmsBlock::query()
            ->whereIn('key', ['hero', 'about', 'contact_details', 'intent_blocks'])
            ->get()
            ->keyBy('key');

        return view('public.home', [
            'hero' => $blocks->get('hero'),
            'about' => $blocks->get('about'),
            'contact' => $blocks->get('contact_details'),
            'intents' => $blocks->get('intent_blocks'),
            'projects' => Project::query()->published()->where('is_featured', true)->with(['locationArea', 'media'])->withCount(['properties' => fn ($query) => $query->published()])->limit(3)->get(),
            'properties' => Property::query()->published()->where('is_featured', true)->with(['propertyType', 'locationArea', 'media'])->limit(6)->get(),
            'areas' => LocationArea::query()
                ->active()
                ->withCount(['properties' => fn ($query) => $query->published()])
                ->orderByDesc('properties_count')
                ->orderBy('name')
                ->limit(6)
                ->get(),
            'types' => PropertyType::query()->active()->orderBy('label')->get(),
            'searchAreas' => LocationArea::query()->active()->orderBy('name')->get(),
            'seo' => SeoMeta::for(null, config('app.name'), 'Company-owned apartments, duplexes and project units for sale and rent in Dhaka.'),
        ]);
    }
}
