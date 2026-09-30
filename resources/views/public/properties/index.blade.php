@extends('layouts.public')

@php
    $hasAdvanced = filled($filters['min_price'] ?? null)
        || filled($filters['max_price'] ?? null)
        || filled($filters['min_beds'] ?? null)
        || filled($filters['availability'] ?? null)
        || filled($filters['amenities'] ?? null)
        || (array_key_exists('is_furnished', $filters) && $filters['is_furnished'] !== null && $filters['is_furnished'] !== '');
    $listing = (string) ($filters['listing_type'] ?? '');
    $areaName = $areas->firstWhere('id', (int) ($filters['location_area_id'] ?? 0))?->name;
    $place = $areaName ?: ($filters['city'] ?? null);
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
    $furnished = $filters['is_furnished'] ?? null;
    $exploreAreas = $areas->where('properties_count', '>', 0)->sortByDesc('properties_count')->take(8);
@endphp

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container pt-6 pb-0">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => $resultsTitle],
            ]" />
            <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="uh-h1">{{ $resultsTitle }}</h1>
                    <p class="mt-2 text-sm text-[var(--color-muted)]">{{ __('Every home here is owned and published by Urban Haven Properties Ltd.') }}</p>
                </div>
            </div>

            <nav class="-mb-px mt-5 flex gap-1 overflow-x-auto" aria-label="{{ __('Buy or rent') }}">
                <a href="{{ route('properties.index', $kept) }}"
                   @class(['uh-tab', 'uh-tab-active' => $listing === ''])>{{ __('All') }}</a>
                <a href="{{ route('properties.index', ['listing_type' => 'sale'] + $kept) }}"
                   @class(['uh-tab', 'uh-tab-active' => $listing === 'sale'])>{{ __('Buy') }}</a>
                <a href="{{ route('properties.index', ['listing_type' => 'rent'] + $kept) }}"
                   @class(['uh-tab', 'uh-tab-active' => $listing === 'rent'])>{{ __('Rent') }}</a>
            </nav>
        </div>
    </div>

    <div x-data="uhBrowse({{ $hasAdvanced ? 'true' : 'false' }})"
         @keydown.escape.window="onEscape()"
         @keydown.left.window="preview && previousSlide()"
         @keydown.right.window="preview && nextSlide()">

        <div class="sticky top-16 z-30 border-b border-line bg-paper lg:top-18">
            <div class="uh-container">
                <div class="flex items-center gap-2 py-3 lg:hidden">
                    <button type="button" class="uh-btn-outline uh-btn-sm flex-1" @click="toggleFilters()"
                            :aria-expanded="filtersOpen.toString()" aria-controls="property-filters">
                        <x-icon name="filter" class="size-4" />
                        {{ __('Filters') }}
                        @if(filled($activeFilters))
                            <span class="inline-flex size-5 items-center justify-center rounded-full bg-forest text-[0.6875rem] font-bold text-cream">{{ count($activeFilters) }}</span>
                        @endif
                    </button>
                    <button type="button" class="uh-btn-outline uh-btn-sm flex-1" @click="toggleMap()"
                            :aria-expanded="showMap.toString()" aria-controls="property-map-panel">
                        <x-icon name="map" class="size-4" />
                        <span x-text="showMap ? '{{ __('Hide map') }}' : '{{ __('Map') }}'">{{ __('Map') }}</span>
                    </button>
                </div>

                <div x-show="filtersOpen" x-cloak x-transition.opacity.duration.150ms
                     class="fixed inset-0 z-40 bg-ink/40 lg:hidden" @click="closeFilters()" aria-hidden="true"></div>

                <form id="property-filters" method="GET" action="{{ route('properties.index') }}"
                      class="hidden pb-4 lg:block lg:py-4"
                      :class="filtersOpen && 'max-lg:!fixed max-lg:!inset-x-0 max-lg:!bottom-0 max-lg:!z-50 max-lg:!block max-lg:!max-h-[92dvh] max-lg:!overflow-y-auto max-lg:!rounded-t-2xl max-lg:!bg-paper max-lg:!px-4 max-lg:!pt-3 max-lg:!pb-6 max-lg:!shadow-[var(--shadow-uh-lg)]'"
                      x-data="uhForm"
                      @submit="submit; $event.target.querySelectorAll('input, select, textarea').forEach((field) => { if (field.value === '' && field.type !== 'checkbox' && field.type !== 'radio') field.disabled = true })">
                    @if($listing !== '')
                        <input type="hidden" name="listing_type" value="{{ $listing }}">
                    @endif

                    <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-line-strong lg:hidden"></div>
                    <div class="mb-3 flex items-center justify-between lg:hidden">
                        <h2 class="uh-h4">{{ __('Filter homes') }}</h2>
                        <button type="button" class="uh-icon-btn" @click="closeFilters()" aria-label="{{ __('Close filters') }}">
                            <x-icon name="close" class="size-5" />
                        </button>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_9.5rem_9.5rem_auto] lg:items-end">
                        <x-ui.select name="location_area_id" :label="__('Area')">
                            <option value="">{{ __('Any area') }}</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" @selected(($filters['location_area_id'] ?? '') == $area->id)>
                                    {{ $area->name }}@if($area->properties_count) ({{ $area->properties_count }})@endif
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select name="property_type_id" :label="__('Property type')">
                            <option value="">{{ __('Any type') }}</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" @selected(($filters['property_type_id'] ?? '') == $type->id)>{{ $type->label }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.input name="min_price" type="number" min="0" inputmode="numeric"
                                    :label="__('Min price')" :value="$filters['min_price'] ?? null" placeholder="0" />
                        <x-ui.input name="max_price" type="number" min="0" inputmode="numeric"
                                    :label="__('Max price')" :value="$filters['max_price'] ?? null" placeholder="{{ __('Any') }}" />

                        <button type="submit" class="uh-btn-primary" :disabled="submitting">
                            <x-icon name="search" class="size-4" x-show="!submitting" />
                            <span class="uh-spinner" x-show="submitting" x-cloak></span>
                            {{ __('Search') }}
                        </button>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-[var(--color-muted)]">{{ __('Bedrooms') }}</span>
                        <div class="flex flex-wrap gap-1.5" role="radiogroup" aria-label="{{ __('Bedrooms') }}">
                            <button type="button"
                                    @class(['uh-choice', 'border-forest bg-forest text-cream' => $minBeds === ''])
                                    onclick="this.form.querySelectorAll('input[name=min_beds]').forEach((input) => { input.checked = false }); this.form.requestSubmit()">
                                {{ __('Any') }}
                            </button>
                            @foreach(['1', '2', '3', '4', '5'] as $value)
                                <label class="uh-choice">
                                    <input type="radio" name="min_beds" value="{{ $value }}" @checked($minBeds === $value)
                                           onchange="this.form.requestSubmit()">
                                    <span class="uh-numeric">{{ $value }}+</span>
                                </label>
                            @endforeach
                        </div>
                        <button type="button" class="uh-btn-ghost uh-btn-sm ml-auto" @click="more = !more" :aria-expanded="more.toString()">
                            <x-icon name="filter" class="size-4" />
                            {{ __('More filters') }}
                        </button>
                    </div>

                    <div class="mt-4" x-show="more" x-cloak>
                        <div class="grid gap-4 border-t border-line pt-4 md:grid-cols-2 lg:grid-cols-3">
                            <x-ui.select name="city" :label="__('City')">
                                <option value="">{{ __('Any city') }}</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                                @endforeach
                            </x-ui.select>

                            <x-ui.select name="availability" :label="__('Availability')">
                                <option value="">{{ __('Any') }}</option>
                                @foreach(['available' => __('Available'), 'reserved' => __('Reserved')] as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['availability'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-ui.select>

                            <fieldset>
                                <legend class="uh-label">{{ __('Furnishing') }}</legend>
                                <div class="flex flex-wrap gap-1.5" role="radiogroup" aria-label="{{ __('Furnishing') }}">
                                    <button type="button"
                                            @class(['uh-choice', 'border-forest bg-forest text-cream' => $furnished === null || $furnished === ''])
                                            onclick="this.form.querySelectorAll('input[name=is_furnished]').forEach((input) => { input.checked = false }); this.form.requestSubmit()">
                                        {{ __('Any') }}
                                    </button>
                                    <label class="uh-choice">
                                        <input type="radio" name="is_furnished" value="1" @checked($furnished === true || $furnished === 1 || $furnished === '1')
                                               onchange="this.form.requestSubmit()">
                                        <span>{{ __('Furnished') }}</span>
                                    </label>
                                    <label class="uh-choice">
                                        <input type="radio" name="is_furnished" value="0" @checked($furnished === false || $furnished === 0 || $furnished === '0')
                                               onchange="this.form.requestSubmit()">
                                        <span>{{ __('Unfurnished') }}</span>
                                    </label>
                                </div>
                            </fieldset>
                        </div>

                        @if($amenities->isNotEmpty())
                            <fieldset class="mt-4">
                                <legend class="uh-legend">{{ __('Amenities') }}</legend>
                                <div class="grid gap-x-5 gap-y-1 sm:grid-cols-2 lg:grid-cols-4">
                                    @foreach($amenities as $amenity)
                                        <label class="uh-check">
                                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                                                   @checked(in_array((string) $amenity->id, array_map('strval', (array) ($filters['amenities'] ?? [])), true))>
                                            <span>{{ $amenity->label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif
                    </div>

                    @if(filled($activeFilters))
                        <div class="mt-4 lg:hidden">
                            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Clear all') }}</a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

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

                    <button type="button" class="uh-btn-outline uh-btn-sm hidden lg:inline-flex" @click="toggleMap()"
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
                        <div class="grid gap-4" :class="layout === 'grid' ? 'sm:grid-cols-2' : ''">
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
                            <button type="button" class="uh-btn-outline uh-btn-sm" @click="filtersOpen = true; more = true">
                                {{ __('Adjust filters') }}
                            </button>
                        </x-ui.empty>
                    @endif
                </div>

                <aside class="space-y-4 lg:sticky lg:top-36">
                    <div id="property-map-panel" class="hidden lg:block" :class="showMap ? '!block' : '!hidden'" aria-label="{{ __('Map of available homes') }}">
                        <div class="uh-panel-flush overflow-hidden">
                            <div data-uh-map
                                 class="h-72 w-full lg:h-[24rem]"
                                 data-lat="{{ config('urbanhaven.maps.default_lat') }}"
                                 data-lng="{{ config('urbanhaven.maps.default_lng') }}"
                                 data-zoom="12"
                                 data-tiles="{{ config('urbanhaven.maps.tile_url') }}"
                                 data-attribution="{{ e(config('urbanhaven.maps.attribution')) }}"
                                 data-properties='@json($mapMarkers)'></div>
                            <p class="border-t border-line px-4 py-3 text-xs text-[var(--color-muted)]">
                                {{ __('Pin locations are approximate. Exact addresses are shared by our sales desk.') }}
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
                                           href="{{ route('properties.index', array_filter(['location_area_id' => $area->id, 'listing_type' => $listing ?: null])) }}">
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
