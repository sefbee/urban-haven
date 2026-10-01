@extends('layouts.public')

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container py-8 md:py-10">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => $area->name],
            ]" />
            <h1 class="uh-h1 mt-3">{{ __('Property in :area, :city', ['area' => $area->name, 'city' => $area->city]) }}</h1>
            <div class="uh-prose mt-4 max-w-3xl">{!! nl2br(e($area->intro)) !!}</div>
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach($purposes as $purpose)
                    <a class="uh-btn-outline uh-btn-sm" href="{{ route('properties.index', ['location_area_id' => $area->id, 'listing_type' => $purpose]) }}">
                        {{ $purpose === 'rent' ? __('Properties for rent in :area', ['area' => $area->name]) : __('Properties for sale in :area', ['area' => $area->name]) }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="uh-container uh-section-tight">
        <h2 class="uh-h2">{{ __('Available in :area', ['area' => $area->name]) }}</h2>
        @if($listings->isNotEmpty())
            <div class="uh-discover-grid mt-6">
                @foreach($listings as $property)
                    @include('public.partials.property-card', ['property' => $property])
                @endforeach
            </div>
            <div class="mt-8">
                <a class="uh-btn-primary" href="{{ route('properties.index', ['location_area_id' => $area->id]) }}">{{ __('See all properties in :area', ['area' => $area->name]) }}</a>
            </div>
        @else
            <x-ui.empty class="mt-6" icon="pin" :title="__('Nothing available in :area right now', ['area' => $area->name])"
                        :description="__('New properties are added regularly. Tell us what you need and we will let you know.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('cms.show', 'contact') }}">{{ __('Tell us what you need') }}</a>
            </x-ui.empty>
        @endif

        @if($projects->isNotEmpty())
            <h2 class="uh-h2 mt-14">{{ __('Projects in :area', ['area' => $area->name]) }}</h2>
            <div class="uh-discover-grid mt-6">
                @foreach($projects as $project)
                    @include('public.partials.project-card', ['project' => $project])
                @endforeach
            </div>
        @endif
    </div>
@endsection
