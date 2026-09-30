@extends('layouts.public')

@php
    $groups = [
        __('Price') => ['price'],
        __('Size & layout') => ['area', 'bedrooms', 'bathrooms', 'floor_number', 'is_furnished'],
        __('Location & type') => ['area_name', 'city', 'type'],
        __('Availability') => ['listing_type', 'availability'],
    ];
    $labels = [
        'price' => __('Asking price'),
        'area' => __('Size'),
        'bedrooms' => __('Bedrooms'),
        'bathrooms' => __('Bathrooms'),
        'floor_number' => __('Floor'),
        'is_furnished' => __('Furnishing'),
        'area_name' => __('Area'),
        'city' => __('City'),
        'type' => __('Property type'),
        'listing_type' => __('Listing'),
        'availability' => __('Status'),
    ];
@endphp

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container py-8 md:py-10">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Shortlist')],
            ]" />
            <h1 class="uh-h1 mt-3">{{ __('Your shortlist') }}</h1>
            <p class="uh-lede mt-3 max-w-2xl">
                {{ __('Save up to four homes here and compare them side by side. Your shortlist stays on this device.') }}
            </p>
        </div>
    </div>

    <div class="uh-container uh-section-tight">
        @if($properties->isEmpty())
            <x-ui.empty icon="heart" :title="__('Nothing shortlisted yet')"
                        :description="__('Use the shortlist button on any home to save it here, then compare the ones you like most.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Browse homes') }}</a>
            </x-ui.empty>
        @else
            {{-- Shortlisted homes --}}
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($properties as $property)
                    @php($image = $property->featuredImage())
                    <div class="uh-card overflow-hidden">
                        <a href="{{ route('properties.show', $property->slug) }}" class="uh-media aspect-4/3 block">
                            @if($image)
                                <img src="{{ $image->url(480) }}" alt="{{ $image->alt(app()->getLocale()) }}" loading="lazy" decoding="async">
                            @else
                                <span class="uh-media-placeholder"><x-icon name="image" class="size-7" /></span>
                            @endif
                        </a>
                        <div class="p-4">
                            <h2 class="text-sm font-semibold leading-snug">
                                <a class="uh-link-quiet" href="{{ route('properties.show', $property->slug) }}">{{ $property->title }}</a>
                            </h2>
                            <p class="uh-price mt-2 text-forest">{{ \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis) }}</p>
                            <form method="POST" action="{{ route('shortlist.remove', $property->id) }}" class="mt-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="uh-btn-ghost uh-btn-sm px-0 text-[var(--color-danger)]">
                                    <x-icon name="trash" class="size-3.5" />
                                    {{ __('Remove') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($properties->count() < 2)
                <div class="uh-alert uh-alert-info mt-7">
                    <x-icon name="info" class="mt-px size-4 shrink-0" />
                    <p>{{ __('Shortlist at least one more home to see them compared side by side.') }}</p>
                </div>
            @else
                {{-- Comparison --}}
                <section class="mt-10" aria-labelledby="compare-heading">
                    <h2 id="compare-heading" class="uh-h2">{{ __('Side by side') }}</h2>
                    <p class="mt-2 text-sm text-[var(--color-muted)] sm:hidden">{{ __('Scroll the table sideways to see every home.') }}</p>

                    <div class="uh-panel-flush mt-4 overflow-hidden">
                        <div class="uh-table-scroll">
                            <table class="uh-table">
                                <caption class="sr-only">{{ __('Comparison of shortlisted homes') }}</caption>
                                <thead>
                                    <tr>
                                        <th scope="col" class="sticky left-0 z-10 bg-sand">{{ __('Detail') }}</th>
                                        @foreach($properties as $property)
                                            <th scope="col" class="min-w-44">{{ $property->title }}</th>
                                        @endforeach
                                    </tr>
                                </thead>

                                @foreach($groups as $group => $keys)
                                    <tbody>
                                        <tr>
                                            <th scope="colgroup" colspan="{{ $properties->count() + 1 }}"
                                                class="bg-sand/60 text-left text-[0.6875rem] font-semibold uppercase tracking-[0.14em] text-[var(--color-muted)]">
                                                {{ $group }}
                                            </th>
                                        </tr>
                                        @foreach($keys as $key)
                                            <tr>
                                                <th scope="row" class="sticky left-0 z-10 bg-paper text-left font-medium">{{ $labels[$key] }}</th>
                                                @foreach($properties as $property)
                                                    <td>
                                                        @switch($key)
                                                            @case('price')
                                                                <span class="uh-numeric font-semibold text-forest">{{ \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis) }}</span>
                                                                @break
                                                            @case('area')
                                                                {{ $property->area_value ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit) : '—' }}
                                                                @break
                                                            @case('is_furnished')
                                                                {{ $property->is_furnished ? __('Furnished') : __('Unfurnished') }}
                                                                @break
                                                            @case('area_name')
                                                                {{ $property->locationArea?->name ?? '—' }}
                                                                @break
                                                            @case('city')
                                                                {{ $property->locationArea?->city ?? '—' }}
                                                                @break
                                                            @case('type')
                                                                {{ $property->propertyType?->label ?? '—' }}
                                                                @break
                                                            @case('listing_type')
                                                                {{ $property->listing_type === 'rent' ? __('For rent') : __('For sale') }}
                                                                @break
                                                            @case('availability')
                                                                <x-ui.status :status="$property->availability" />
                                                                @break
                                                            @default
                                                                <span class="uh-numeric">{{ $property->{$key} ?? '—' }}</span>
                                                        @endswitch
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @endforeach
                            </table>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a class="uh-btn-outline uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Keep browsing') }}</a>
                    </div>
                </section>
            @endif
        @endif
    </div>
@endsection
