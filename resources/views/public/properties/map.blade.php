@extends('layouts.public')

@php
    $listViewUrl = route('properties.index', request()->except('page'));
    $amenityCatalog = $amenities->keyBy('id');
@endphp

@section('content')
    <div class="uh-mapview" x-data="uhBrowse({{ $hasAdvanced ? 'true' : 'false' }})"
         :class="{
            'is-map-full': mapMode === 'full',
            'is-map-hidden': mapMode === 'hidden',
         }"
         @open-preview="openPreview($event.detail)"
         @keydown.escape.window="onEscape()"
         @keydown.left.window="preview && previousSlide()"
         @keydown.right.window="preview && nextSlide()">

        @include('public.partials.filter-bar', ['browseRoute' => 'map'])

        <section id="uh-mapview-map" class="uh-mapview-map" aria-label="{{ __('Map of matching homes') }}"
                 :aria-hidden="(mapMode === 'hidden').toString()" :inert="mapMode === 'hidden'">
            
            <div class="uh-mapview-frame">
                <div data-uh-map class="uh-mapview-canvas"
                     data-lat="{{ config('urbanhaven.maps.default_lat') }}"
                     data-lng="{{ config('urbanhaven.maps.default_lng') }}"
                     data-zoom="12"
                     data-scroll-zoom="true"
                     data-tiles="{{ config('urbanhaven.maps.tile_url') }}"
                     data-attribution="{{ config('urbanhaven.maps.attribution') }}"
                     data-src="{{ $mapDataUrl }}"></div>
                <button type="button" 
                        class="absolute bottom-4 right-4 z-[1000] flex size-10 items-center justify-center bg-white rounded-md shadow-[0_2px_8px_rgb(0,0,0,0.1)] border border-gray-200 text-gray-700 hover:text-black hover:bg-gray-50 transition-colors"
                        @click="mapMode = (mapMode === 'full' ? 'split' : 'full'); $nextTick(() => { window.dispatchEvent(new Event('uh:refresh-maps')); setTimeout(() => window.dispatchEvent(new Event('uh:refresh-maps')), 300); })"
                        :aria-label="mapMode === 'full' ? '{{ __('Exit full screen map') }}' : '{{ __('Show full screen map') }}'"
                        :title="mapMode === 'full' ? '{{ __('Exit full screen map') }}' : '{{ __('Show full screen map') }}'">
                    <x-icon name="minimize" class="size-4" x-show="mapMode === 'full'" x-cloak />
                    <x-icon name="expand" class="size-4" x-show="mapMode !== 'full'" />
                </button>
                @if(count($purposes) > 1)
                    <p class="uh-map-legend" aria-hidden="true">
                        <span><i></i>{{ __('For sale') }}</span>
                        <span><i class="is-rent"></i>{{ __('For rent') }}</span>
                </p>
                @endif
            </div>

            <button type="button" 
                    class="absolute top-1/2 -right-[1.125rem] z-[1200] flex h-14 w-[1.125rem] items-center justify-center rounded-r-lg bg-white text-slate-500 shadow-[2px_0_8px_rgb(0,0,0,0.1)] border border-l-0 border-slate-200 hover:text-slate-800 hover:bg-slate-50 transition-colors -translate-y-1/2"
                    x-show="mapMode === 'split'" x-cloak
                    @click="mapMode = 'hidden'"
                    aria-label="{{ __('Hide map') }}" title="{{ __('Hide map') }}">
                <x-icon name="chevron-left" class="size-4" />
            </button>

        </section>

        <section class="uh-mapview-list" aria-labelledby="map-title">
            <div class="fixed left-1/2 -translate-x-1/2 z-[1500] transition-[bottom] duration-200"
                 :style="{ bottom: $store.saved.compare.length > 1 ? ($store.saved.notice ? '8rem' : '5.25rem') : ($store.saved.notice ? '5.25rem' : '2rem') }"
                 x-show="mapMode === 'hidden'" x-cloak>
                <button type="button" 
                        class="flex items-center gap-3 bg-black text-white rounded-full pl-5 pr-1.5 py-1.5 shadow-lg hover:bg-gray-900 transition-colors"
                        @click="mapMode = 'split'; $nextTick(() => { window.dispatchEvent(new Event('uh:refresh-maps')); setTimeout(() => window.dispatchEvent(new Event('uh:refresh-maps')), 300); })"
                        aria-label="{{ __('Show map') }}">
                    <span class="font-medium text-[0.9375rem]">{{ __('Show map') }}</span>
                    <span class="bg-white text-black rounded-full size-8 flex items-center justify-center">
                        <i class="fa-duotone fa-map"></i>
                    </span>
                </button>
            </div>
            <div class="uh-mapview-results">
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
                            <button type="button" :aria-pressed="(layout === 'grid').toString()"
                                    aria-label="{{ __('Grid view') }}" title="{{ __('Grid view') }}"
                                    @click="setLayout('grid')">
                                <x-icon name="grid" class="size-4" />
                                <span class="sr-only">{{ __('Grid') }}</span>
                            </button>
                            <button type="button" :aria-pressed="(layout === 'list').toString()"
                                    aria-label="{{ __('List view') }}" title="{{ __('List view') }}"
                                    @click="setLayout('list')">
                                <x-icon name="list" class="size-4" />
                                <span class="sr-only">{{ __('List') }}</span>
                            </button>
                        </div>
                        @include('public.partials.sort-select')
                    </div>
                </div>

                @if(filled($activeFilters))
                    <div class="uh-active-filters">
                        @foreach($activeFilters as $chip)
                            <a class="uh-chip uh-chip-active" href="{{ str_replace(route('properties.index'), route('map'), $chip['url']) }}">
                                {{ $chip['label'] }}
                                <span class="uh-chip-remove" aria-hidden="true"><x-icon name="close" class="size-3" /></span>
                                <span class="sr-only">{{ __('Remove filter') }}</span>
                            </a>
                        @endforeach
                        <a class="uh-btn-text" href="{{ route('map') }}">{{ __('Clear all') }}</a>
                    </div>
                @endif

                <div class="uh-workspace-results mt-5" :class="submitting ? 'is-busy' : ''" :aria-busy="submitting.toString()">
                    @if($properties->isNotEmpty())
                        <div class="uh-mapview-grid" x-show="layout === 'grid'">
                            @foreach($properties as $index => $property)
                                @include('public.partials.showcase-slide', [
                                    'property' => $property,
                                    'amenityCatalog' => $amenityCatalog,
                                    'index' => $index,
                                    'total' => $properties->count(),
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
                        <x-ui.empty icon="map" :title="__('Nothing matches this search yet')"
                                    :description="__('Nothing we have right now meets every filter at once. Loosening one of them usually opens things up.')">
                            <x-ui.pill-link variant="light" :href="route('map')">{{ __('Start over with everything') }}</x-ui.pill-link>
                        </x-ui.empty>
                    @endif
                </div>
            </div>
        </section>

        @include('public.partials.property-preview')
    </div>
@endsection
