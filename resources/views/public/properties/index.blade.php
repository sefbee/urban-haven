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
    $resultsHeading = match (true) {
        $listing === 'sale' && filled($place) => __('Properties For Sale in :place', ['place' => $place]),
        $listing === 'rent' && filled($place) => __('Properties For Rent in :place', ['place' => $place]),
        $listing === 'sale' => __('Properties For Sale in Bangladesh'),
        $listing === 'rent' => __('Properties For Rent in Bangladesh'),
        filled($place) => __('Properties in :place', ['place' => $place]),
        default => __('Properties in Bangladesh'),
    };
    $exploreAreas = $areas->where('properties_count', '>', 0)->sortByDesc('properties_count')->take(8);
    $exploreTypes = $types;
    $typeIcons = [
        'apartment' => 'building', 'penthouse' => 'building', 'residential-building' => 'building',
        'commercial-building' => 'building', 'duplex' => 'home', 'house' => 'home', 'townhouse' => 'home',
        'plot' => 'area', 'commercial-plot' => 'area', 'land' => 'area', 'single-room' => 'bed',
        'sublet-room' => 'bed', 'hotel' => 'bed', 'hostel' => 'users', 'co-working' => 'users',
        'office' => 'dashboard', 'shop' => 'tag', 'showroom' => 'car', 'restaurant' => 'sofa',
        'warehouse' => 'inbox', 'factory' => 'settings',
    ];
    $savedSearchUrl = route('properties.index', request()->except('page'));
@endphp

@section('content')
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

    <div x-data="uhBrowse({{ $hasAdvanced ? 'true' : 'false' }})" x-init="layout = 'grid'"
         @open-preview="openPreview($event.detail)"
         @keydown.escape.window="onEscape()"
         @keydown.left.window="preview && previousSlide()"
         @keydown.right.window="preview && nextSlide()">

        @include('public.partials.filter-bar')

        <div class="uh-container">
            <div class="uh-results-bar is-property-listing">
                <div class="uh-results-summary">
                    <h1 class="uh-results-heading">{{ __('Showing Results for :title', ['title' => $resultsHeading]) }}</h1>
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
                </div>

                <div class="uh-results-tools is-property-listing-tools">
                    <div class="uh-save-search" x-data="uhSavedSearch(@js($savedSearchUrl), @js($resultsTitle), @js([
                        'saved' => __('Search saved on this device.'),
                        'removed' => __('Saved search removed.'),
                        'storageError' => __('Could not save this search in your browser.'),
                    ]))">
                        <button type="button" class="uh-save-search-button" :aria-pressed="saved.toString()" @click="toggle()">
                            <span x-text="saved ? @js(__('Saved')) : @js(__('Save this search'))"></span>
                        </button>
                        <p class="uh-save-search-notice" x-cloak x-show="open" x-transition role="status">
                            <span x-text="notice || @js(__('This search is saved on this device.'))"></span>
                            <button type="button" class="uh-save-search-close" @click="open = false" aria-label="{{ __('Close') }}">
                                <x-icon name="close" class="size-3.5" />
                            </button>
                        </p>
                    </div>

                    <div class="uh-text-toggle" role="group" aria-label="{{ __('Result layout') }}">
                        <button type="button" :aria-pressed="(layout === 'grid').toString()" aria-label="{{ __('Grid view') }}" title="{{ __('Grid view') }}" @click="setLayout('grid')">
                            <x-icon name="grid" class="size-4" /><span class="sr-only">{{ __('Grid') }}</span>
                        </button>
                        <button type="button" :aria-pressed="(layout === 'list').toString()" aria-label="{{ __('List view') }}" title="{{ __('List view') }}" @click="setLayout('list')">
                            <x-icon name="list" class="size-4" /><span class="sr-only">{{ __('List') }}</span>
                        </button>
                    </div>

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
                        <div class="uh-grid-cards uh-featured-grid uh-property-results-grid" x-show="layout === 'grid'">
                            @foreach($properties as $property)
                                @include('public.partials.showcase-slide', [
                                    'property' => $property,
                                    'amenityCatalog' => $amenities->keyBy('id'),
                                    'index' => $loop->index,
                                    'total' => $loop->count,
                                    'listingLayout' => true,
                                    'gridLayout' => true,
                                ])
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
                                <x-ui.pill-link variant="light" :href="route('cms.show', 'contact')">{{ __('Tell us what you need') }}</x-ui.pill-link>
                            @endif
                        </x-ui.empty>
                    @endif
                </div>

                <aside class="uh-workspace-aside">
                    @if($exploreAreas->isNotEmpty())
                        <section class="uh-area-panel" aria-labelledby="explore-areas-title">
                            <header class="uh-explore-panel-head">
                                <span class="uh-explore-panel-icon is-area" aria-hidden="true"><x-icon name="pin" class="size-5" /></span>
                                <span>
                                    <h2 id="explore-areas-title">{{ __('Explore more areas') }}</h2>
                                    <p>{{ __('Find homes around Dhaka') }}</p>
                                </span>
                            </header>
                            <ul class="uh-area-list">
                                @foreach($exploreAreas as $area)
                                    <li>
                                        <a href="{{ route('properties.index', array_filter(['location_area_ids' => [$area->id], 'listing_type' => $listing ?: null])) }}"
                                           @if(in_array($area->id, $selectedAreaIds, true)) aria-current="true" @endif>
                                            <span class="uh-area-row-icon"><x-icon name="pin" class="size-4" /></span>
                                            <span class="uh-area-row-name">{{ $area->name }}</span>
                                            <span class="uh-area-count uh-numeric">{{ $area->properties_count }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if($exploreTypes->isNotEmpty())
                        <section class="uh-explore-panel" aria-labelledby="explore-types-title">
                            <header class="uh-explore-panel-head">
                                <span class="uh-explore-panel-icon" aria-hidden="true"><x-icon name="building" class="size-5" /></span>
                                <span>
                                    <h2 id="explore-types-title">{{ __('Explore by category') }}</h2>
                                    <p>{{ __('Browse by property type') }}</p>
                                </span>
                            </header>
                            <ul class="uh-explore-type-grid">
                                @foreach($exploreTypes as $type)
                                    @php
                                        $typeUrl = route('properties.index', array_merge(
                                            request()->except(['page', 'property_type_ids', 'property_type_id', 'category']),
                                            ['property_type_ids' => [$type->id]],
                                        ));
                                    @endphp
                                    <li>
                                        <a href="{{ $typeUrl }}" @if(in_array($type->id, (array) ($filters['property_type_ids'] ?? []), true)) aria-current="true" @endif>
                                            <span class="uh-explore-type-icon"><x-icon :name="$typeIcons[$type->key] ?? 'building'" class="size-5" /></span>
                                            <span class="uh-explore-type-name">{{ $type->label }}</span>
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
