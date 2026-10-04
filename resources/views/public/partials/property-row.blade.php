@php
    $onRequest = $property->isPriceOnRequest();
    $compact = $onRequest ? null : \App\Support\MoneyFormatter::compactBdt($property->price);
    $exact = $onRequest ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headline = $property->listing_type !== 'rent' && $compact ? 'BDT '.$compact : $exact;
    $whatsapp = $property->whatsappEnquiryUrl();
    $preview = \App\Support\PropertyPreview::for($property);
    $place = collect([$property->locationArea?->name, $property->locationArea?->city])->filter()->implode(', ');
    $story = \App\Support\PropertyStory::for($property);
    $facts = $story->facts();
@endphp

<article x-data="{ payload: {{ \Illuminate\Support\Js::from($preview) }} }" class="uh-listing uh-listing-row" data-spotlight="{{ $property->id }}">
    <div class="uh-listing-frame">
        @include('public.partials.property-carousel', ['sizes' => '(min-width: 1280px) 320px, (min-width: 640px) 272px, 100vw'])

        @if($property->availability !== 'available')
            <span class="uh-listing-top">
                <x-ui.status :status="$property->availability" />
            </span>
        @endif
        <span class="uh-listing-tools">
            <x-save-button :property="$property" />
            <x-save-button :property="$property" list="compare" />
        </span>
    </div>

    <div class="uh-listing-body">
        <h2 class="uh-listing-title">
            <a class="line-clamp-2" href="{{ route('properties.show', $property->slug) }}" lang="{{ app()->getLocale() }}"
               data-track="property_card_click" data-track-property-id="{{ $property->id }}">{{ $property->title }}</a>
        </h2>

        <p class="uh-listing-place">
            <span>{{ $place ?: __('Dhaka') }}</span>
            @if($property->propertyType)
                <span class="uh-listing-kind">{{ $property->propertyType->label }}</span>
            @endif
        </p>

        @if($facts)
            <p class="uh-listing-facts uh-numeric">
                @foreach($facts as $fact)
                    <span>{{ $fact }}</span>
                @endforeach
            </p>
        @endif

        <p class="uh-listing-price uh-numeric">{{ $headline }}</p>

        <p class="uh-listing-ref">
            <span>{{ __('Ref') }} {{ $property->reference ?: $property->id }}</span>
            @if($property->last_updated_at)
                <span>{{ __('Updated :time', ['time' => $property->last_updated_at->diffForHumans()]) }}</span>
            @endif
        </p>

        @include('public.partials.card-actions', [
            'url' => route('properties.show', $property->slug),
            'title' => $property->title,
            'whatsapp' => $property->isUnavailable() ? null : $whatsapp,
            'trackPropertyId' => $property->id,
            'trackLocation' => 'list',
            'showQuickView' => true,
            'detailsLabel' => $story->exploreLabel(),
        ])
    </div>
</article>
