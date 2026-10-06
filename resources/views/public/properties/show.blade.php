@extends('layouts.public')

@php
    $media = $property->galleryImages();
    $floorPlans = $property->floorPlans();
    $brochures = $property->brochures();
    $amenities = $property->amenities();
    $units = $property->units;
    $isPlot = $property->fieldProfile() === \App\Models\PropertyType::PROFILE_PLOT;
    $isRent = $property->listing_type === 'rent';
    $area = $property->area_value ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit) : null;
    $onRequest = $property->isPriceOnRequest();
    $compactPrice = $onRequest ? null : \App\Support\MoneyFormatter::compactBdt($property->price);
    $exactPrice = $onRequest ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headlinePrice = ! $isRent && $compactPrice ? 'BDT '.$compactPrice : $exactPrice;
    $perSqft = ! $isRent && ! $onRequest && filled($property->price) && (float) $property->area_sqft > 0
        ? \App\Support\MoneyFormatter::formatBdt(round((float) $property->price / (float) $property->area_sqft))
        : null;
    $visitTab = $errors->has('preferred_at');
    $videoEmbed = \App\Support\VideoEmbed::embedUrl($property->video_url);
    $shareUrl = route('properties.show', $property->slug);
    $purpose = $isRent ? __('For rent') : __('For sale');
    $availabilityLabel = __(\App\Models\Property::AVAILABILITY_LABELS[$property->availability] ?? ucfirst((string) $property->availability));
    $updatedAt = $property->updated_at?->timezone(config('urbanhaven.display_timezone'));
    $place = collect([$property->locationArea?->name, $property->locationArea?->city])->filter()->implode(', ');
    $story = \App\Support\PropertyStory::for($property);
    $anchors = $story->anchors();
    $officeHours = \App\Models\Setting::get('office_hours');
    $contactEmail = \App\Models\Setting::get('email');
    $contactName = $property->assignedContact?->name ?? __('Urban Haven sales team');
    $contactInitials = \Illuminate\Support\Str::of($contactName)->explode(' ')->filter()->take(2)->map(fn ($word) => mb_substr($word, 0, 1))->implode('');
    $availabilityNote = match (true) {
        $isUnavailable => __('No longer available. We can suggest similar properties.'),
        $property->availability === 'reserved' => __('Reserved by another buyer. You can still enquire in case it does not go ahead.'),
        default => __('Open for enquiries and site visits.'),
    };
    $quickFacts = array_values(array_filter([
        $isPlot || $property->bedrooms === null ? null : ['icon' => 'bed', 'text' => trans_choice(':count bedroom|:count bedrooms', $property->bedrooms, ['count' => $property->bedrooms])],
        $isPlot || $property->bathrooms === null ? null : ['icon' => 'bath', 'text' => trans_choice(':count bathroom|:count bathrooms', $property->bathrooms, ['count' => $property->bathrooms])],
        $area ? ['icon' => 'area', 'text' => $area] : null,
        $property->propertyType ? ['icon' => 'building', 'text' => $property->propertyType->label] : null,
        $property->parking_spaces ? ['icon' => 'car', 'text' => trans_choice(':count parking|:count parking', $property->parking_spaces, ['count' => $property->parking_spaces])] : null,
    ]));
    $overview = array_values(array_filter([
        ['icon' => 'building', 'label' => __('Property type'), 'value' => $property->propertyType?->label],
        ['icon' => 'area', 'label' => __('Size'), 'value' => $area],
        $isPlot ? null : ['icon' => 'bed', 'label' => __('Bedrooms'), 'value' => $property->bedrooms],
        $isPlot ? null : ['icon' => 'bath', 'label' => __('Bathrooms'), 'value' => $property->bathrooms],
        $isPlot ? null : ['icon' => 'home', 'label' => __('Balconies'), 'value' => $property->balconies],
        $isPlot ? null : ['icon' => 'floor', 'label' => __('Floor'), 'value' => $property->floor_number],
        ['icon' => 'car', 'label' => __('Parking'), 'value' => $property->parking_spaces],
        $isPlot ? null : ['icon' => 'sofa', 'label' => __('Furnishing'), 'value' => $property->is_furnished ? __('Furnished') : __('Unfurnished')],
        ['icon' => 'compass', 'label' => __('Facing'), 'value' => $property->facing ? __(config('urbanhaven.facings.'.$property->facing, ucfirst($property->facing))) : null],
        ['icon' => 'road', 'label' => __('Road width'), 'value' => $property->road_width_ft ? $property->road_width_ft.' ft' : null],
        ['icon' => 'tag', 'label' => __('Price'), 'value' => $exactPrice],
        ['icon' => 'check-circle', 'label' => __('Availability'), 'value' => $availabilityLabel],
    ], fn ($fact) => $fact !== null && filled($fact['value'])));
    $hasDescription = filled($property->description) || $anchors;
    $crumbs = [
        ['label' => __('Home'), 'url' => route('home')],
        ['label' => $isRent ? __('Rent') : __('Buy'), 'url' => route('properties.index', ['listing_type' => $property->listing_type])],
        ['label' => $property->locationArea?->name, 'url' => $property->locationArea?->hasLandingPage() ? route('locations.show', $property->locationArea->slug) : ($property->locationArea ? route('properties.index', ['location_area_id' => $property->locationArea->id]) : null)],
        ['label' => $property->title],
    ];
    $thumbs = $media->slice(1, 2)->values();
    $hiddenPhotos = max(0, $media->count() - 1 - $thumbs->count());
    $tabs = array_filter([
        'overview' => __('Overview'),
        'description' => $hasDescription ? __('Description') : null,
        'amenities' => $amenities->isNotEmpty() ? __('Amenities') : null,
        'floor-plans' => $floorPlans->isNotEmpty() ? __('Floor plans') : null,
        'contact' => __('Contact'),
    ]);
    $enquiryIntents = $isUnavailable ? [] : [
        __('Is it still available?') => __('Is this property still available? I would like to know the next step.'),
        __('Price and payment') => __('Could you share the final price, payment schedule and any additional costs?'),
        __('Arrange a call') => __('Please call me to talk about this property.'),
        __('Similar options') => __('I like this property. Could you also suggest similar options?'),
    ];
