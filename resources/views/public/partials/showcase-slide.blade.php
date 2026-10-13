@php
    $listingLayout = $listingLayout ?? false;
    $gridLayout = $gridLayout ?? false;
    $featuredContent = $featuredContent ?? (! $listingLayout && ! $gridLayout);
    $showListingMeta = $showListingMeta ?? $listingLayout;
    $onRequest = $property->isPriceOnRequest();
    $isRent = $property->listing_type === 'rent';
    $compact = $onRequest || $isRent ? null : \App\Support\MoneyFormatter::compactBdt($property->price);
    [$priceAmount, $priceUnit] = match (true) {
        $onRequest => [__('Price on request'), null],
        $compact !== null && str_contains($compact, ' ') => ['BDT '.\Illuminate\Support\Str::beforeLast($compact, ' '), \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::afterLast($compact, ' '))],
        default => [\App\Support\MoneyFormatter::formatBdt($property->price), $property->price_basis === 'monthly_rent' ? __('/month') : null],
    };
    $url = route('properties.show', $property->slug);
    $story = \App\Support\PropertyStory::for($property);
    $isPlot = $story->kind() === \App\Support\PropertyStory::KIND_PLOT;
    $place = collect([$property->locationArea?->name, $property->locationArea?->city])->filter()->implode(', ') ?: __('Dhaka');
    $amenityLabels = collect($property->amenity_ids ?? [])
        ->map(fn (int $amenityId): ?string => ($amenityCatalog ?? collect())->get($amenityId)?->label)
        ->filter()
        ->take(2);
    $phoneHref = \App\Support\PhoneNumber::telHref(\App\Models\Setting::get('phone'));
    $contactEmail = \App\Models\Setting::get('email');
    $whatsapp = $property->isUnavailable() ? null : $property->whatsappEnquiryUrl();
    $preview = \App\Support\PropertyPreview::for($property);
    $photos = $property->relationLoaded('media') ? $property->galleryImages() : collect();
    if ($property->featured_media_id) {
        $photos = $photos->sortBy(fn ($photo) => $photo->id === $property->featured_media_id ? 0 : 1)->values();
    }
    $photos = $photos->take(5)->values();
    $photoTotal = $photos->count();
    $slot = match (true) {
        $index === 0 => 'active',
        $index === 1 => 'next',
        $index === $total - 1 && $total > 2 => 'prev',
        default => 'far-next',
    };
@endphp

