@extends('layouts.public')

@php
    $hasAdvanced = filled($filters['min_price'] ?? null)
        || filled($filters['max_price'] ?? null)
        || filled($filters['price_band'] ?? null)
        || filled($filters['min_beds'] ?? null)
        || filled($filters['min_baths'] ?? null)
        || filled($filters['availability'] ?? null)
        || filled($filters['amenities'] ?? null)
        || filled($filters['facing'] ?? null)
        || filled($filters['min_road_width'] ?? null)
        || filled($filters['max_road_width'] ?? null)
        || filled($filters['is_verified'] ?? null)
        || filled($filters['verification'] ?? null)
        || filled($filters['furnishing'] ?? null)
        || filled($filters['area_band'] ?? null)
        || filled($filters['lat'] ?? null)
        || (array_key_exists('is_furnished', $filters) && $filters['is_furnished'] !== null && $filters['is_furnished'] !== '');
    $listing = (string) ($filters['listing_type'] ?? '');
    $selectedAreaIds = array_values(array_filter(array_map('intval', (array) ($filters['location_area_ids'] ?? []))));
    if ($selectedAreaIds === [] && filled($filters['location_area_id'] ?? null)) {
        $selectedAreaIds = [(int) $filters['location_area_id']];
    }
    $placeNames = $areas->whereIn('id', $selectedAreaIds)->pluck('name');
    $place = $placeNames->isNotEmpty() ? $placeNames->join(', ') : ($filters['city'] ?? null);
    $resultsTitle = match (true) {
        $listing === 'sale' && filled($place) => __('Homes for sale in :place', ['place' => $place]),
        $listing === 'rent' && filled($place) => __('Homes for rent in :place', ['place' => $place]),
        $listing === 'sale' => __('Homes for sale'),
        $listing === 'rent' => __('Homes for rent'),
        filled($place) => __('Homes in :place', ['place' => $place]),
        default => __('Homes in Dhaka'),
    };
    $kept = request()->except(['listing_type', 'page']);
    $minBeds = (string) ($filters['min_beds'] ?? '');
    $minBaths = (string) ($filters['min_baths'] ?? '');
    $furnished = $filters['is_furnished'] ?? null;
    $exploreAreas = $areas->where('properties_count', '>', 0)->sortByDesc('properties_count')->take(8);
    $areaOptions = $areas->map(fn ($area) => ['id' => $area->id, 'name' => $area->name])->values();
    $priceBand = (string) ($filters['price_band'] ?? '');
    $priceGroups = $listing === 'rent'
        ? config('urbanhaven.price_bands.rent.options', [])
        : config('urbanhaven.price_bands.sale.options', []);
@endphp

