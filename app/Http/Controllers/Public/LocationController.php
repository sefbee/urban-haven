<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LocationArea;
use App\Models\Project;
use App\Models\Property;
use App\Models\Setting;
use App\Support\SeoMeta;
use App\Support\StructuredData;
use Illuminate\View\View;

class LocationController extends Controller
{
    /**
     * Landing pages exist only for areas with owner-written intro copy, so thin pages never get indexed.
     */
    public function show(string $slug): View
    {
        $area = LocationArea::query()->where('slug', $slug)->firstOrFail();
        abort_unless($area->hasLandingPage(), 404);

        $listings = Property::query()
            ->published()
            ->where('location_area_id', $area->id)
            ->whereIn('listing_type', Setting::enabledPurposes() ?: ['__none__'])
            ->whereNotIn('availability', Property::UNAVAILABLE)
            ->with(['propertyType', 'locationArea', 'media'])
            ->orderByRaw('display_priority is null, display_priority asc')
            ->latest('id')
            ->limit(12)
            ->get();

        return view('public.locations.show', [
            'area' => $area,
            'listings' => $listings,
            'projects' => Project::query()->published()->where('location_area_id', $area->id)->with(['locationArea', 'media'])->limit(6)->get(),
            'purposes' => Setting::enabledPurposes(),
            'seo' => SeoMeta::for(null, 'Property in '.$area->name.', '.$area->city, $area->meta_description ?: mb_strimwidth(strip_tags((string) $area->intro), 0, 155, '…'), [
                'json_ld' => [StructuredData::breadcrumbs([[__('Home'), route('home')], [$area->name, route('locations.show', $area->slug)]])],
            ]),
        ]);
    }
}