<article x-data="{ payload: {{ \Illuminate\Support\Js::from($preview) }} }"
         class="uh-showcase-slide {{ $listingLayout ? 'uh-showcase-list-item' : '' }} {{ $gridLayout ? 'uh-showcase-grid-item' : '' }}"
         @if($listingLayout)
             data-slot="active"
         @else
             data-slot="{{ $slot }}" :data-slot="slot({{ $index }})"
             :aria-hidden="(slot({{ $index }}) !== 'active').toString()"
             @click="if (slot({{ $index }}) !== 'active') { $event.preventDefault(); go({{ $index }}); }"
             @mouseenter="hoverCard({{ $index }}, {{ $photoTotal }})"
             @mouseleave="leaveCard({{ $index }})"
             aria-roledescription="{{ __('slide') }}" aria-label="{{ __(':current of :total', ['current' => $index + 1, 'total' => $total]) }}"
         @endif
         data-spotlight="{{ $property->id }}">
    <div class="uh-showcase-card">
        <div class="uh-showcase-media">
            <a class="uh-showcase-image-link" href="{{ $url }}" @if(! $listingLayout) :tabindex="slot({{ $index }}) === 'active' ? 0 : -1" @endif
               aria-label="{{ $story->exploreLabel() }}: {{ $property->title }}"
               style="view-transition-name: uh-property-{{ $property->id }}">
            @forelse($photos as $photoIndex => $photo)
                <img src="{{ $photo->url(1280) }}"
                     srcset="{{ $photo->url(768) }} 768w, {{ $photo->url(1280) }} 1280w"
                     sizes="(min-width: 1024px) 40rem, 80vw"
                     alt="" @if($index > 1 || $photoIndex > 0) loading="lazy" @endif decoding="async" draggable="false"
                     @if($listingLayout) @class(['is-on' => $photoIndex === 0]) @else :class="{ 'is-on': photoOn({{ $index }}, {{ $photoIndex }}, {{ $photoTotal }}) }" @endif>
            @empty
                <span class="uh-media-placeholder">{{ $property->locationArea?->name ?? __('Urban Haven') }}</span>
            @endforelse
            </a>

            <span @class(['uh-showcase-badge', 'is-rent' => $isRent, 'is-sale' => ! $isRent])>
                {{ $isRent ? __('For Rent') : __('For Sale') }}
            </span>
            @if($listingLayout && $showListingMeta)
                <span class="uh-showcase-ref uh-numeric">#{{ $property->reference ?: $property->id }}</span>
            @endif
            @if($listingLayout && $showListingMeta && $property->is_featured)
                <span class="uh-showcase-highlight-mark" aria-label="{{ __('Featured property') }}">
                    <x-icon name="star" class="size-5" />
                </span>
            @endif
            @if($photoTotal > 1)
                <span class="uh-showcase-dots" aria-hidden="true">
                    @for($dot = 0; $dot < $photoTotal; $dot++)
                        <span @class(['is-on' => $dot === 0]) @if(! $listingLayout) :class="{ 'is-on': photoOn({{ $index }}, {{ $dot }}, {{ $photoTotal }}) }" @endif></span>
                    @endfor
                </span>
            @endif
        </div>

        <div class="uh-showcase-copy">
            <div class="uh-showcase-tools">
            <button type="button" x-data
                    @click.prevent.stop="$store.saved.toggle('shortlist', {{ $property->id }})"
                    :aria-pressed="$store.saved.has('shortlist', {{ $property->id }}).toString()"
                    :aria-label="$store.saved.has('shortlist', {{ $property->id }}) ? @js(__('Remove :title from shortlist', ['title' => $property->title])) : @js(__('Save :title to shortlist', ['title' => $property->title]))"
                    aria-label="{{ __('Save :title to shortlist', ['title' => $property->title]) }}"
                    class="uh-icon-action uh-showcase-favorite"
                    @if($listingLayout) tabindex="0" @else x-bind:tabindex="slot({{ $index }}) === 'active' ? 0 : -1" @endif>
                <x-icon name="heart" class="size-5 uh-saved-action-icon" x-show="!$store.saved.has('shortlist', {{ $property->id }})" />
                <x-icon name="heart-solid" class="size-5 uh-saved-action-icon" x-show="$store.saved.has('shortlist', {{ $property->id }})" x-cloak />
            </button>
                <button type="button" x-data
                        @click.prevent.stop="$store.saved.toggle('compare', {{ $property->id }})"
                        :aria-pressed="$store.saved.has('compare', {{ $property->id }}).toString()"
                        :aria-label="$store.saved.has('compare', {{ $property->id }}) ? @js(__('Remove :title from compare', ['title' => $property->title])) : @js(__('Add :title to compare', ['title' => $property->title]))"
                        aria-label="{{ __('Add :title to compare', ['title' => $property->title]) }}"
                        @class(['uh-icon-action', 'uh-showcase-compare', 'is-featured-content' => $featuredContent])
                        @if($listingLayout) tabindex="0" @else x-bind:tabindex="slot({{ $index }}) === 'active' ? 0 : -1" @endif>
                    <i class="fa-light fa-code-compare uh-saved-action-icon" x-show="!$store.saved.has('compare', {{ $property->id }})"></i>
                    <i class="fa-light fa-code-compare uh-saved-action-icon" x-show="$store.saved.has('compare', {{ $property->id }})" x-cloak></i>
                </button>
            </div>

            <div class="uh-showcase-main">
                <div class="uh-showcase-heading">
                    <h3 class="uh-showcase-title">
                        <a href="{{ $url }}" lang="{{ app()->getLocale() }}" @if(! $listingLayout) :tabindex="slot({{ $index }}) === 'active' ? 0 : -1" @endif class="uh-showcase-title-link"
                           data-track="property_card_click" data-track-property-id="{{ $property->id }}">{{ $property->title }}</a>
                    </h3>
                    <p class="uh-showcase-price uh-numeric">
                        <strong>{{ $priceAmount }}</strong>@if($priceUnit) <span>{{ $priceUnit }}</span>@endif
                    </p>
                </div>
                <div @class(['uh-showcase-info', 'is-featured-content' => $featuredContent])>
                    <div class="uh-showcase-description">
                        <p class="uh-showcase-location">
                            <x-icon name="pin" class="size-5" />
                            <span>{{ $place }}</span>
                        </p>
                        @unless($featuredContent)
                            <div class="uh-showcase-tags">
                                @if($property->propertyType?->label)
                                    <span class="uh-showcase-chip"><x-icon name="building" class="size-5" />{{ $property->propertyType->label }}</span>
                                @endif
                                @foreach($amenityLabels as $amenityLabel)
                                    <span class="uh-showcase-chip"><x-icon name="sparkle" class="size-4" />{{ $amenityLabel }}</span>
                                @endforeach
                            </div>
                        @endunless
                    </div>
                    <div class="uh-showcase-facts uh-numeric">
                        @if($featuredContent && $property->propertyType?->label)
                            <span><x-icon name="building" class="size-4" />{{ $property->propertyType->label }}</span>
                        @endif
                        @if($story->size())
                            <span><x-icon name="area" class="size-4" />{{ $story->size() }}</span>
                        @endif
                        @if(! $isPlot && $property->bedrooms)
                            <span><x-icon name="bed" class="size-4" />{{ $property->bedrooms }} {{ trans_choice(':count bedroom|:count bedrooms', (int) $property->bedrooms, ['count' => $property->bedrooms]) }}</span>
                        @endif
                        @if(! $isPlot && $property->bathrooms)
                            <span><x-icon name="bath" class="size-4" />{{ $property->bathrooms }} {{ trans_choice(':count bathroom|:count bathrooms', (int) $property->bathrooms, ['count' => $property->bathrooms]) }}</span>
                        @endif
                    </div>
                    @if($featuredContent)
                        <div class="uh-showcase-tags">
                            @foreach($amenityLabels as $amenityLabel)
                                <span class="uh-showcase-chip"><x-icon name="sparkle" class="size-4" />{{ $amenityLabel }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="uh-showcase-actions">
                <div class="uh-showcase-contact">
                    @if($whatsapp)
                        <a class="uh-showcase-action-icon text-[#25D366] hover:text-[#25D366]" href="{{ $whatsapp }}" rel="noopener" target="_blank"
                           @if(! $listingLayout) :tabindex="slot({{ $index }}) === 'active' ? 0 : -1" @endif
                           data-track="whatsapp_click" data-track-location="{{ $listingLayout ? 'list' : 'home_featured' }}"
                           data-track-property-id="{{ $property->id }}" aria-label="{{ __('WhatsApp') }}" title="{{ __('WhatsApp') }}">
                            <x-icon name="whatsapp" class="size-5" />
                        </a>
                    @endif
                    @if($phoneHref)
                        <a class="uh-showcase-action-icon text-blue-500 hover:text-blue-600" href="{{ $phoneHref }}"
                           @if(! $listingLayout) :tabindex="slot({{ $index }}) === 'active' ? 0 : -1" @endif
                           data-track="phone_click" data-track-location="{{ $listingLayout ? 'list' : 'home_featured' }}"
                           data-track-property-id="{{ $property->id }}" aria-label="{{ __('Call') }}" title="{{ __('Call') }}">
                            <x-icon name="phone" class="size-5" />
                        </a>
                    @endif
                    @if(filled($contactEmail))
                        <a class="uh-showcase-action-icon text-rose-500 hover:text-rose-600" href="mailto:{{ $contactEmail }}"
                           @if(! $listingLayout) :tabindex="slot({{ $index }}) === 'active' ? 0 : -1" @endif aria-label="{{ __('Email') }}" title="{{ __('Email') }}">
                            <x-icon name="mail" class="size-5" />
                        </a>
                    @endif
                    @if(! $listingLayout)
                        <x-share-menu :url="$url" :title="$property->title" button-class="uh-showcase-action-icon text-slate-500 hover:text-slate-700" icon-class="size-5"
                                      x-bind:tabindex="slot({{ $index }}) === 'active' ? 0 : -1" />
                    @else
                        <x-share-menu :url="$url" :title="$property->title" button-class="uh-showcase-action-icon text-slate-500 hover:text-slate-700" icon-class="size-5" />
                    @endif
                </div>
                <div class="uh-showcase-link-actions">
                    <button type="button" class="uh-showcase-quick-view"
                            @click.stop="$dispatch('open-preview', payload)"
                            @if(! $listingLayout) :tabindex="slot({{ $index }}) === 'active' ? 0 : -1" @endif
                            aria-label="{{ __('Quick view :title', ['title' => $property->title]) }}">
                        <x-icon name="expand" class="size-4" />
                    </button>
                    <a class="uh-showcase-details" href="{{ $url }}"
                       @if(! $listingLayout) :tabindex="slot({{ $index }}) === 'active' ? 0 : -1" @endif
                       aria-label="{{ __('View details') }}">
                        <x-icon name="external" class="size-5" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</article>
