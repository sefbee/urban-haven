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
    <div class="border-b border-line bg-paper">
        <div class="uh-container pt-6 pb-4">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => $resultsTitle],
            ]" />
            <h1 class="uh-h1 mt-3">{{ $resultsTitle }}</h1>
            <p class="mt-2 text-sm text-[var(--color-muted)]">{{ __('Every property here is published directly by Urban Haven.') }}</p>
            @if($searchErrors && $searchErrors->any())
                <x-ui.alert tone="warn" class="mt-4">
                    <p class="font-medium">{{ __('Some filters were not valid and have been ignored:') }}</p>
                    <ul class="mt-1 list-disc pl-4">
                        @foreach($searchErrors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            @endif
        </div>
    </div>

    <div x-data="uhBrowse({{ $hasAdvanced ? 'true' : 'false' }})"
         @open-preview="openPreview($event.detail)"
         @keydown.escape.window="onEscape()"
         @keydown.left.window="preview && previousSlide()"
         @keydown.right.window="preview && nextSlide()">

        <div class="sticky top-16 z-30 border-b border-line bg-paper lg:top-[4.25rem]">
            <div class="uh-container py-3">
                <form id="property-filters" method="GET" action="{{ route('properties.index') }}"
                      class="flex flex-col gap-3"
                      @submit="submitting = true; $event.target.querySelectorAll('input, select, textarea').forEach((field) => { if (field.value === '' && field.type !== 'checkbox' && field.type !== 'radio' && field.type !== 'hidden') field.disabled = true })">

                    <div class="flex flex-wrap items-center gap-2">
                        @if(count($purposes) > 1)
                            <div class="inline-flex rounded-lg bg-sand p-1" aria-label="{{ __('Buy or rent') }}">
                                <a href="{{ route('properties.index', ['listing_type' => 'sale'] + $kept) }}" @if($listing === 'sale') aria-current="page" @endif
                                   @class(['inline-flex min-h-9 items-center rounded-md px-3 text-sm font-semibold transition', 'bg-forest text-cream shadow-sm' => $listing === 'sale', 'text-ink/70 hover:text-ink' => $listing !== 'sale'])>
                                    {{ __('Buy') }}
                                </a>
                                <a href="{{ route('properties.index', ['listing_type' => 'rent'] + $kept) }}" @if($listing === 'rent') aria-current="page" @endif
                                   @class(['inline-flex min-h-9 items-center rounded-md px-3 text-sm font-semibold transition', 'bg-forest text-cream shadow-sm' => $listing === 'rent', 'text-ink/70 hover:text-ink' => $listing !== 'rent'])>
                                    {{ __('Rent') }}
                                </a>
                            </div>
                        @endif
                        @if($listing !== '')
                            <input type="hidden" name="listing_type" value="{{ $listing }}">
                        @endif
                        @if(filled($filters['q'] ?? null))
                            <input type="hidden" name="q" value="{{ $filters['q'] }}">
                        @endif

                        <div class="min-w-0 flex-1 relative"
                             x-data="uhLocationTags({{ \Illuminate\Support\Js::from($areaOptions) }}, {{ \Illuminate\Support\Js::from($selectedAreaIds) }})"
                             @keydown.escape.stop="open = false">
                            <label class="sr-only" for="location-query">{{ __('Location') }}</label>
                            <div class="flex min-h-11 flex-wrap items-center gap-1.5 rounded-lg border border-line-strong bg-paper px-2 py-1.5 focus-within:border-forest focus-within:ring-2 focus-within:ring-forest/15">
                                <template x-for="area in selectedAreas" :key="area.id">
                                    <span class="uh-chip uh-chip-active min-h-7 px-2.5 text-xs">
                                        <span x-text="area.name"></span>
                                        <button type="button" class="uh-chip-remove" @click="remove(area.id)" :aria-label="'{{ __('Remove filter') }} ' + area.name">
                                            <x-icon name="close" class="size-3" />
                                        </button>
                                        <input type="hidden" name="location_area_ids[]" :value="area.id">
                                    </span>
                                </template>
                                <input id="location-query" type="text" x-model="query" @focus="open = true" @input="open = true"
                                       @keydown.enter.prevent="onEnter()" autocomplete="off"
                                       class="min-w-32 flex-1 border-0 bg-transparent px-1 py-1 text-sm outline-none"
                                       placeholder="{{ __('Badda, Bangshal, Chak Bazar…') }}">
                            </div>
                            <div x-show="open && suggestions.length" x-cloak @click.outside="open = false"
                                 class="absolute z-40 mt-1 max-h-64 w-[min(100%,24rem)] overflow-y-auto rounded-xl bg-paper p-1 ring-1 ring-line shadow-[var(--shadow-uh)]">
                                <template x-for="area in suggestions" :key="area.id">
                                    <button type="button" class="flex min-h-10 w-full items-center rounded-lg px-3 text-left text-sm hover:bg-sand" @click="add(area.id)" x-text="area.name"></button>
                                </template>
                            </div>
                        </div>

                        <button type="button" class="uh-icon-action shrink-0" x-data="uhNearMe()" @click="locate()"
                                :aria-busy="locating.toString()" :disabled="locating"
                                aria-label="{{ __('Properties Near Me') }}" title="{{ __('Properties Near Me') }}">
                            <x-icon name="locate" class="size-4" x-show="!locating" />
                            <span class="uh-spinner" x-show="locating" x-cloak></span>
                        </button>
                        <input type="hidden" name="lat" value="{{ $filters['lat'] ?? '' }}">
                        <input type="hidden" name="lng" value="{{ $filters['lng'] ?? '' }}">
                        <input type="hidden" name="radius_km" value="{{ $filters['radius_km'] ?? '3' }}">
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto_auto]">
                        <label class="sr-only" for="price-range">{{ __('Price Range') }}</label>
                        <select id="price-range" name="price_band" class="uh-select">
                            <option value="">{{ __('Price Range') }}</option>
                            @foreach($priceGroups as $value => $band)
                                <option value="{{ $value }}" @selected($priceBand === $value)>{{ $band['label'] }}</option>
                            @endforeach
                        </select>

                        <x-ui.select name="property_type_id" :label="__('Property Type')" sr-label>
                            <option value="">{{ __('Property Type') }}</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" @selected(($filters['property_type_id'] ?? '') == $type->id)>{{ $type->label }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select name="area_band" :label="__('Area size')" sr-label>
                            <option value="">{{ __('Area size') }}</option>
                            @foreach(\App\Support\SearchBands::areas() as $key => $band)
                                <option value="{{ $key }}" @selected(($filters['area_band'] ?? '') === $key)>{{ $band['label'] }}</option>
                            @endforeach
                        </x-ui.select>

                        <button type="button" class="uh-btn-outline" @click="toggleFilters()"
                                :aria-expanded="filtersOpen.toString()" aria-controls="more-filters">
                            <x-icon name="filter" class="size-4" />
                            {{ __('More Filters') }}
                            @if($hasAdvanced)
                                <span class="inline-flex size-5 items-center justify-center rounded-full bg-forest text-[0.6875rem] font-bold text-cream">+</span>
                            @endif
                        </button>

                        <button type="submit" class="uh-btn-primary" :disabled="submitting">
                            <x-icon name="search" class="size-4" x-show="!submitting" />
                            <span class="uh-spinner" x-show="submitting" x-cloak></span>
                            {{ __('Search') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @include('public.partials.filter-drawer')

        <div class="uh-container uh-section-tight">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-[var(--color-muted)]" aria-live="polite">
                    @if($properties->total())
                        {{ __('Showing :from–:to of :total', [
                            'from' => $properties->firstItem(),
                            'to' => $properties->lastItem(),
                            'total' => $properties->total(),
                        ]) }}
                    @else
                        {{ __('No homes match') }}
                    @endif
                </p>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex rounded-lg bg-sand p-1" role="group" aria-label="{{ __('Result layout') }}">
                        <button type="button" class="inline-flex min-h-9 items-center gap-1.5 rounded-md px-2.5 text-xs font-semibold"
                                :class="layout === 'list' ? 'bg-paper text-ink shadow-sm' : 'text-[var(--color-muted)]'"
                                :aria-pressed="(layout === 'list').toString()" @click="setLayout('list')">
                            <x-icon name="list" class="size-4" />
                            <span class="hidden sm:inline">{{ __('List') }}</span>
                        </button>
                        <button type="button" class="inline-flex min-h-9 items-center gap-1.5 rounded-md px-2.5 text-xs font-semibold"
                                :class="layout === 'grid' ? 'bg-paper text-ink shadow-sm' : 'text-[var(--color-muted)]'"
                                :aria-pressed="(layout === 'grid').toString()" @click="setLayout('grid')">
                            <x-icon name="grid" class="size-4" />
                            <span class="hidden sm:inline">{{ __('Cards') }}</span>
                        </button>
                    </div>

                    <button type="button" class="uh-btn-outline uh-btn-sm" @click="toggleMap()"
                            :aria-expanded="showMap.toString()">
                        <x-icon name="map" class="size-4" />
                        <span x-text="showMap ? '{{ __('Hide map') }}' : '{{ __('Map') }}'">{{ __('Map') }}</span>
                    </button>

                    <label class="flex items-center gap-2 text-sm">
                        <span class="sr-only">{{ __('Sort by') }}</span>
                        <select name="sort" form="property-filters" class="uh-select min-h-9 w-auto py-1.5 text-[0.8125rem]"
                                onchange="this.form.requestSubmit()">
                            @foreach($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>

            @if(filled($activeFilters))
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @foreach($activeFilters as $chip)
                        <a class="uh-chip uh-chip-active" href="{{ $chip['url'] }}">
                            {{ $chip['label'] }}
                            <span class="uh-chip-remove" aria-hidden="true"><x-icon name="close" class="size-3" /></span>
                            <span class="sr-only">{{ __('Remove filter') }}</span>
                        </a>
                    @endforeach
                    <a class="uh-btn-ghost uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Clear all') }}</a>
                </div>
            @endif

            <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_21rem] lg:items-start lg:gap-8">
                <div>
                    @if($properties->isNotEmpty())
                        <div class="grid gap-4 sm:grid-cols-2" x-show="layout === 'grid'">
                            @foreach($properties as $property)
                                @include('public.partials.property-card', ['property' => $property])
                            @endforeach
                        </div>
                        <div class="grid gap-4" x-show="layout === 'list'" x-cloak>
                            @foreach($properties as $property)
                                @include('public.partials.property-row', ['property' => $property])
                            @endforeach
                        </div>

                        @if($properties->hasPages())
                            <div class="mt-9">{{ $properties->onEachSide(1)->links() }}</div>
                        @endif
                    @else
                        <x-ui.empty icon="search" :title="__('No homes match these filters')"
                                    :description="__('Nothing in our current inventory matches every filter you selected. Widening the price range or choosing a nearby area usually helps.')">
                            @if(filled($activeFilters))
                                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Clear all filters') }}</a>
                            @endif
                            <button type="button" class="uh-btn-outline uh-btn-sm" @click="filtersOpen = true">
                                {{ __('Adjust filters') }}
                            </button>
                        </x-ui.empty>
                    @endif
                </div>

                <aside class="space-y-4 lg:sticky lg:top-40">
                    <div id="property-map-panel" class="hidden lg:block" :class="showMap ? '!block' : '!hidden'" aria-label="{{ __('Map of available homes') }}">
                        <div class="uh-panel-flush overflow-hidden">
                            <div data-uh-map
                                 class="h-72 w-full lg:h-[24rem]"
                                 data-lat="{{ config('urbanhaven.maps.default_lat') }}"
                                 data-lng="{{ config('urbanhaven.maps.default_lng') }}"
                                 data-zoom="12"
                                 data-tiles="{{ config('urbanhaven.maps.tile_url') }}"
                                 data-attribution="{{ e(config('urbanhaven.maps.attribution')) }}"
                                 data-src="{{ $mapDataUrl }}"></div>
                            <p class="border-t border-line px-4 py-3 text-xs text-[var(--color-muted)]">
                                {{ __('The map shows the same results as the list. Pins may be approximate; exact addresses are shared by our sales team.') }}
                            </p>
                        </div>
                    </div>

                    @if($exploreAreas->isNotEmpty())
                        <div class="uh-panel">
                            <h2 class="uh-h4">{{ __('Explore more areas') }}</h2>
                            <ul class="mt-3 divide-y divide-line">
                                @foreach($exploreAreas as $area)
                                    <li>
                                        <a class="flex items-center justify-between gap-3 py-2.5 text-sm transition hover:text-forest"
                                           href="{{ route('properties.index', array_filter(['location_area_ids' => [$area->id], 'listing_type' => $listing ?: null])) }}">
                                            <span class="truncate font-medium">{{ $area->name }}</span>
                                            <span class="uh-numeric shrink-0 text-xs text-[var(--color-muted)]">{{ $area->properties_count }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </aside>
            </div>
        </div>

        @include('public.partials.property-preview')
    </div>
@endsection
