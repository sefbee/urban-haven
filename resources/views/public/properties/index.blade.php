@extends('layouts.public')

@php
    $listing = (string) ($filters['listing_type'] ?? '');
    $selectedAreaIds = array_values(array_filter(array_map('intval', (array) ($filters['location_area_ids'] ?? []))));
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
    $exploreAreas = $areas->where('properties_count', '>', 0)->sortByDesc('properties_count')->take(8);
    $mapViewUrl = route('map', request()->except('page'));
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

        @include('public.partials.filter-bar')

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

                    <a class="uh-btn-secondary uh-btn-sm" href="{{ $mapViewUrl }}" data-track="map_view_click" data-track-location="results_bar">
                        <x-icon name="map" class="size-4" />
                        {{ __('Map view') }}
                    </a>

                    <div class="uh-sort">
                        <label class="sr-only" for="results-sort">{{ __('Sort by') }}</label>
                        <select id="results-sort" name="sort" form="property-filters" x-on:change="$el.form.requestSubmit()"
                                data-uh-select data-uh-select-search="off" data-uh-select-auto-width="true">
                            @foreach($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
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

            <div class="uh-workspace is-mapless">
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

                <aside class="uh-workspace-aside">
                    <a class="uh-map-cta" href="{{ $mapViewUrl }}" data-track="map_view_click" data-track-location="aside">
                        <span class="uh-map-cta-art" aria-hidden="true">
                            <span class="uh-map-cta-pin is-one"></span>
                            <span class="uh-map-cta-pin is-two"></span>
                            <span class="uh-map-cta-pin is-three"></span>
                        </span>
                        <span class="uh-map-cta-body">
                            <span class="uh-map-cta-title">{{ __('See these homes on a map') }}</span>
                            <span class="uh-map-cta-link">
                                <x-icon name="map" class="size-4" />
                                {{ __('Open map view') }}
                            </span>
                        </span>
                    </a>

                    @if($exploreAreas->isNotEmpty())
                        <section class="uh-area-panel" aria-labelledby="explore-areas-title">
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
