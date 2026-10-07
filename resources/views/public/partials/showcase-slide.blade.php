@php
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
        ->map(fn (int $amenityId): ?string => $amenityCatalog->get($amenityId)?->label)
        ->filter()
        ->take(2);
    $phoneHref = \App\Support\PhoneNumber::telHref(\App\Models\Setting::get('phone'));
    $contactEmail = \App\Models\Setting::get('email');
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
         class="uh-showcase-slide" data-slot="{{ $slot }}" :data-slot="slot({{ $index }})"
         :aria-hidden="(slot({{ $index }}) !== 'active').toString()"
         @click="if (slot({{ $index }}) !== 'active') { $event.preventDefault(); go({{ $index }}); }"
         @mouseenter="hoverCard({{ $index }}, {{ $photoTotal }})"
         @mouseleave="leaveCard({{ $index }})"
         aria-roledescription="{{ __('slide') }}" aria-label="{{ __(':current of :total', ['current' => $index + 1, 'total' => $total]) }}"
         data-spotlight="{{ $property->id }}">
    <div class="uh-showcase-card">
        <div class="uh-showcase-media">
            <a class="uh-showcase-image-link" href="{{ $url }}" :tabindex="slot({{ $index }}) === 'active' ? 0 : -1"
               aria-label="{{ $story->exploreLabel() }}: {{ $property->title }}"
               style="view-transition-name: uh-property-{{ $property->id }}">
            @forelse($photos as $photoIndex => $photo)
                <img src="{{ $photo->url(1280) }}"
                     srcset="{{ $photo->url(768) }} 768w, {{ $photo->url(1280) }} 1280w"
                     sizes="(min-width: 1024px) 40rem, 80vw"
                     alt="" @if($index > 1 || $photoIndex > 0) loading="lazy" @endif decoding="async" draggable="false"
                     @class(['is-on' => $photoIndex === 0])
                     @if($photoTotal > 1) :class="{ 'is-on': photoOn({{ $index }}, {{ $photoIndex }}, {{ $photoTotal }}) }" @endif>
            @empty
                <span class="uh-media-placeholder">{{ $property->locationArea?->name ?? __('Urban Haven') }}</span>
            @endforelse
            </a>

            <span class="uh-showcase-badge">{{ $isRent ? __('For Rent') : __('For Sale') }}</span>
            <span class="uh-showcase-ref uh-numeric">#{{ $property->reference ?: $property->id }}</span>

            @if($photoTotal > 1)
                <span class="uh-showcase-dots" aria-hidden="true">
                    @for($dot = 0; $dot < $photoTotal; $dot++)
                        <span @class(['is-on' => $dot === 0]) :class="{ 'is-on': photoOn({{ $index }}, {{ $dot }}, {{ $photoTotal }}) }"></span>
                    @endfor
                </span>
            @endif
        </div>

        <div class="uh-showcase-copy">
            <x-save-button :property="$property" class="uh-showcase-favorite"
                           x-bind:tabindex="slot({{ $index }}) === 'active' ? 0 : -1" />

            <div class="uh-showcase-main">
                <div class="uh-showcase-heading">
                    <h3 class="uh-showcase-title">
                        <a href="{{ $url }}" lang="{{ app()->getLocale() }}" :tabindex="slot({{ $index }}) === 'active' ? 0 : -1"
                           data-track="property_card_click" data-track-property-id="{{ $property->id }}">{{ $property->title }}</a>
                    </h3>
                    <p class="uh-showcase-price uh-numeric">
                        <strong>{{ $priceAmount }}</strong>@if($priceUnit) <span>{{ $priceUnit }}</span>@endif
                    </p>
                </div>
                <div class="uh-showcase-info">
                    <div class="uh-showcase-description">
                        <p class="uh-showcase-location">
                            <x-icon name="pin" class="size-5" />
                            <span>{{ $place }}</span>
                        </p>
                        <div class="uh-showcase-tags">
                            @if($property->propertyType?->label)
                                <span class="uh-showcase-chip"><x-icon name="building" class="size-5" />{{ $property->propertyType->label }}</span>
                            @endif
                            @foreach($amenityLabels as $amenityLabel)
                                <span class="uh-showcase-chip"><x-icon name="sparkle" class="size-4" />{{ $amenityLabel }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="uh-showcase-facts uh-numeric">
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
                </div>
            </div>

            <div class="uh-showcase-actions">
                <div class="uh-showcase-contact">
                    @if($phoneHref)
                        <a class="uh-showcase-action-icon" href="{{ $phoneHref }}"
                           :tabindex="slot({{ $index }}) === 'active' ? 0 : -1"
                           data-track="phone_click" data-track-location="home_featured"
                           data-track-property-id="{{ $property->id }}" aria-label="{{ __('Call') }}">
                            <x-icon name="phone" class="size-5" />
                        </a>
                    @endif
                    @if(filled($contactEmail))
                        <a class="uh-showcase-action-icon" href="mailto:{{ $contactEmail }}"
                           :tabindex="slot({{ $index }}) === 'active' ? 0 : -1" aria-label="{{ __('Email') }}">
                            <x-icon name="mail" class="size-5" />
                        </a>
                    @endif
                    <button type="button" class="uh-showcase-action-icon"
                            x-data="uhShare({{ \Illuminate\Support\Js::from($url) }}, {{ \Illuminate\Support\Js::from($property->title) }})"
                            @click.stop="share()" :tabindex="slot({{ $index }}) === 'active' ? 0 : -1"
                            aria-label="{{ __('Share') }}">
                        <x-icon name="share" class="size-5" />
                    </button>
                </div>
                <div class="uh-showcase-link-actions">
                    <button type="button" class="uh-showcase-quick-view"
                            @click.stop="$dispatch('open-preview', payload)"
                            :tabindex="slot({{ $index }}) === 'active' ? 0 : -1"
                            aria-label="{{ __('Quick view :title', ['title' => $property->title]) }}">
                        <x-icon name="expand" class="size-4" />
                    </button>
                    <a class="uh-showcase-details" href="{{ $url }}"
                       :tabindex="slot({{ $index }}) === 'active' ? 0 : -1"
                       aria-label="{{ __('View details') }}">
                        <x-icon name="external" class="size-5" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</article>
