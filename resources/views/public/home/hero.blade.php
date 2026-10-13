@php
    $heroTitle = trim($hero['title'] ?? '');
    $coverListing = $heroListing;
    $coverPlace = collect([$coverListing?->locationArea?->name, $coverListing?->locationArea?->city])->filter()->implode(', ');
    $hasHeroImage = $heroImages->isNotEmpty();
@endphp

<section @class(['uh-cover uh-under-bar', 'is-bare' => ! $hasHeroImage]) data-cover aria-label="{{ filled($heroTitle) ? $heroTitle : __('Homepage property search') }}"
         @if($heroImages->count() > 1) x-data="uhCoverSlides({{ $heroImages->count() }}, {{ (int) ($hero['slide_seconds'] ?? 7) }})" @endif>
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
        @if(filled($hero['eyebrow']) || filled($heroTitle) || filled($hero['body']))
            <div class="uh-cover-copy">
                @if(filled($hero['eyebrow']))
                    <p class="uh-cover-eyebrow">{{ $hero['eyebrow'] }}</p>
                @endif
                @if(filled($heroTitle))
                    <h1 id="hero-title" class="uh-display">{{ $heroTitle }}</h1>
                @endif
                @if(filled($hero['body']))
                    <p class="uh-cover-description">{{ $hero['body'] }}</p>
                @endif
            </div>
        @endif
        @include('public.home.find')
        @if(filled($hero['cta_label']) && filled($hero['cta_url']))
            <x-ui.pill-link variant="light" :href="$hero['cta_url']" data-track="home_cta_click" data-track-cta="cover">
                {{ $hero['cta_label'] }}
            </x-ui.pill-link>
        @endif
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
