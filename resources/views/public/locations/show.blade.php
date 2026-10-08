@extends('layouts.public')

@php
    $hasMap = filled($area->lat) && filled($area->lng);
@endphp

@section('content')
    <header class="uh-page-head">
        <div class="uh-container">
            <div @class(['uh-area-intro', 'has-map' => $hasMap])>
                <div class="min-w-0">
                    <h1 class="uh-h1 uh-page-title">{{ __('Property in :area, :city', ['area' => $area->name, 'city' => $area->city]) }}</h1>
                    <div class="uh-prose uh-prose-lead">{!! nl2br(e($area->intro)) !!}</div>
                    @if($purposes)
                        <div class="uh-next-links mt-8">
                            @foreach($purposes as $purpose)
                                <a class="uh-arrow-link" href="{{ route('properties.index', ['location_area_id' => $area->id, 'listing_type' => $purpose]) }}">
                                    {{ $purpose === 'rent' ? __('Properties for rent in :area', ['area' => $area->name]) : __('Properties for sale in :area', ['area' => $area->name]) }}
                                    <x-icon name="arrow-right" class="size-3.5" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($hasMap)
                    <div>
                        <div class="uh-map-frame">
                            <div class="h-80 w-full lg:h-[28rem]" role="region" aria-label="{{ __('Map of :area', ['area' => $area->name]) }}"
                                 data-uh-map data-lat="{{ $area->lat }}" data-lng="{{ $area->lng }}"
                                 data-zoom="14" data-pin="true"
                                 data-tiles="{{ config('urbanhaven.maps.tile_url') }}"
                                 data-attribution="{{ config('urbanhaven.maps.attribution') }}"></div>
                        </div>
                        <p class="uh-map-note">{{ __('The map marks the neighbourhood. Exact addresses are shared by our sales team.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </header>

    <div class="uh-container uh-section-tight">
        <header class="uh-section-head">
            <h2 class="uh-h2">{{ __('Available in :area', ['area' => $area->name]) }}</h2>
            @if($listings->isNotEmpty())
                <a class="uh-arrow-link" href="{{ route('properties.index', ['location_area_id' => $area->id]) }}">
                    {{ __('See all properties in :area', ['area' => $area->name]) }}
                    <x-icon name="arrow-right" class="size-3.5" />
                </a>
            @endif
        </header>

        @if($listings->isNotEmpty())
            <div class="uh-grid-cards">
                @foreach($listings as $property)
                    @include('public.partials.property-card', ['property' => $property, 'revealIndex' => $loop->index % 3])
                @endforeach
            </div>
        @else
            <x-ui.empty icon="pin" :title="__('Nothing available in :area right now', ['area' => $area->name])"
                        :description="__('New properties are added regularly. Tell us what you need and we will let you know.')">
                <x-ui.pill-link variant="light" :href="route('cms.show', 'contact')">{{ __('Tell us what you need') }}</x-ui.pill-link>
            </x-ui.empty>
        @endif
    </div>
@endsection