@section('content')
    @include('public.partials.page-head', [
        'title' => $resultsTitle,
        'crumbs' => [
            ['label' => __('Home'), 'url' => route('home')],
            ['label' => $resultsTitle],
        ],
    ])
    @if($searchErrors && $searchErrors->any())
        <div class="uh-container">
            <x-ui.alert tone="warn" class="mt-4">
                <p class="font-medium">{{ __('Some filters were not valid and have been ignored:') }}</p>
                <ul class="mt-1 list-disc pl-4">
                    @foreach($searchErrors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        </div>
    @endif

    <div x-data="uhBrowse({{ $hasAdvanced ? 'true' : 'false' }})"
         @open-preview="openPreview($event.detail)"
         @keydown.escape.window="onEscape()"
         @keydown.left.window="preview && previousSlide()"
         @keydown.right.window="preview && nextSlide()">

        <div class="uh-filter-bar">
            <div class="uh-container">
                <form id="property-filters" method="GET" action="{{ route('properties.index') }}"
                      class="uh-filter-grid"
                      @submit="submitting = true; $event.target.querySelectorAll('input, select, textarea').forEach((field) => { if (field.value === '' && field.type !== 'checkbox' && field.type !== 'hidden') field.disabled = true })">
                    @if($listing !== '')
                        <input type="hidden" name="listing_type" value="{{ $listing }}">
                    @endif
                    @if(filled($filters['q'] ?? null))
                        <input type="hidden" name="q" value="{{ $filters['q'] }}">
                    @endif
                    <input type="hidden" name="lat" value="{{ $filters['lat'] ?? '' }}">
                    <input type="hidden" name="lng" value="{{ $filters['lng'] ?? '' }}">
                    <input type="hidden" name="radius_km" value="{{ $filters['radius_km'] ?? '3' }}">

                    @if(count($purposes) > 1)
                        <div class="uh-seg uh-filter-wide justify-self-start" aria-label="{{ __('Buy or rent') }}">
                            <a href="{{ route('properties.index', $kept) }}" @class(['uh-seg-btn', 'is-on' => $listing === ''])>{{ __('All') }}</a>
                            <a href="{{ route('properties.index', ['listing_type' => 'sale'] + $kept) }}" @if($listing === 'sale') aria-current="page" @endif
                               class="uh-seg-btn">{{ __('Buy') }}</a>
                            <a href="{{ route('properties.index', ['listing_type' => 'rent'] + $kept) }}" @if($listing === 'rent') aria-current="page" @endif
                               class="uh-seg-btn">{{ __('Rent') }}</a>
                        </div>
                    @endif

                    <div class="uh-filter-wide relative min-w-0"
                         x-data="uhLocationTags({{ \Illuminate\Support\Js::from($areaOptions) }}, {{ \Illuminate\Support\Js::from($selectedAreaIds) }})"
                         @keydown.escape.stop="open = false">
                        <label class="sr-only" for="location-query">{{ __('Location') }}</label>
                        <div class="uh-filter-field">
                            <x-icon name="search" class="size-4 shrink-0 text-[var(--uh-faint)]" />
                            <template x-for="area in selectedAreas" :key="area.id">
                                <span class="uh-chip uh-chip-active uh-chip-sm">
                                    <span x-text="area.name"></span>
                                    <button type="button" class="uh-chip-remove" @click="remove(area.id)" :aria-label="'{{ __('Remove filter') }} ' + area.name">
                                        <x-icon name="close" class="size-3" />
                                    </button>
                                    <input type="hidden" name="location_area_ids[]" :value="area.id">
                                </span>
                            </template>
                            <input id="location-query" type="text" x-model="query" @focus="open = true" @input="open = true"
                                   @keydown.enter.prevent="onEnter()" autocomplete="off"
                                   class="uh-filter-input"
                                   placeholder="{{ __('Search an area') }}">
                            <button type="button" class="uh-icon-action shrink-0" x-data="uhNearMe()" @click="locate()"
                                    :aria-busy="locating.toString()" :disabled="locating"
                                    aria-label="{{ __('Properties Near Me') }}" title="{{ __('Properties Near Me') }}">
                                <x-icon name="locate" class="size-4" x-show="!locating" />
                                <span class="uh-spinner" x-show="locating" x-cloak></span>
                            </button>
                        </div>
                        <div x-show="open && suggestions.length" x-cloak @click.outside="open = false" class="uh-suggest">
                            <template x-for="area in suggestions" :key="area.id">
                                <button type="button" @click="add(area.id)" x-text="area.name"></button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="sr-only" for="price-range">{{ __('Price Range') }}</label>
                        <select id="price-range" name="price_band" class="uh-select">
                            <option value="">{{ __('Price Range') }}</option>
                            @foreach($priceGroups as $value => $band)
                                <option value="{{ $value }}" @selected($priceBand === $value)>{{ $band['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-ui.select name="property_type_id" :label="__('Property Type')" sr-label>
                        <option value="">{{ __('Property Type') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected(($filters['property_type_id'] ?? '') == $type->id)>{{ $type->label }}</option>
                        @endforeach
                    </x-ui.select>

                    <button type="button" class="uh-btn-secondary" @click="toggleFilters()"
                            :aria-expanded="filtersOpen.toString()" aria-controls="more-filters">
                        {{ __('Filters') }}
                        @if($hasAdvanced)
                            <span class="uh-search-dot" aria-hidden="true"></span>
                            <span class="sr-only">{{ __('(active)') }}</span>
                        @endif
                    </button>

                    <button type="submit" class="uh-btn-primary" :disabled="submitting">
                        <span class="uh-spinner" x-show="submitting" x-cloak></span>
                        {{ __('Search') }}
                    </button>
                </form>
            </div>
            <span class="uh-progress" x-show="submitting" x-cloak aria-hidden="true"></span>
        </div>
        @include('public.partials.filter-drawer')

        <div class="uh-container">
            <div class="uh-results-bar">
                <p class="uh-results-count" aria-live="polite">
                    @if($properties->total())
                        {!! __('Showing :from–:to of :total', [
                            'from' => '<b>'.e($properties->firstItem()).'</b>',
                            'to' => '<b>'.e($properties->lastItem()).'</b>',
                            'total' => '<b>'.e($properties->total()).'</b>',
                        ]) !!}
                    @else
                        {{ __('Nothing matches yet') }}
                    @endif
                </p>

                <div class="uh-results-tools">
                    <div class="uh-text-toggle" role="group" aria-label="{{ __('Result layout') }}">
                        <button type="button" :aria-pressed="(layout === 'grid').toString()" @click="setLayout('grid')">{{ __('Cards') }}</button>
                        <button type="button" :aria-pressed="(layout === 'list').toString()" @click="setLayout('list')">{{ __('List') }}</button>
                    </div>

                    <button type="button" class="uh-btn-text" @click="toggleMap()"
                            :aria-expanded="showMap.toString()" aria-controls="property-map-panel">
                        <span x-text="showMap ? '{{ __('Hide map') }}' : '{{ __('Map') }}'">{{ __('Map') }}</span>
                    </button>

                    <label class="uh-sort">
                        <span class="sr-only">{{ __('Sort by') }}</span>
                        <select name="sort" form="property-filters" onchange="this.form.requestSubmit()">
                            @foreach($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>

            @if(filled($activeFilters))
                <div class="uh-active-filters">
                    @foreach($activeFilters as $chip)
                        <a class="uh-chip uh-chip-active" href="{{ $chip['url'] }}">
                            {{ $chip['label'] }}
                            <span class="uh-chip-remove" aria-hidden="true"><x-icon name="close" class="size-3" /></span>
                            <span class="sr-only">{{ __('Remove filter') }}</span>
                        </a>
                    @endforeach
                    <a class="uh-btn-text" href="{{ route('properties.index') }}">{{ __('Clear all') }}</a>
                </div>
            @endif

            <div class="uh-workspace" :class="showMap ? '' : 'is-mapless'">
                <div class="uh-workspace-results" :class="submitting ? 'is-busy' : ''" :aria-busy="submitting.toString()">
                    @if($properties->isNotEmpty())
                        <div class="uh-grid-cards" x-show="layout === 'grid'">
                            @foreach($properties as $property)
                                @include('public.partials.property-card', ['property' => $property, 'sizes' => '(min-width: 1100px) 26vw, (min-width: 640px) 50vw, 100vw'])
                            @endforeach
                        </div>
                        <div class="uh-list-rows" x-show="layout === 'list'" x-cloak>
                            @foreach($properties as $property)
                                @include('public.partials.property-row', ['property' => $property])
                            @endforeach
                        </div>

                        @if($properties->hasPages())
                            <div class="uh-pagination-wrap">{{ $properties->onEachSide(1)->links() }}</div>
                        @endif
                    @else
                        <x-ui.empty icon="search" :title="__('Nothing matches this search yet')"
                                    :description="filled($activeFilters)
                                        ? __('Nothing we have right now meets every filter at once. Loosening one of them usually opens things up.')
                                        : __('There are no live listings at the moment. Tell us what you are looking for and our sales team will get back to you.')">
                            @if(filled($activeFilters))
                                <div class="uh-empty-suggest">
                                    <p>{{ __('Try removing') }}</p>
                                    <ul>
                                        @foreach($activeFilters as $chip)
                                            <li>
                                                <a class="uh-chip" href="{{ $chip['url'] }}">
                                                    {{ $chip['label'] }}
                                                    <x-icon name="close" class="size-3" />
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Start over with everything') }}</a>
                                <button type="button" class="uh-btn-secondary uh-btn-sm" @click="toggleFilters()">
                                    {{ __('Adjust filters') }}
                                </button>
                            @else
                                <a class="uh-btn-primary uh-btn-sm" href="{{ route('cms.show', 'contact') }}">{{ __('Tell us what you need') }}</a>
                            @endif
                        </x-ui.empty>
                    @endif
                </div>

                <aside class="uh-workspace-aside" :class="showMap ? 'max-[1099px]:order-first' : ''">
                    <section id="property-map-panel" class="hidden lg:block" :class="showMap ? '!block' : '!hidden'" aria-label="{{ __('Map of available homes') }}">
                        <div class="uh-map-frame">
                            <div data-uh-map
                                 class="h-80 w-full min-[1100px]:h-[min(62vh,36rem)]"
                                 data-lat="{{ config('urbanhaven.maps.default_lat') }}"
                                 data-lng="{{ config('urbanhaven.maps.default_lng') }}"
                                 data-zoom="12"
                                 data-tiles="{{ config('urbanhaven.maps.tile_url') }}"
                                 data-attribution="{{ e(config('urbanhaven.maps.attribution')) }}"
                                 data-src="{{ $mapDataUrl }}"></div>
                        </div>
                        <p class="uh-map-note">{{ __('Pins may be approximate; exact addresses are shared by our sales team.') }}</p>
                    </section>

                    @if($exploreAreas->isNotEmpty())
                        <section class="uh-area-panel" :class="showMap ? 'max-[1099px]:hidden' : ''" aria-labelledby="explore-areas-title">
                            <h2 id="explore-areas-title" class="uh-area-title">{{ __('Explore more areas') }}</h2>
                            <ul class="uh-area-list">
                                @foreach($exploreAreas as $area)
                                    <li>
                                        <a href="{{ route('properties.index', array_filter(['location_area_ids' => [$area->id], 'listing_type' => $listing ?: null])) }}"
                                           @if(in_array($area->id, $selectedAreaIds, true)) aria-current="true" @endif>
                                            <span class="truncate">{{ $area->name }}</span>
                                            <span class="uh-numeric">{{ $area->properties_count }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </aside>
            </div>
        </div>

        @include('public.partials.property-preview')
    </div>
@endsection
