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
         class="uh-listing" data-spotlight="{{ $property->id }}" @if(isset($revealIndex)) data-reveal style="position: relative; --uh-i: {{ $revealIndex }}" @else style="position: relative;" @endif>
    <a href="{{ $url }}" class="uh-listing-full-link" style="position: absolute; inset: 0; z-index: 1;" data-track="property_card_click" data-track-property-id="{{ $property->id }}"></a>
    <div class="uh-listing-frame" style="position: relative; z-index: 2; pointer-events: none; @if($morph) view-transition-name: uh-property-{{ $property->id }}; @endif">
        <div class="absolute inset-0 z-50" style="pointer-events: auto;">
            @include('public.partials.property-carousel', ['sizes' => $sizes ?? '(min-width: 1024px) 30vw, (min-width: 640px) 50vw, 100vw'])
        </div>

        @unless($priceInBody)
            <div class="uh-listing-image-price-scrim bg-gradient-to-t from-black/60 to-transparent absolute inset-x-0 bottom-0 h-24 z-[2] pointer-events-none"></div>
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
            <span class="uh-listing-tools" style="position: absolute; z-index: 50; pointer-events: auto;">
                <x-save-button :property="$property" class="uh-card-shortlist-action" />
                <x-save-button :property="$property" list="compare" class="uh-card-compare-action" />
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
                    <span class="line-clamp-2 uh-listing-title-link" lang="{{ app()->getLocale() }}">
                       {{ $property->title }}</span>
                </h3>
                @if($priceInBody)
                    <p class="uh-listing-price uh-numeric">{{ $headline }}</p>
                @endif

                <p class="uh-listing-place">
                    <span>{{ $place ?: __('Dhaka') }}</span>
                    @if(! $highlight && $property->propertyType?->label)
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

        <div class="uh-listing-foot" style="position: relative; z-index: 10; pointer-events: auto;">
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
