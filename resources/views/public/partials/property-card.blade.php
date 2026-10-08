@php
    $showShortlist = $showShortlist ?? true;
    $morph = $morph ?? true;
    $quickView = $quickView ?? false;
    $showDetails = $showDetails ?? $quickView;
    $showContact = $showContact ?? true;
    $showFavoriteAction = $showFavoriteAction ?? false;
    $priceInBody = $priceInBody ?? false;
    $detailsLabel = $detailsLabel ?? __('Explore');
    $detailsClass = $detailsClass ?? 'uh-arrow-link';
    $detailsIconOnly = $detailsIconOnly ?? false;
    $highlight = $highlight ?? false;
    $onRequest = $property->isPriceOnRequest();
    $compact = $onRequest ? null : \App\Support\MoneyFormatter::compactBdt($property->price);
    $exact = $onRequest ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headline = $property->listing_type !== 'rent' && $compact ? 'BDT '.$compact : $exact;
    $whatsapp = $property->whatsappEnquiryUrl();
    $url = route('properties.show', $property->slug);
    $place = collect([$property->locationArea?->name, $property->locationArea?->city])->filter()->implode(', ');
    $facts = \App\Support\PropertyStory::for($property)->facts();
@endphp

<article @if($quickView) x-data="{ payload: {{ \Illuminate\Support\Js::from(\App\Support\PropertyPreview::for($property)) }} }" @endif
         class="uh-listing" data-spotlight="{{ $property->id }}" @isset($revealIndex) data-reveal style="--uh-i: {{ $revealIndex }}" @endisset>
    <div class="uh-listing-frame" @if($morph) style="view-transition-name: uh-property-{{ $property->id }}" @endif>
        @include('public.partials.property-carousel', ['sizes' => $sizes ?? '(min-width: 1024px) 30vw, (min-width: 640px) 50vw, 100vw'])

        @unless($priceInBody)
            <p class="uh-listing-image-price uh-numeric">{{ $headline }}</p>
        @endunless

        @if($highlight)
            <span class="uh-listing-trending-mark" aria-label="{{ __('Trending property') }}">
                <x-icon name="star" class="size-5" />
            </span>
            @if($property->availability === 'available')
                <span class="uh-listing-available-mark" aria-label="{{ __('Available') }}">
                    <x-icon name="check" class="size-3.5" />
                </span>
            @endif
        @endif

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
        @if($highlight)
            <div class="uh-listing-highlights" aria-label="{{ __('Property highlights') }}">
                @if($property->is_featured)
                    <span class="uh-listing-highlight is-featured">{{ __('Featured') }}</span>
                @endif
                @if($property->propertyType?->label)
                    <span class="uh-listing-highlight">{{ $property->propertyType->label }}</span>
                @endif
                <span class="uh-listing-highlight {{ $property->listing_type === 'rent' ? 'is-rent' : '' }}">
                    {{ $property->listing_type === 'rent' ? __('For Rent') : __('For Sale') }}
                </span>
            </div>
        @endif

        <div class="uh-listing-content">
            <div class="uh-listing-description">
                <h3 class="uh-listing-title">
                    <a class="line-clamp-2" href="{{ $url }}" lang="{{ app()->getLocale() }}"
                       data-track="property_card_click" data-track-property-id="{{ $property->id }}">{{ $property->title }}</a>
                </h3>
                @if($priceInBody)
                    <p class="uh-listing-price uh-numeric">{{ $headline }}</p>
                @endif

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
                'showContact' => $showContact,
                'showQuickView' => $quickView,
                'iconActions' => $quickView,
                'showDetails' => $showDetails,
                'showFavoriteAction' => $showFavoriteAction,
                'property' => $property,
                'detailsLabel' => $detailsLabel,
                'detailsClass' => $detailsClass,
                'detailsIconOnly' => $detailsIconOnly,
            ])
        </div>
    </div>
</article>
