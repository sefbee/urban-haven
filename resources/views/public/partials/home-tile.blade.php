@php
    $onRequest = $property->isPriceOnRequest();
    $compact = $onRequest ? null : \App\Support\MoneyFormatter::compactBdt($property->price);
    $exact = $onRequest ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headline = $property->listing_type !== 'rent' && $compact ? 'BDT '.$compact : $exact;
    $url = route('properties.show', $property->slug);
    $image = $property->featuredImage();
@endphp

<article class="uh-home-tile">
    <a href="{{ $url }}" class="uh-home-tile-photo" tabindex="-1" aria-hidden="true">
        @if($image)
            <img src="{{ $image->url(768) }}"
                 srcset="{{ $image->url(480) }} 480w, {{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w"
                 sizes="(min-width: 1024px) 360px, 100vw"
                 alt="" loading="lazy" decoding="async">
        @endif
    </a>
    <p class="uh-listing-price">{{ $headline }}</p>
    <h3 class="mt-1 text-base font-semibold tracking-tight">
        <a class="uh-home-link" href="{{ $url }}" lang="{{ app()->getLocale() }}"
           data-track="property_card_click" data-track-property-id="{{ $property->id }}">{{ $property->title }}</a>
    </h3>
    <p class="mt-1 text-sm text-[var(--color-muted)]">
        {{ $property->locationArea?->name }}@if($property->locationArea?->city), {{ $property->locationArea->city }}@endif
    </p>
    <p class="mt-4">
        <a class="uh-home-link text-sm" href="{{ $url }}">{{ __('Explore') }}</a>
    </p>
</article>
