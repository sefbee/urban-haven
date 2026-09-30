@extends('layouts.public')

@php
    $hasAdvanced = filled($filters['min_price'] ?? null)
        || filled($filters['max_price'] ?? null)
        || filled($filters['min_beds'] ?? null)
        || filled($filters['availability'] ?? null)
        || filled($filters['amenities'] ?? null);
    $total = $properties->total();
@endphp

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container py-8 md:py-10">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Buy & rent')],
            ]" />
            <h1 class="uh-h1 mt-3">{{ __('Find a home in Dhaka') }}</h1>
            <p class="uh-lede mt-3 max-w-2xl">{{ __('Every home below is owned and published by Urban Haven Properties Ltd.') }}</p>
        </div>
    </div>

    <div class="uh-container uh-section-tight"
         x-data="uhSearchPage({{ $hasAdvanced ? 'true' : 'false' }})"
         @keydown.escape.window="closeFilters()">

        {{-- Mobile filter / map controls --}}
        <div class="flex items-center gap-2 lg:hidden">
            <button type="button" class="uh-btn-outline uh-btn-sm flex-1" @click="toggleFilters()"
                    :aria-expanded="filtersOpen.toString()" aria-controls="property-filters">
                <x-icon name="filter" class="size-4" />
                {{ __('Filters') }}
                @if(filled($activeFilters))
                    <span class="inline-flex size-5 items-center justify-center rounded-full bg-forest text-[0.6875rem] font-bold text-cream">{{ count($activeFilters) }}</span>
                @endif
            </button>
            <button type="button" class="uh-btn-outline uh-btn-sm flex-1"
                    @click="showMap = !showMap; $nextTick(() => window.dispatchEvent(new Event('uh:refresh-maps')))"
                    :aria-expanded="showMap.toString()" aria-controls="property-map-panel">
                <x-icon name="map" class="size-4" />
                <span x-text="showMap ? '{{ __('Hide map') }}' : '{{ __('Show map') }}'">{{ __('Show map') }}</span>
            </button>
        </div>

        <div x-show="filtersOpen" x-cloak x-transition.opacity.duration.150ms
             class="fixed inset-0 z-40 bg-ink/40 lg:hidden" @click="closeFilters()" aria-hidden="true"></div>

        {{-- Filters --}}
        <form id="property-filters" method="GET" action="{{ route('properties.index') }}"
              class="uh-panel mt-3 hidden lg:mt-0 lg:block"
              :class="filtersOpen && 'max-lg:!fixed max-lg:!inset-x-0 max-lg:!bottom-0 max-lg:!z-50 max-lg:!mt-0 max-lg:!block max-lg:!max-h-[90vh] max-lg:!overflow-y-auto max-lg:!rounded-t-2xl'"
              x-data="uhForm" @submit="submit">
            <div class="mb-4 flex items-center justify-between lg:hidden">
                <h2 class="uh-h4">{{ __('Filter properties') }}</h2>
                <button type="button" class="uh-icon-btn" @click="closeFilters()" aria-label="{{ __('Close filters') }}">
                    <x-icon name="close" class="size-5" />
                </button>
            </div>
            <h2 class="sr-only">{{ __('Filter properties') }}</h2>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <x-ui.select name="listing_type" :label="__('Buy or rent')">
                    <option value="">{{ __('Any') }}</option>
                    <option value="sale" @selected(($filters['listing_type'] ?? '') === 'sale')>{{ __('For sale') }}</option>
                    <option value="rent" @selected(($filters['listing_type'] ?? '') === 'rent')>{{ __('For rent') }}</option>
                </x-ui.select>

                <x-ui.select name="location_area_id" :label="__('Area')">
                    <option value="">{{ __('Any area') }}</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected(($filters['location_area_id'] ?? '') == $area->id)>{{ $area->name }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select name="property_type_id" :label="__('Property type')">
                    <option value="">{{ __('Any type') }}</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" @selected(($filters['property_type_id'] ?? '') == $type->id)>{{ $type->label }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select name="city" :label="__('City')">
                    <option value="">{{ __('Any city') }}</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="mt-4 lg:mt-5" x-show="more" x-cloak>
                <div class="border-t border-line pt-5">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <x-ui.input name="min_price" type="number" min="0" inputmode="numeric"
                                    :label="__('Minimum price')" :value="$filters['min_price'] ?? null"
                                    placeholder="0" :hint="__('BDT')" />
                        <x-ui.input name="max_price" type="number" min="0" inputmode="numeric"
                                    :label="__('Maximum price')" :value="$filters['max_price'] ?? null"
                                    placeholder="—" :hint="__('BDT')" />
                        <x-ui.select name="min_beds" :label="__('Bedrooms')">
                            <option value="">{{ __('Any') }}</option>
                            @foreach([1, 2, 3, 4, 5] as $beds)
                                <option value="{{ $beds }}" @selected(($filters['min_beds'] ?? '') == $beds)>{{ $beds }}+</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.select name="availability" :label="__('Availability')">
                            <option value="">{{ __('Any') }}</option>
                            @foreach(['available' => __('Available'), 'reserved' => __('Reserved')] as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['availability'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    @if($amenities->isNotEmpty())
                        <fieldset class="mt-5">
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
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-line pt-4">
                <button type="submit" class="uh-btn-primary" :disabled="submitting">
                    <x-icon name="search" class="size-4" x-show="!submitting" />
                    <span class="uh-spinner" x-show="submitting" x-cloak></span>
                    {{ __('Search') }}
                </button>
                <button type="button" class="uh-btn-ghost uh-btn-sm" @click="more = !more" :aria-expanded="more.toString()">
                    <x-icon name="filter" class="size-4" />
                    <span x-text="more ? '{{ __('Fewer filters') }}' : '{{ __('More filters') }}'">{{ __('More filters') }}</span>
                </button>
                @if(filled($activeFilters))
                    <a class="uh-btn-ghost uh-btn-sm ml-auto" href="{{ route('properties.index') }}">{{ __('Clear all') }}</a>
                @endif
            </div>
        </form>

        {{-- Results toolbar --}}
        <div class="mt-7 flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-[var(--color-muted)]" aria-live="polite">
                <span class="uh-numeric font-semibold text-ink">{{ $total }}</span>
                {{ $total === 1 ? __('home available') : __('homes available') }}
            </p>

            <label class="flex items-center gap-2 text-sm">
                <span class="text-[var(--color-muted)]">{{ __('Sort by') }}</span>
                <select name="sort" form="property-filters" class="uh-select min-h-9 w-auto py-1.5 text-[0.8125rem]"
                        onchange="this.form.requestSubmit()">
                    @foreach($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        {{-- Active filter chips --}}
        @if(filled($activeFilters))
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--color-muted)]">{{ __('Filtered by') }}</span>
                @foreach($activeFilters as $chip)
                    <a class="uh-chip uh-chip-active" href="{{ $chip['url'] }}">
                        {{ $chip['label'] }}
                        <span class="uh-chip-remove" aria-hidden="true"><x-icon name="close" class="size-3" /></span>
                        <span class="sr-only">{{ __('Remove filter') }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Results + map --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_21rem] lg:gap-8">
            <div>
                @if($properties->isNotEmpty())
                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach($properties as $property)
                            @include('public.partials.property-card', ['property' => $property])
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

            <aside id="property-map-panel" class="lg:block" :class="{ 'hidden': ! showMap }" aria-label="{{ __('Map of available homes') }}">
                <div class="lg:sticky lg:top-24">
                    <div class="uh-panel-flush overflow-hidden">
                        <div data-uh-map
                             class="h-72 w-full lg:h-[28rem]"
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
            </aside>
        </div>
    </div>
@endsection
