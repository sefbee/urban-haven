@php
    $area = $property->area_value
        ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit)
        : null;
    $onRequest = $property->isPriceOnRequest();
    $compact = $onRequest ? null : \App\Support\MoneyFormatter::compactBdt($property->price);
    $exact = $onRequest ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headline = $property->listing_type !== 'rent' && $compact ? 'BDT '.$compact : $exact;
    $whatsapp = $property->whatsappEnquiryUrl();
    $url = route('properties.show', $property->slug);
    $image = $property->featuredImage();
    $slideIndex = $slideIndex ?? 0;
    $clone = $clone ?? false;
@endphp

<article class="uh-featured-slide"
         data-index="{{ $slideIndex }}"
         @if($clone) data-clone="1" aria-hidden="true" inert @endif
         :data-active="index === {{ $slideIndex }} ? true : null">
    <div class="uh-featured-photo">
        @if($image)
            <a href="{{ $url }}" tabindex="-1" aria-hidden="true" class="absolute inset-0">
                <img src="{{ $image->url(1280) }}"
                     srcset="{{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w, {{ $image->url(1920) }} 1920w"
                     sizes="(min-width: 1024px) 28vw, 70vw"
                     alt=""
                     loading="lazy"
                     decoding="async">
            </a>
        @endif
        @if($property->availability !== 'available')
            <span class="pointer-events-none absolute bottom-5 left-5 z-10">
                <x-ui.status :status="$property->availability" />
            </span>
        @endif
    </div>

    <div class="uh-featured-copy">
        <div class="flex items-start justify-between gap-8">
            <div class="min-w-0">
                <h3 class="text-[1.2rem] font-semibold tracking-tight">
                    <a class="uh-home-link" href="{{ $url }}" lang="{{ app()->getLocale() }}"
                       data-track="property_card_click" data-track-property-id="{{ $property->id }}">{{ $property->title }}</a>
                </h3>
                <p class="mt-1.5 max-w-sm text-[0.8125rem] leading-6 text-[#6e6e73]">
                    {{ $property->listing_type === 'rent' ? __('For rent') : __('For sale') }}
                    @if($property->propertyType?->label)
                        <span aria-hidden="true"> · </span>
                        {{ $property->propertyType->label }}
                    @endif
                    @if($property->locationArea?->name)
                        <span aria-hidden="true"> · </span>
                        {{ $property->locationArea->name }}@if($property->locationArea?->city), {{ $property->locationArea->city }}@endif
                    @endif
                </p>
            </div>
            <div class="shrink-0 text-right">
                <p class="text-[1.2rem] font-semibold tracking-tight">{{ $headline }}</p>
                @if($headline !== $exact)
                    <p class="mt-1 text-[0.8125rem] text-[#6e6e73] uh-numeric">{{ $exact }}</p>
                @endif
                @if($property->bedrooms)
                    <p class="mt-1.5 text-[0.8125rem] text-[#6e6e73]"><span class="uh-numeric">{{ $property->bedrooms }}</span> {{ __('bedrooms') }}</p>
                @endif
                @if($property->bathrooms)
                    <p class="text-[0.8125rem] text-[#6e6e73]"><span class="uh-numeric">{{ $property->bathrooms }}</span> {{ __('bathrooms') }}</p>
                @endif
                @if($area)
                    <p class="text-[0.8125rem] text-[#6e6e73] uh-numeric">{{ $area }}</p>
                @endif
            </div>
        </div>

        @include('public.partials.card-actions', [
            'url' => $url,
            'title' => $property->title,
            'whatsapp' => $property->isUnavailable() ? null : $whatsapp,
            'trackPropertyId' => $property->id,
            'trackLocation' => 'featured',
            'dividerClass' => 'border-black/8',
            'detailsClass' => 'uh-home-link text-sm',
        ])
    </div>
</article>
