@php
    $heroTitle = trim($hero['title'] ?? __('Considered places to live in Dhaka.'));
    $coverListing = $heroListing;
    $coverPlace = collect([$coverListing?->locationArea?->name, $coverListing?->locationArea?->city])->filter()->implode(', ');
    $hasHeroImage = $heroImages->isNotEmpty();
@endphp

<section @class(['uh-cover uh-under-bar', 'is-bare' => ! $hasHeroImage]) data-cover aria-labelledby="hero-title"
         @if($heroImages->count() > 1) x-data="uhCoverSlides({{ $heroImages->count() }})" @endif>
    @if($hasHeroImage)
        <div class="uh-cover-media">
            @foreach($heroImages as $heroImage)
                <span @class(['uh-cover-slide', 'is-on' => $loop->first])
                      @if($heroImages->count() > 1) :class="{ 'is-on': active === {{ $loop->index }} }" @endif>
                    <img src="{{ $heroImage->url(1920) }}"
                         srcset="{{ $heroImage->url(768) }} 768w, {{ $heroImage->url(1280) }} 1280w, {{ $heroImage->url(1920) }} 1920w"
                         sizes="100vw"
                         alt="{{ $loop->first ? ($heroImage->alt(app()->getLocale()) ?: '') : '' }}"
                         @if($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                </span>
            @endforeach
        </div>
        <div class="uh-cover-veil" aria-hidden="true"></div>
    @endif

    <div class="uh-cover-inner">
        <h1 id="hero-title" class="sr-only">{{ $heroTitle }}</h1>
        @include('public.home.find')
        <a class="uh-cover-link" href="{{ route('properties.index') }}" data-track="home_cta_click" data-track-cta="cover">
            {{ __('Explore properties') }}
            <x-icon name="arrow-right" class="size-3.5" />
        </a>
    </div>

    @if($heroImages->count() > 1)
        <div class="uh-cover-dots" role="group" aria-label="{{ __('Background photos') }}">
            @foreach($heroImages as $heroImage)
                <button type="button" class="uh-cover-dot" :class="{ 'is-on': active === {{ $loop->index }} }"
                        :aria-pressed="(active === {{ $loop->index }}).toString()" @click="show({{ $loop->index }})"
                        aria-label="{{ __('Show photo :number of :total', ['number' => $loop->iteration, 'total' => $loop->count]) }}"></button>
            @endforeach
        </div>
    @endif

    @if($coverListing && $hasHeroImage)
        <a class="uh-cover-caption" href="{{ route('properties.show', $coverListing->slug) }}"
           data-track="property_card_click" data-track-property-id="{{ $coverListing->id }}">
            <span lang="{{ app()->getLocale() }}">{{ $coverListing->title }}</span>@if($coverPlace)<span>{{ $coverPlace }}</span>@endif
        </a>
    @endif
</section>
