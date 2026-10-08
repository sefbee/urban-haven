@php
    $exact = $property->isPriceOnRequest()
        ? __('Price on request')
        : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $whatsapp = $property->whatsappEnquiryUrl();
    $preview = \App\Support\PropertyPreview::for($property);
    $place = collect([$property->address ?: $property->locationArea?->name, $property->locationArea?->city])
        ->filter()
        ->unique()
        ->implode(', ');
    $url = route('properties.show', $property->slug);
@endphp

<article x-data="{ payload: {{ \Illuminate\Support\Js::from($preview) }} }" class="uh-listing uh-listing-row" data-spotlight="{{ $property->id }}">
    <div class="uh-listing-frame">
        @include('public.partials.property-carousel', ['sizes' => '(min-width: 1280px) 320px, (min-width: 640px) 272px, 100vw'])

        @if($property->is_featured)
            <span class="uh-listing-row-star" aria-label="{{ __('Featured property') }}">
                <x-icon name="star" class="size-5" />
            </span>
        @endif

        @if($property->availability !== 'available')
            <span class="uh-listing-top">
                <x-ui.status :status="$property->availability" />
            </span>
        @endif

        <span class="uh-listing-tools">
            <x-save-button :property="$property" />
            <button type="button" class="uh-icon-action" @click="$dispatch('open-preview', payload)"
                    aria-label="{{ __('Quick view') }}" title="{{ __('Quick view') }}">
                <x-icon name="expand" class="size-4" />
            </button>
        </span>
    </div>

    <div class="uh-listing-body uh-listing-row-body">
        <div class="uh-listing-row-highlights" aria-label="{{ __('Property highlights') }}">
            @if($property->is_featured)
                <span class="uh-listing-row-badge is-featured">{{ __('Featured') }}</span>
            @endif
            @if($property->propertyType?->label)
                <span class="uh-listing-row-badge is-type">{{ $property->propertyType->label }}</span>
            @endif
            <span class="uh-listing-row-badge is-purpose">
                {{ $property->listing_type === 'rent' ? __('Rent') : __('Sale') }}
            </span>
        </div>

        <p class="uh-listing-price uh-listing-row-price uh-numeric">
            {{ $exact }}
            @if($property->price_basis === 'total_sale' && ! $property->isPriceOnRequest())
                <span>{{ __('Total Price') }}</span>
            @endif
        </p>

        <h2 class="uh-listing-title uh-listing-row-title">
            <a class="line-clamp-2" href="{{ $url }}" lang="{{ app()->getLocale() }}"
               data-track="property_card_click" data-track-property-id="{{ $property->id }}">{{ $property->title }}</a>
        </h2>

        @if($place !== '')
            <p class="uh-listing-place uh-listing-row-place">{{ $place }}</p>
        @endif

        <footer class="uh-listing-row-foot">
            <div class="uh-listing-row-meta">
                <span>
                    <x-icon name="calendar" class="size-4" />
                    {{ $property->created_at?->diffForHumans() }}
                </span>
                @if(filled($property->trust_label))
                    <span>
                        <x-icon name="building" class="size-4" />
                        {{ $property->trust_label }}
                    </span>
                @endif
            </div>

            <div class="uh-listing-row-actions">
                <a class="uh-listing-row-details" href="{{ $url }}"
                   aria-label="{{ __('View details') }}" title="{{ __('View details') }}"
                   data-track="property_card_click" data-track-location="list"
                   data-track-property-id="{{ $property->id }}">
                    <x-icon name="arrow-up-right" class="size-4" />
                </a>
                @if($whatsapp && ! $property->isUnavailable())
                    <a class="uh-listing-row-whatsapp" href="{{ $whatsapp }}" rel="noopener" target="_blank"
                       aria-label="{{ __('WhatsApp') }}" title="{{ __('WhatsApp') }}"
                       data-track="whatsapp_click" data-track-location="list"
                       data-track-property-id="{{ $property->id }}">
                        <x-icon name="whatsapp" class="size-5" />
                    </a>
                @endif
            </div>
        </footer>
    </div>
</article>
