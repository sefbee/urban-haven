<?php

namespace App\Http\Controllers\Public;

use App\Contracts\SearchService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\SearchRequest;
use App\Models\Amenity;
use App\Models\LocationArea;
use App\Models\Property;
use App\Models\PropertyType;
use App\Support\MoneyFormatter;
use App\Support\SeoMeta;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PropertySearchController extends Controller
{
    public function index(SearchRequest $request, SearchService $search): View
    {
        $filters = $request->validated();
        $results = $search->search($filters, (int) $request->integer('page', 1), (int) ($filters['per_page'] ?? 12));
        $types = PropertyType::query()->active()->orderBy('label')->get();
        $areas = LocationArea::query()->active()->orderBy('name')->get();
        $amenities = Amenity::query()->active()->orderBy('label')->get();
        $decimals = (int) config('urbanhaven.maps.approximate_decimals', 3);

        $mapMarkers = $search->mapListings($filters)->map(fn (Property $property): array => [
            'title' => $property->title,
            'url' => route('properties.show', $property->slug),
            'price' => MoneyFormatter::formatBdt($property->price, $property->price_basis),
            'lat' => round((float) $property->lat, $decimals),
            'lng' => round((float) $property->lng, $decimals),
        ])->values();

        return view('public.properties.index', [
            'properties' => $results,
            'filters' => $filters,
            'types' => $types,
            'areas' => $areas,
            'amenities' => $amenities,
            'cities' => LocationArea::query()->active()->distinct()->orderBy('city')->pluck('city'),
            'mapMarkers' => $mapMarkers,
            'activeFilters' => $this->activeFilters($filters, $areas, $types, $amenities),
            'sortOptions' => [
                'newest' => __('Newest first'),
                'price_asc' => __('Price: low to high'),
                'price_desc' => __('Price: high to low'),
                'area_desc' => __('Largest first'),
                'beds_desc' => __('Most bedrooms'),
            ],
            'seo' => SeoMeta::for(null, 'Properties for sale and rent in Dhaka', 'Search Urban Haven apartments, duplexes and commercial space.'),
        ]);
    }

    /**
     * Build removable chips describing the filters currently narrowing results.
     *
     * @param  array<string, mixed>  $filters
     * @param  Collection<int, LocationArea>  $areas
     * @param  Collection<int, PropertyType>  $types
     * @param  Collection<int, Amenity>  $amenities
     * @return list<array{label: string, url: string}>
     */
    private function activeFilters(array $filters, Collection $areas, Collection $types, Collection $amenities): array
    {
        $applied = array_filter($filters, fn ($value) => $value !== null && $value !== '' && $value !== []);
        unset($applied['page'], $applied['per_page'], $applied['sort']);

        $labels = [
            'listing_type' => fn ($value) => $value === 'rent' ? __('For rent') : __('For sale'),
            'property_type_id' => fn ($value) => $types->firstWhere('id', (int) $value)?->label,
            'location_area_id' => fn ($value) => $areas->firstWhere('id', (int) $value)?->name,
            'city' => fn ($value) => (string) $value,
            'min_price' => fn ($value) => __('From :price', ['price' => MoneyFormatter::formatBdt($value)]),
            'max_price' => fn ($value) => __('Up to :price', ['price' => MoneyFormatter::formatBdt($value)]),
            'min_beds' => fn ($value) => __(':count+ bedrooms', ['count' => $value]),
            'max_beds' => fn ($value) => __('Up to :count bedrooms', ['count' => $value]),
            'availability' => fn ($value) => ucfirst((string) $value),
            'is_furnished' => fn ($value) => $value ? __('Furnished') : __('Unfurnished'),
        ];

        $chips = [];

        foreach ($applied as $key => $value) {
            if ($key === 'amenities') {
                foreach ((array) $value as $amenityId) {
                    $remaining = array_values(array_diff((array) $value, [$amenityId]));
                    $chips[] = [
                        'label' => (string) $amenities->firstWhere('id', (int) $amenityId)?->label,
                        'url' => route('properties.index', array_filter(['amenities' => $remaining] + $applied + ['sort' => $filters['sort'] ?? null])),
                    ];
                }

                continue;
            }

            $label = isset($labels[$key]) ? $labels[$key]($value) : null;

            if (blank($label)) {
                continue;
            }

            $chips[] = [
                'label' => $label,
                'url' => route('properties.index', array_filter(
                    array_diff_key($applied, [$key => null]) + ['sort' => $filters['sort'] ?? null],
                )),
            ];
        }

        return array_values(array_filter($chips, fn (array $chip): bool => filled($chip['label'])));
    }
}
