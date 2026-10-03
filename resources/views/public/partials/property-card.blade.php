@php
    $showShortlist = $showShortlist ?? true;
    $area = $property->area_value
        ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit)
        : null;
    $onRequest = $property->isPriceOnRequest();
    $compact = $onRequest ? null : \App\Support\MoneyFormatter::compactBdt($property->price);
    $exact = $onRequest ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headline = $property->listing_type !== 'rent' && $compact ? 'BDT '.$compact : $exact;
    $whatsapp = $property->whatsappEnquiryUrl();
    $listingId = $property->reference ?: (string) $property->id;
    $url = route('properties.show', $property->slug);
@endphp

<article class="uh-home-tile">
    <div class="uh-home-tile-photo relative">
        @include('public.partials.property-carousel', ['sizes' => '(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw'])

        <span class="pointer-events-none absolute inset-x-3 top-3 z-10 flex flex-wrap items-start justify-between gap-2">
            <x-ui.badge>{{ $property->listing_type === 'rent' ? __('For rent') : __('For sale') }}</x-ui.badge>
            <span class="rounded-full bg-white/90 px-2.5 py-1 text-[0.6875rem] font-medium tracking-wide text-[#1d1d1f]">{{ $listingId }}</span>
        </span>
        @if($property->availability !== 'available')
            <span class="pointer-events-none absolute bottom-3 left-3 z-10">
                <x-ui.status :status="$property->availability" />
            </span>
        @endif
        @if($showShortlist)
            <x-save-button :property="$property" class="absolute bottom-3 right-3 z-20 rounded-full bg-white shadow-sm" />
        @endif
    </div>

    <p class="mt-4 text-lg font-semibold tracking-tight">{{ $headline }}</p>
    @if($headline !== $exact)
        <p class="mt-0.5 text-xs text-[var(--color-muted)] uh-numeric">{{ $exact }}</p>
    @endif

    <h3 class="mt-1 text-base font-semibold tracking-tight">
        <a class="uh-home-link line-clamp-2" href="{{ $url }}" lang="{{ app()->getLocale() }}"
           data-track="property_card_click" data-track-property-id="{{ $property->id }}">{{ $property->title }}</a>
    </h3>

    <p class="mt-1 truncate text-sm text-[var(--color-muted)]">
        @if($property->propertyType?->label)
            {{ $property->propertyType->label }}
            <span aria-hidden="true"> · </span>
        @endif
        {{ $property->locationArea?->name }}@if($property->locationArea?->city), {{ $property->locationArea->city }}@endif
    </p>

    <ul class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm text-[var(--color-muted)]">
        @if($area)
            <li class="flex items-center gap-1.5"><x-icon name="area" class="size-4 shrink-0" /><span class="uh-numeric">{{ $area }}</span></li>
        @endif
        @if($property->bedrooms)
            <li class="flex items-center gap-1.5"><x-icon name="bed" class="size-4 shrink-0" /><span class="uh-numeric">{{ $property->bedrooms }}</span> {{ __('bed') }}</li>
        @endif
        @if($property->bathrooms)
            <li class="flex items-center gap-1.5"><x-icon name="bath" class="size-4 shrink-0" /><span class="uh-numeric">{{ $property->bathrooms }}</span> {{ __('bath') }}</li>
        @endif
    </ul>

    @include('public.partials.card-actions', [
        'url' => $url,
        'title' => $property->title,
        'whatsapp' => $property->isUnavailable() ? null : $whatsapp,
        'trackPropertyId' => $property->id,
        'trackLocation' => 'card',
    ])
</article>