@endphp

@section('content')
    <article class="uh-pd pb-24 lg:pb-0" x-data="uhGallery({{ $media->count() }})" x-init="$store.saved.viewed({{ $property->id }})">
        <div class="uh-container">
            <x-ui.breadcrumbs :items="$crumbs" />

            @if($isUnavailable)
                <x-ui.alert tone="warn" class="mt-6">
                    <p class="font-medium">{{ __('This property is :status.', ['status' => strtolower($availabilityLabel)]) }}</p>
                    <p class="mt-1">{{ __('It is no longer available, but we can suggest similar homes. See the alternatives below or ask our team.') }}</p>
                </x-ui.alert>
            @elseif($property->availability === 'reserved')
                <x-ui.alert tone="info" class="mt-6">
                    {{ __('This property is currently reserved. You can still enquire in case the reservation does not go ahead.') }}
                </x-ui.alert>
            @endif

            <header id="identity" class="uh-pd-head">
                <div class="min-w-0">
                    <div class="uh-pd-tags">
                        <span class="uh-pd-tag is-dark">{{ $purpose }}</span>
                        @if($property->availability !== 'available')
                            <span class="uh-pd-tag">{{ $availabilityLabel }}</span>
                        @endif
                        @if(filled($property->trust_label))
                            <span class="uh-pd-tag">
                                <x-icon name="shield" class="size-3.5" />
                                {{ $property->trust_label }}
                            </span>
                        @endif
                    </div>
                    <h1 id="property-title" class="uh-pd-title" lang="{{ app()->getLocale() }}">{{ $property->title }}</h1>
                    @if($publicAddress || $place)
                        <p class="uh-pd-address">
                            <x-icon name="pin" class="size-4" />
                            {{ $publicAddress ?: $place }}
                        </p>
                    @endif
                    @if($quickFacts)
                        <ul class="uh-pd-quick" aria-label="{{ __('Key facts') }}">
                            @foreach($quickFacts as $fact)
                                <li>
                                    <x-icon :name="$fact['icon']" class="size-[1.125rem]" />
                                    <span class="uh-numeric">{{ $fact['text'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="uh-pd-head-side">
                    <p class="uh-pd-price uh-numeric">{{ $headlinePrice }}</p>
                    @if($headlinePrice !== $exactPrice)
                        <p class="uh-pd-price-exact uh-numeric">{{ $exactPrice }}</p>
                    @endif
                    @if($updatedAt)
                        <p class="uh-pd-updated">
                            {{ __('Last updated') }} <time datetime="{{ $updatedAt->toDateString() }}">{{ $updatedAt->format('j M Y') }}</time>
                        </p>
                    @endif
                    <div class="uh-pd-actions">
                        <x-save-button :property="$property" variant="button" />
                        <x-save-button :property="$property" list="compare" variant="button" />
                        <button type="button" class="uh-btn-text" x-data="uhShare({{ \Illuminate\Support\Js::from($shareUrl) }}, {{ \Illuminate\Support\Js::from($property->title) }})" @click="share()">
                            <span x-text="copied ? @js(__('Link copied')) : @js(__('Share'))">{{ __('Share') }}</span>
                        </button>
                    </div>
                </div>
            </header>

            <div class="uh-pd-layout">
                <x-media-stage class="uh-pd-stage" :title="$property->title" :photo-count="$media->count()"
                               :video="$videoEmbed" :tour="$property->virtual_tour_url" :coordinates="$coordinates">
                    <section @class(['uh-pd-gallery', 'has-thumbs' => $thumbs->isNotEmpty(), 'has-two' => $thumbs->count() === 2]) aria-label="{{ __('Photographs') }}">
                        @if($media->isNotEmpty())
                            <button type="button" class="uh-pd-shot is-main"
                                    style="view-transition-name: uh-property-{{ $property->id }}"
                                    @click="open(0)"
                                    aria-label="{{ __('Open image :number at full size', ['number' => 1]) }}">
                                <img src="{{ $media->first()->url(1280) }}"
                                     srcset="{{ $media->first()->url(768) }} 768w, {{ $media->first()->url(1280) }} 1280w, {{ $media->first()->url(1920) }} 1920w"
                                     sizes="(min-width: 1100px) 60vw, 100vw"
                                     alt="{{ $media->first()->alt(app()->getLocale()) }}"
                                     fetchpriority="high" decoding="async">
                            </button>
                            @foreach($thumbs as $index => $image)
                                <button type="button" class="uh-pd-shot"
                                        @click="open({{ $index + 1 }})"
                                        aria-label="{{ __('Open image :number at full size', ['number' => $index + 2]) }}">
                                    <img src="{{ $image->url(768) }}" alt="{{ $image->alt(app()->getLocale()) }}" loading="lazy" decoding="async">
                                    @if($loop->last && $hiddenPhotos > 0)
                                        <span class="uh-pd-shot-more">+{{ $hiddenPhotos }}</span>
                                    @endif
                                </button>
                            @endforeach
                            @if($media->count() > 1)
                                <button type="button" class="uh-pd-gallery-all" @click="open(0)">
                                    <x-icon name="grid" class="size-4" />
                                    {{ __('View all :count photos', ['count' => $media->count()]) }}
                                </button>
                            @endif
                        @else
                            <div class="uh-pd-shot is-main is-empty">
                                <x-icon name="image" class="size-8" />
                                <span>{{ __('Photos coming soon') }}</span>
                            </div>
                        @endif
                    </section>
                </x-media-stage>

                <aside class="uh-pd-summary uh-surface" aria-label="{{ __('Price and next steps') }}">
                    <div>
                        <p class="uh-pd-summary-label">{{ $isRent ? __('Monthly rent') : __('Asking price') }}</p>
                        <p class="uh-pd-summary-price uh-numeric">{{ $exactPrice }}</p>
                        @if($perSqft)
                            <p class="uh-pd-summary-note uh-numeric">{{ __(':price per sq ft', ['price' => $perSqft]) }}</p>
                        @endif
                    </div>

                    <dl class="uh-pd-summary-facts">
                        <div>
                            <dt>{{ __('Purpose') }}</dt>
                            <dd>{{ $purpose }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Availability') }}</dt>
                            <dd><x-ui.status :status="$property->availability" /></dd>
                        </div>
                        @if($property->reference)
                            <div>
                                <dt>{{ __('Reference') }}</dt>
                                <dd class="uh-numeric">{{ $property->reference }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="uh-pd-summary-actions">
                        <button type="button" class="uh-btn-primary w-full" @click="$dispatch('uh:open-enquire')">
                            <x-icon name="phone" class="size-4" />
                            {{ $isUnavailable ? __('Ask about similar homes') : __('Get a callback') }}
                        </button>
                        @unless($isUnavailable)
                            <button type="button" class="uh-btn-secondary w-full" @click="$dispatch('uh:open-visit')">
                                <x-icon name="calendar" class="size-4" />
                                {{ __('Book a visit') }}
                            </button>
                        @endunless
                        @if($callHref || filled($whatsapp))
                            <div class="uh-pd-summary-direct">
                                @if($callHref)
                                    <a href="{{ $callHref }}" data-track="phone_click" data-track-property-id="{{ $property->id }}" data-track-location="summary">
                                        <x-icon name="phone" class="size-4" />
                                        {{ __('Call') }}
                                    </a>
                                @endif
                                @if(filled($whatsapp))
                                    <a href="{{ $whatsapp }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-property-id="{{ $property->id }}" data-track-location="summary">
                                        <x-icon name="whatsapp" class="size-4" />
                                        {{ __('WhatsApp') }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </aside>

                <div class="uh-pd-body">
                    <nav class="uh-pd-tabs" x-data="uhSectionTabs" aria-label="{{ __('Property details') }}">
                        <div class="uh-pd-tabs-strip">
                            @foreach($tabs as $anchor => $label)
                                <a href="#{{ $anchor }}" :data-active="current === '{{ $anchor }}'" @if($loop->first) data-active @endif>{{ $label }}</a>
                            @endforeach
                        </div>
                    </nav>

                    <section id="overview" class="uh-pd-card" aria-labelledby="overview-heading">
                        <h2 id="overview-heading" class="uh-pd-card-title">{{ __('Overview') }}</h2>
                        <ul class="uh-pd-overview">
                            @foreach($overview as $fact)
                                <li>
                                    <span class="uh-pd-overview-icon"><x-icon :name="$fact['icon']" class="size-5" /></span>
                                    <span class="uh-pd-overview-label">{{ $fact['label'] }}</span>
                                    <span class="uh-pd-overview-value uh-numeric">{{ $fact['value'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>

                    @if($hasDescription)
                        <section id="description" class="uh-pd-card" aria-labelledby="about-heading">
                            <h2 id="about-heading" class="uh-pd-card-title">{{ __('Description') }}</h2>
                            @if(filled($property->description))
                                <div class="uh-prose uh-pd-prose">{!! nl2br(e($property->description)) !!}</div>
                            @endif

                            @if($anchors)
                                <div class="uh-pd-card-part">
                                    <h3 class="uh-pd-card-sub">{{ $story->lifeTitle() }}</h3>
                                    <ul class="uh-pd-highlights" aria-label="{{ __('What it is like') }}">
                                        @foreach($anchors as $anchor)
                                            <li>
                                                <x-icon name="check-circle" class="size-5" />
                                                <span>
                                                    <strong>{{ $anchor['title'] }}</strong>
                                                    @if($anchor['detail'])
                                                        <span>{{ $anchor['detail'] }}</span>
                                                    @endif
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </section>
                    @endif

                    @if($amenities->isNotEmpty())
                        <section id="amenities" class="uh-pd-card" aria-labelledby="amenities-heading">
                            <h2 id="amenities-heading" class="uh-pd-card-title">{{ __('Amenities') }}</h2>
                            <ul class="uh-pd-amenities">
                                @foreach($amenities as $amenity)
                                    <li>
                                        <x-icon name="check" class="size-4" />
                                        {{ $amenity->label }}
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if($units->isNotEmpty())
                        <section class="uh-pd-card" aria-labelledby="units-heading">
                            <h2 id="units-heading" class="uh-pd-card-title">{{ __('Units') }}</h2>
                            <div class="uh-table-scroll mt-4">
                                <table class="uh-table">
                                    <caption class="sr-only">{{ __('Units in this property') }}</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ __('Unit') }}</th>
                                            <th scope="col">{{ __('Price') }}</th>
                                            <th scope="col">{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($units as $unit)
                                            <tr>
                                                <td class="font-medium">{{ $unit->unit_number }}</td>
                                                <td class="uh-numeric">{{ \App\Support\MoneyFormatter::formatBdt($unit->price, $property->price_basis) }}</td>
                                                <td><x-ui.status :status="$unit->status" /></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @endif

                    @if($floorPlans->isNotEmpty())
                        <section id="floor-plans" class="uh-pd-card" aria-labelledby="plans-heading">
                            <h2 id="plans-heading" class="uh-pd-card-title">{{ __('Floor plans') }}</h2>
                            <div class="uh-plans">
                                @foreach($floorPlans as $plan)
                                    <a href="{{ $plan->url() }}" target="_blank" rel="noopener" class="uh-plan">
                                        <span class="uh-plan-sheet">
                                            <img src="{{ $plan->url(768) }}" alt="{{ $plan->alt(app()->getLocale()) }}" loading="lazy" decoding="async">
                                        </span>
                                        <span class="uh-plan-caption">{{ $plan->alt(app()->getLocale()) ?: __('Floor plan') }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($brochures->isNotEmpty())
                        <section class="uh-pd-card" aria-labelledby="brochure-heading">
                            <h2 id="brochure-heading" class="uh-pd-card-title">{{ __('Brochure') }}</h2>
                            <ul class="uh-pd-files">
                                @foreach($brochures as $brochure)
                                    <li>
                                        <a href="{{ $brochure->url() }}" data-track="brochure_download" data-track-property-id="{{ $property->id }}">
                                            <x-icon name="document" class="size-5" />
                                            <span class="min-w-0 flex-1">{{ $brochure->alt(app()->getLocale()) ?: __('Download brochure (PDF)') }}</span>
                                            @if($brochure->size_bytes)
                                                <span class="uh-numeric text-[var(--uh-faint)]">{{ number_format($brochure->size_bytes / 1048576, 1) }} MB</span>
                                            @endif
                                            <x-icon name="download" class="size-4" />
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    <section id="confidence" class="uh-pd-card" aria-labelledby="confidence-heading">
                        <h2 id="confidence-heading" class="uh-pd-card-title">{{ __('Before you decide') }}</h2>
                        <dl class="uh-assurance">
                            <div>
                                <dt>{{ __('Who is selling') }}</dt>
                                <dd>
                                    {{ __('Listed directly by Urban Haven') }}
                                    <span>{{ __('You deal with the team that manages this property — no third-party agent.') }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt>{{ __('Availability') }}</dt>
                                <dd>
                                    {{ $availabilityLabel }}
                                    <span>{{ $availabilityNote }}</span>
                                </dd>
                            </div>
                            @if($coordinates && $coordinates['approximate'])
                                <div>
                                    <dt>{{ __('Exact address') }}</dt>
                                    <dd>
                                        {{ __('Shared when you book a visit') }}
                                        <span>{{ __('The map shows the neighbourhood until then.') }}</span>
                                    </dd>
                                </div>
                            @endif
                            @if($property->reference)
                                <div>
                                    <dt>{{ __('Reference') }}</dt>
                                    <dd>
                                        <span class="uh-numeric !text-[var(--uh-ink)]">{{ $property->reference }}</span>
                                        <span>{{ __('Mention it when you call and we will know which property you mean.') }}</span>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    <section id="contact" class="uh-pd-card" aria-labelledby="contact-heading">
                        <h2 id="contact-heading" class="uh-pd-card-title">{{ __('Contact') }}</h2>
                        <div class="uh-pd-reach">
                            <div class="uh-pd-agent">
                                <span class="uh-pd-agent-avatar" aria-hidden="true">{{ $contactInitials }}</span>
                                <span class="min-w-0">
                                    <span class="uh-pd-agent-name">{{ $contactName }}</span>
                                    <span class="uh-pd-agent-meta">
                                        <x-icon name="shield" class="size-3.5" />
                                        {{ __('Listed directly by Urban Haven') }}
                                    </span>
                                </span>
                            </div>
                            <ul class="uh-pd-reach-list">
                                @if($callHref)
                                    <li>
                                        <x-icon name="phone" class="size-4" />
                                        <a href="{{ $callHref }}" class="uh-numeric" dir="ltr" data-track="phone_click" data-track-property-id="{{ $property->id }}" data-track-location="contact_card">{{ $callNumber }}</a>
                                    </li>
                                @endif
                                @if(filled($contactEmail))
                                    <li>
                                        <x-icon name="mail" class="size-4" />
                                        <a href="mailto:{{ $contactEmail }}" dir="ltr">{{ $contactEmail }}</a>
                                    </li>
                                @endif
                                @if($officeHours)
                                    <li>
                                        <x-icon name="clock" class="size-4" />
                                        <span>{{ $officeHours }}</span>
                                    </li>
                                @endif
                                @if($property->reference)
                                    <li>
                                        <x-icon name="tag" class="size-4" />
                                        <span>{{ __('Reference') }} <span class="uh-numeric">{{ $property->reference }}</span></span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="uh-pd-reach-actions">
                            <button type="button" class="uh-btn-primary" @click="$dispatch('uh:open-enquire')">
                                <x-icon name="mail" class="size-4" />
                                {{ $isUnavailable ? __('Ask about similar homes') : __('Send an enquiry') }}
                            </button>
                            @unless($isUnavailable)
                                <button type="button" class="uh-btn-secondary" @click="$dispatch('uh:open-visit')">
                                    <x-icon name="calendar" class="size-4" />
                                    {{ __('Book a visit') }}
                                </button>
                            @endunless
                            @if(filled($whatsapp))
                                <a class="uh-btn-whatsapp" href="{{ $whatsapp }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-property-id="{{ $property->id }}" data-track-location="contact_card">
                                    <x-icon name="whatsapp" class="size-4" />
                                    {{ __('WhatsApp') }}
                                </a>
                            @endif
                        </div>
                    </section>
                </div>

                <aside id="contact-panel" class="uh-pd-contact" x-data="{ tab: '{{ $visitTab ? 'visit' : 'enquire' }}' }"
                       @uh:open-visit.window="tab = 'visit'; $el.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' })"
                       @uh:open-enquire.window="tab = 'enquire'; $el.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' }); setTimeout(() => $el.querySelector('#panel-enquire [name=name]')?.focus({ preventScroll: true }), 500)">
                    <div class="uh-convert uh-surface" id="enquire">
                        <div class="uh-pd-agent">
                            <span class="uh-pd-agent-avatar" aria-hidden="true">{{ $contactInitials }}</span>
                            <span class="min-w-0">
                                <span class="uh-pd-agent-name">{{ $contactName }}</span>
                                <span class="uh-pd-agent-meta">
                                    <x-icon name="shield" class="size-3.5" />
                                    {{ __('Listed directly by Urban Haven') }}
                                </span>
                            </span>
                        </div>

                        <h2 class="uh-convert-title">
                            {{ $isUnavailable ? __('Looking for something like this?') : __('Talk about this property') }}
                        </h2>
                        @if($officeHours)
                            <p class="uh-convert-who">{{ __('Available :hours', ['hours' => $officeHours]) }}</p>
                        @endif

                        @if($callHref || filled($whatsapp))
                            <div class="uh-convert-direct">
                                @if($callHref)
                                    <a class="uh-btn-secondary uh-btn-sm" href="{{ $callHref }}" data-track="phone_click" data-track-property-id="{{ $property->id }}" data-track-location="sidebar">
                                        <x-icon name="phone" class="size-4" />
                                        {{ __('Call') }}
                                    </a>
                                @endif
                                @if(filled($whatsapp))
                                    <a class="uh-btn-secondary uh-btn-sm" href="{{ $whatsapp }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-property-id="{{ $property->id }}" data-track-location="sidebar">
                                        <x-icon name="whatsapp" class="size-4" />
                                        {{ __('WhatsApp') }}
                                    </a>
                                @endif
                            </div>
                        @endif

                        <div class="uh-convert-body">
                            @if($isUnavailable)
                                <p class="text-sm text-[var(--uh-muted)]">{{ __('Tell us what you are looking for and we will suggest similar properties.') }}</p>
                                <div class="mt-5" id="panel-enquire">
                                    @include('public.partials.lead-form', [
                                        'propertyId' => $property->id,
                                        'leadType' => 'property_inquiry',
                                        'prefix' => 'similar',
                                        'submitLabel' => __('Ask about similar properties'),
                                        'messagePlaceholder' => __('Budget, preferred area, size…'),
                                    ])
                                </div>
                            @else
                                <div class="uh-seg grid w-full grid-cols-2" role="tablist" aria-label="{{ __('Contact options') }}">
                                    <button type="button" role="tab" id="tab-enquire" aria-controls="panel-enquire" class="uh-seg-btn"
                                            @click="tab = 'enquire'" :aria-selected="(tab === 'enquire').toString()" :class="tab === 'enquire' ? 'is-on' : ''">
                                        {{ __('Enquire') }}
                                    </button>
                                    <button type="button" role="tab" id="tab-visit" aria-controls="panel-visit" class="uh-seg-btn"
                                            @click="tab = 'visit'" :aria-selected="(tab === 'visit').toString()" :class="tab === 'visit' ? 'is-on' : ''">
                                        {{ __('Book a visit') }}
                                    </button>
                                </div>

                                <div class="mt-6" id="panel-enquire" role="tabpanel" aria-labelledby="tab-enquire" x-show="tab === 'enquire'">
                                    @include('public.partials.lead-form', ['propertyId' => $property->id, 'leadType' => 'property_inquiry', 'prefix' => 'enq', 'submitLabel' => __('Ask about this property'), 'intents' => $enquiryIntents])
                                </div>
                                <div class="mt-6" id="panel-visit" role="tabpanel" aria-labelledby="tab-visit" x-show="tab === 'visit'" x-cloak>
                                    @include('public.partials.lead-form', ['propertyId' => $property->id, 'formType' => 'visit', 'prefix' => 'visit', 'addressOnBooking' => $coordinates && $coordinates['approximate']])
                                </div>
                            @endif
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        @if($similar->isNotEmpty())
            <section class="uh-pd-similar" x-data="uhRail" aria-labelledby="similar-title">
                <div class="uh-container uh-section-tight">
                    <header class="uh-rail-head">
                        <h2 id="similar-title" class="uh-h2">{{ $isUnavailable ? __('Available alternatives') : __('Similar properties') }}</h2>
                        <div class="flex gap-1">
                            <button type="button" class="uh-icon-action" @click="prev()" :disabled="!canPrev" aria-label="{{ __('Previous homes') }}">
                                <x-icon name="chevron-left" class="size-5" />
                            </button>
                            <button type="button" class="uh-icon-action" @click="next()" :disabled="!canNext" aria-label="{{ __('Next homes') }}">
                                <x-icon name="chevron-right" class="size-5" />
                            </button>
                        </div>
                    </header>
                    <div x-ref="scroller" class="uh-rail flex snap-x snap-mandatory overflow-x-auto pb-2 [scrollbar-width:none]" tabindex="0" aria-label="{{ __('Similar properties') }}">
                        @foreach($similar as $item)
                            <div class="uh-rail-card shrink-0 snap-start">
                                @include('public.partials.property-card', ['property' => $item])
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <div class="uh-dock lg:hidden">
            @if($callHref)
                <a class="uh-btn-secondary flex-1" href="{{ $callHref }}" data-track="phone_click" data-track-property-id="{{ $property->id }}" data-track-location="mobile_bar">
                    {{ __('Call') }}
                </a>
            @endif
            @if(filled($whatsapp))
                <a class="uh-btn-secondary flex-1" href="{{ $whatsapp }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-property-id="{{ $property->id }}" data-track-location="mobile_bar">
                    {{ __('WhatsApp') }}
                </a>
            @endif
            @if($isUnavailable)
                <a class="uh-btn-primary flex-1" href="#contact">{{ __('Similar homes') }}</a>
            @else
                <button type="button" class="uh-btn-primary flex-1" x-data @click="$dispatch('uh:open-visit')">
                    {{ __('Book a visit') }}
                </button>
            @endif
        </div>

        @if($media->isNotEmpty())
            <div x-show="lightbox" x-cloak x-transition.opacity.duration.400ms class="uh-lightbox"
                 role="dialog" aria-modal="true" aria-label="{{ __('Property gallery') }}"
                 @keydown.escape.window="lightbox && close()" @keydown.left.window="lightbox && previous()" @keydown.right.window="lightbox && next()">
                <div class="uh-lightbox-bar">
                    <p class="uh-numeric">
                        <span x-text="active + 1">1</span>
                        <span class="opacity-50"> / {{ $media->count() }}</span>
                    </p>
                    <p class="hidden truncate px-6 sm:block">{{ $property->title }}</p>
                    <button type="button" x-ref="closeLightbox" class="uh-lightbox-close"
                            @click="close()" aria-label="{{ __('Close gallery') }}">
                        <x-icon name="close" class="size-5" />
                    </button>
                </div>
                <div class="uh-lightbox-stage">
                    @foreach($media as $index => $image)
                        <img x-show="active === {{ $index }}" x-transition.opacity.duration.500ms src="{{ $image->url(1920) }}"
                             alt="{{ $image->alt(app()->getLocale()) }}" loading="lazy" @if($index) x-cloak @endif>
                    @endforeach
                    @if($media->count() > 1)
                        <button type="button" @click="previous()" class="uh-lightbox-nav left-2 sm:left-5" aria-label="{{ __('Previous image') }}">
                            <x-icon name="chevron-left" class="size-5" />
                        </button>
                        <button type="button" @click="next()" class="uh-lightbox-nav right-2 sm:right-5" aria-label="{{ __('Next image') }}">
                            <x-icon name="chevron-right" class="size-5" />
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </article>
@endsection
