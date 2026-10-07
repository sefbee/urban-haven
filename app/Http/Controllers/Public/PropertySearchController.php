<?php

namespace App\Http\Controllers\Public;

use App\Contracts\SearchService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\SearchRequest;
use App\Models\Amenity;
use App\Models\LocationArea;
use App\Models\PropertyType;
use App\Models\Setting;
use App\Support\MoneyFormatter;
use App\Support\SearchBands;
use App\Support\SeoMeta;
use App\Support\StructuredData;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PropertySearchController extends Controller
{
    public function index(SearchRequest $request, SearchService $search): View
    {
        return view('public.properties.index', $this->browseData($request, $search));
    }

    /**
     * The same search as the list, shown as cards beside a map of every matching pin.
     */
    public function map(SearchRequest $request, SearchService $search): View
    {
        $data = $this->browseData($request, $search, 'map');
        $data['seo'] = SeoMeta::for(null, __('Map of properties'), 'Browse Urban Haven apartments, duplexes, land and commercial space in Dhaka on a map.', [
            'noindex' => true,
            'json_ld' => [StructuredData::breadcrumbs([[__('Home'), route('home')], [__('Properties'), route('properties.index')], [__('Map'), route('map')]])],
        ]);

        return view('public.properties.map', $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function browseData(SearchRequest $request, SearchService $search, string $routeName = 'properties.index'): array
    {
        $filters = $request->validated();
        $filters['location_area_ids'] = collect($filters['location_area_ids'] ?? [])
            ->when(filled($filters['location_area_id'] ?? null), fn ($ids) => $ids->push($filters['location_area_id']))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        unset($filters['location_area_id']);
        $filters['property_type_ids'] = collect($filters['property_type_ids'] ?? [])
            ->when(filled($filters['property_type_id'] ?? null), fn ($ids) => $ids->push($filters['property_type_id']))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        unset($filters['property_type_id']);
        $results = $search->search($filters, (int) $request->integer('page', 1), (int) ($filters['per_page'] ?? 12));
        $types = PropertyType::query()->active()->orderBy('label')->get();
        $areas = LocationArea::query()
            ->active()
            ->withCount(['properties' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();
        $amenities = Amenity::query()->active()->orderBy('label')->get();
        $indexableKeys = config('urbanhaven.seo.indexable_query_keys', ['listing_type', 'page']);
        $hasFilters = collect($request->query())->keys()->diff($indexableKeys)->isNotEmpty();
        $selectedTypes = $types->whereIn('id', $filters['property_type_ids'])->values();

        return [
            'properties' => $results,
            'filters' => $filters,
            'hasAdvanced' => $this->hasAdvancedFilters($filters),
            'types' => $types,
            'areas' => $areas,
            'amenities' => $amenities,
            'cities' => LocationArea::query()->active()->distinct()->orderBy('city')->pluck('city'),
            'mapDataUrl' => route('properties.map', $request->query()),
            'purposes' => Setting::enabledPurposes(),
            'showBedroomFilters' => $selectedTypes->isEmpty() || $selectedTypes->contains(fn (PropertyType $type): bool => $type->hasResidentialFields()),
            'searchErrors' => session('errors')?->getBag('search'),
            'analyticsEvents' => [[
                'event' => 'search',
                'listing_type' => $filters['listing_type'] ?? 'any',
                'property_type' => $selectedTypes->pluck('key')->implode(',') ?: ($filters['category'] ?? null),
                'results' => $results->total(),
            ]],
            'activeFilters' => $this->activeFilters($filters, $areas, $types, $amenities, $routeName),
            'sortOptions' => [
                'newest' => __('Newest first'),
                'price_asc' => __('Price: low to high'),
                'price_desc' => __('Price: high to low'),
                'area_desc' => __('Largest first'),
                'beds_desc' => __('Most bedrooms'),
            ],
            'seo' => SeoMeta::for(null, $this->title($filters), 'Search Urban Haven apartments, duplexes, land and commercial space in Dhaka.', [
                'noindex' => $hasFilters,
                'canonical' => SeoMeta::canonical($indexableKeys),
                'json_ld' => [StructuredData::breadcrumbs([[__('Home'), route('home')], [__('Properties'), route('properties.index')]])],
            ]),
        ];
    }

    /**
     * Whether any filter that lives in the "Filters" drawer is narrowing the results.
     *
     * @param  array<string, mixed>  $filters
     */
    private function hasAdvancedFilters(array $filters): bool
    {
        $drawerKeys = ['min_price', 'max_price', 'price_band', 'min_beds', 'min_baths', 'availability', 'amenities', 'facing',
            'min_road_width', 'max_road_width', 'is_verified', 'verification', 'furnishing', 'area_band', 'lat'];

        return collect($drawerKeys)->contains(fn (string $key): bool => filled($filters[$key] ?? null))
            || (array_key_exists('is_furnished', $filters) && $filters['is_furnished'] !== null && $filters['is_furnished'] !== '');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function title(array $filters): string
    {
        return match ($filters['listing_type'] ?? null) {
            'sale' => 'Properties for sale in Dhaka',
            'rent' => 'Properties for rent in Dhaka',
            default => 'Properties for sale and rent in Dhaka',
        };
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
    private function activeFilters(array $filters, Collection $areas, Collection $types, Collection $amenities, string $routeName): array
    {
        $applied = array_filter($filters, fn ($value) => $value !== null && $value !== '' && $value !== []);
        unset($applied['page'], $applied['per_page'], $applied['sort']);

        $labels = [
            'q' => fn ($value) => __('Search: :term', ['term' => $value]),
            'listing_type' => fn ($value) => $value === 'rent' ? __('For rent') : __('For sale'),
            'category' => fn ($value) => PropertyType::categoryLabels()[$value] ?? null,
            'location_area_id' => fn ($value) => $areas->firstWhere('id', (int) $value)?->name,
            'city' => fn ($value) => (string) $value,
            'min_price' => fn ($value) => __('From :price', ['price' => MoneyFormatter::formatBdt($value)]),
            'max_price' => fn ($value) => __('Up to :price', ['price' => MoneyFormatter::formatBdt($value)]),
            'min_beds' => fn ($value) => __(':count+ bedrooms', ['count' => $value]),
            'max_beds' => fn ($value) => __('Up to :count bedrooms', ['count' => $value]),
            'min_baths' => fn ($value) => __(':count+ bathrooms', ['count' => $value]),
            'availability' => fn ($value) => ucfirst((string) $value),
            'is_furnished' => fn ($value) => $value ? __('Furnished') : __('Unfurnished'),
            'is_verified' => fn ($value) => $value ? __('Verified') : null,
            'lat' => fn () => __('Properties Near Me'),
            'lng' => fn () => null,
            'radius_km' => fn () => null,
            'price_band' => fn ($value) => SearchBands::prices()[$value]['label'] ?? null,
            'area_band' => fn ($value) => SearchBands::areas()[$value]['label'] ?? null,
            'facing' => fn ($value) => config('urbanhaven.facings.'.$value),
            'min_road_width' => fn ($value) => __(':feet ft road', ['feet' => $value]),
            'max_road_width' => fn ($value) => __('Up to :feet ft road', ['feet' => $value]),
        ];

        $chips = [];

        foreach ($applied as $key => $value) {
            if (in_array($key, ['lng', 'radius_km'], true)) {
                continue;
            }

            if ($key === 'verification') {
                foreach ((array) $value as $flag) {
                    $remaining = array_values(array_diff((array) $value, [$flag]));
                    $chips[] = [
                        'label' => $flag === 'unverified' ? __('Unverified') : __('Verified Listings'),
                        'url' => route($routeName, array_filter(['verification' => $remaining] + $applied + ['sort' => $filters['sort'] ?? null])),
                    ];
                }

                continue;
            }

            if ($key === 'furnishing') {
                foreach ((array) $value as $flag) {
                    $remaining = array_values(array_diff((array) $value, [$flag]));
                    $chips[] = [
                        'label' => match ($flag) {
                            'full' => __('Full Furnished'),
                            'semi' => __('Semi Furnished'),
                            default => __('Unfurnished'),
                        },
                        'url' => route($routeName, array_filter(['furnishing' => $remaining] + $applied + ['sort' => $filters['sort'] ?? null])),
                    ];
                }

                continue;
            }

            if ($key === 'amenities') {
                foreach ((array) $value as $amenityId) {
                    $remaining = array_values(array_diff((array) $value, [$amenityId]));
                    $chips[] = [
                        'label' => (string) $amenities->firstWhere('id', (int) $amenityId)?->label,
                        'url' => route($routeName, array_filter(['amenities' => $remaining] + $applied + ['sort' => $filters['sort'] ?? null])),
                    ];
                }

                continue;
            }

            if ($key === 'property_type_ids') {
                foreach ((array) $value as $typeId) {
                    $remaining = array_values(array_diff(array_map('intval', (array) $value), [(int) $typeId]));
                    $chips[] = [
                        'label' => (string) $types->firstWhere('id', (int) $typeId)?->label,
                        'url' => route($routeName, array_filter(['property_type_ids' => $remaining] + $applied + ['sort' => $filters['sort'] ?? null])),
                    ];
                }

                continue;
            }

            if ($key === 'location_area_ids') {
                foreach ((array) $value as $areaId) {
                    $remaining = array_values(array_diff(array_map('intval', (array) $value), [(int) $areaId]));
                    $chips[] = [
                        'label' => (string) $areas->firstWhere('id', (int) $areaId)?->name,
                        'url' => route($routeName, array_filter(['location_area_ids' => $remaining] + $applied + ['sort' => $filters['sort'] ?? null])),
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
                'url' => route($routeName, array_filter(
                    array_diff_key($applied, [$key => null]) + ['sort' => $filters['sort'] ?? null],
                )),
            ];
        }

        return array_values(array_filter($chips, fn (array $chip): bool => filled($chip['label'])));
    }
}
