@extends('layouts.public')

@php
    $listViewUrl = route('properties.index', request()->except('page'));
@endphp

@section('content')
    <div class="uh-mapview" x-data="uhBrowse({{ $hasAdvanced ? 'true' : 'false' }})"
         @open-preview="openPreview($event.detail)"
         @keydown.escape.window="onEscape()"
         @keydown.left.window="preview && previousSlide()"
         @keydown.right.window="preview && nextSlide()">

        <section class="uh-mapview-map" aria-label="{{ __('Map of matching homes') }}">
            <div class="uh-mapview-frame">
                <div data-uh-map class="uh-mapview-canvas"
                     data-lat="{{ config('urbanhaven.maps.default_lat') }}"
                     data-lng="{{ config('urbanhaven.maps.default_lng') }}"
                     data-zoom="12"
                     data-scroll-zoom="true"
                     data-tiles="{{ config('urbanhaven.maps.tile_url') }}"
                     data-attribution="{{ config('urbanhaven.maps.attribution') }}"
                     data-src="{{ $mapDataUrl }}"></div>
                @if(count($purposes) > 1)
                    <p class="uh-map-legend" aria-hidden="true">
                        <span><i></i>{{ __('For sale') }}</span>
                        <span><i class="is-rent"></i>{{ __('For rent') }}</span>
                    </p>
                @endif
            </div>
            <a class="uh-mapview-back" href="{{ $listViewUrl }}">
                <x-icon name="list" class="size-4" />
                {{ __('List view') }}
            </a>
        </section>

        <section class="uh-mapview-list" aria-labelledby="map-title">
            <header class="uh-mapview-head">
                <x-ui.breadcrumbs :items="[
                    ['label' => __('Home'), 'url' => route('home')],
                    ['label' => __('Properties'), 'url' => $listViewUrl],
                    ['label' => __('Map')],
                ]" />
                <h1 id="map-title" class="uh-mapview-title">{{ __('Homes on the map') }}</h1>
            </header>

            @include('public.partials.filter-bar', ['browseRoute' => 'map'])

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
                    @include('public.partials.sort-select')
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
                        <div class="uh-mapview-grid">
                            @foreach($properties as $property)
                                @include('public.partials.property-card', ['property' => $property, 'sizes' => '(min-width: 1100px) 22vw, (min-width: 640px) 50vw, 100vw'])
                            @endforeach
                        </div>

                        @if($properties->hasPages())
                            <div class="uh-pagination-wrap">{{ $properties->onEachSide(1)->links() }}</div>
                        @endif
                    @else
                        <x-ui.empty icon="map" :title="__('Nothing matches this search yet')"
                                    :description="__('Nothing we have right now meets every filter at once. Loosening one of them usually opens things up.')">
                            <a class="uh-btn-primary uh-btn-sm" href="{{ route('map') }}">{{ __('Start over with everything') }}</a>
                        </x-ui.empty>
                    @endif
                </div>
            </div>
        </section>

        @include('public.partials.property-preview')
    </div>
@endsection
