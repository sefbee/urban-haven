@php
    $showShortlist = $showShortlist ?? true;
    $morph = $morph ?? true;
    $onRequest = $property->isPriceOnRequest();
    $compact = $onRequest ? null : \App\Support\MoneyFormatter::compactBdt($property->price);
    $exact = $onRequest ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headline = $property->listing_type !== 'rent' && $compact ? 'BDT '.$compact : $exact;
    $whatsapp = $property->whatsappEnquiryUrl();
    $url = route('properties.show', $property->slug);
    $place = collect([$property->locationArea?->name, $property->locationArea?->city])->filter()->implode(', ');
    $facts = \App\Support\PropertyStory::for($property)->facts();
@endphp

<article class="uh-listing" data-spotlight="{{ $property->id }}" @isset($revealIndex) data-reveal style="--uh-i: {{ $revealIndex }}" @endisset>
    <div class="uh-listing-frame" @if($morph) style="view-transition-name: uh-property-{{ $property->id }}" @endif>
        @include('public.partials.property-carousel', ['sizes' => $sizes ?? '(min-width: 1024px) 30vw, (min-width: 640px) 50vw, 100vw'])

        <p class="uh-listing-image-price uh-numeric">{{ $headline }}</p>

        @if($property->availability !== 'available')
            <span class="uh-listing-top">
                <x-ui.status :status="$property->availability" />
            </span>
        @endif

        @if($showShortlist)
            <span class="uh-listing-tools">
                <x-save-button :property="$property" />
                <x-save-button :property="$property" list="compare" />
            </span>
        @endif
    </div>

    <div class="uh-listing-body">
        <div class="uh-listing-content">
            <div class="uh-listing-description">
                <h3 class="uh-listing-title">
                    <a class="line-clamp-2" href="{{ $url }}" lang="{{ app()->getLocale() }}"
                       data-track="property_card_click" data-track-property-id="{{ $property->id }}">{{ $property->title }}</a>
                </h3>

                <p class="uh-listing-place">
                    <span>{{ $place ?: __('Dhaka') }}</span>
                    @if($property->propertyType?->label)
                        <span class="uh-listing-kind">{{ $property->propertyType->label }}</span>
                    @endif
                </p>
            </div>

            @if($facts)
                <p class="uh-listing-facts uh-numeric">
                    @foreach($facts as $fact)
                        <span>{{ $fact }}</span>
                    @endforeach
                </p>
            @endif
        </div>

        <div class="uh-listing-foot">
            @include('public.partials.card-actions', [
                'url' => $url,
                'title' => $property->title,
                'whatsapp' => $property->isUnavailable() ? null : $whatsapp,
                'trackPropertyId' => $property->id,
                'trackLocation' => 'card',
                'showDetails' => false,
            ])
        </div>
    </div>
</article>
