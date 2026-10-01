@extends('layouts.public')

@php
    $media = $property->galleryImages();
    $floorPlans = $property->floorPlans();
    $brochures = $property->brochures();
    $amenities = $property->amenities();
    $units = $property->units;
    $isPlot = $property->fieldProfile() === \App\Models\PropertyType::PROFILE_PLOT;
    $area = $property->area_value ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit) : null;
    $onRequest = $property->isPriceOnRequest();
    $compactPrice = $onRequest ? null : \App\Support\MoneyFormatter::compactBdt($property->price);
    $exactPrice = $onRequest ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headlinePrice = $property->listing_type !== 'rent' && $compactPrice ? 'BDT '.$compactPrice : $exactPrice;
    $visitTab = $errors->has('preferred_at');
    $videoEmbed = \App\Support\VideoEmbed::embedUrl($property->video_url);
    $shareUrl = route('properties.show', $property->slug);
    $purpose = $property->listing_type === 'rent' ? __('For rent') : __('For sale');
    $availabilityLabel = __(\App\Models\Property::AVAILABILITY_LABELS[$property->availability] ?? ucfirst((string) $property->availability));
    $updatedAt = $property->updated_at?->timezone(config('urbanhaven.display_timezone'));
    $mapTiles = config('urbanhaven.maps.tile_url');
    $mapAttribution = config('urbanhaven.maps.attribution');
@endphp

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container py-4">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => $property->listing_type === 'rent' ? __('Rent') : __('Buy'), 'url' => route('properties.index', ['listing_type' => $property->listing_type])],
                ['label' => $property->locationArea?->name, 'url' => $property->locationArea?->hasLandingPage() ? route('locations.show', $property->locationArea->slug) : ($property->locationArea ? route('properties.index', ['location_area_id' => $property->locationArea->id]) : null)],
                ['label' => $property->title],
            ]" />
        </div>
    </div>

    <article class="pb-28 lg:pb-0" x-data x-init="$store.saved.viewed({{ $property->id }})">
        <div class="uh-container py-6 lg:py-8">
            @if($isUnavailable)
                <x-ui.alert tone="warn" class="mb-6">
                    <p class="font-semibold">{{ __('This property is :status.', ['status' => strtolower($availabilityLabel)]) }}</p>
                    <p class="mt-1">{{ __('It is no longer available, but we can suggest similar homes. See the alternatives below or ask our team.') }}</p>
                </x-ui.alert>
            @elseif($property->availability === 'reserved')
                <x-ui.alert tone="info" class="mb-6">
                    {{ __('This property is currently reserved. You can still enquire in case the reservation does not go ahead.') }}
                </x-ui.alert>
            @endif

            <header class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.badge tone="gold">{{ $purpose }}</x-ui.badge>
                        <x-ui.status :status="$property->availability" />
                        @if(filled($property->trust_label))
                            <x-ui.badge tone="info">
                                <x-icon name="shield" class="size-3.5" />
                                {{ $property->trust_label }}
                            </x-ui.badge>
                        @endif
                    </div>
                    <h1 class="uh-h1 mt-3">{{ $property->title }}</h1>
                    @if($publicAddress)
                        <p class="mt-2 flex items-center gap-2 text-sm text-[var(--color-muted)]">
                            <x-icon name="pin" class="size-4 shrink-0 text-[var(--color-gold-ink)]" />
                            {{ $publicAddress }}@if($property->locationArea?->city && $publicAddress === $property->locationArea?->name), {{ $property->locationArea->city }}@endif
                        </p>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2 lg:flex-col lg:items-end">
                    <p class="font-display text-3xl tracking-tight text-forest sm:text-4xl">{{ $headlinePrice }}</p>
                    @if($headlinePrice !== $exactPrice)
                        <p class="text-sm text-[var(--color-muted)] uh-numeric">{{ $exactPrice }}</p>
                    @endif
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="uh-btn-outline uh-btn-sm" x-data="uhShare({{ \Illuminate\Support\Js::from($shareUrl) }}, {{ \Illuminate\Support\Js::from($property->title) }})" @click="share()">
                            <x-icon name="share" class="size-4" />
                            <span x-text="copied ? @js(__('Link copied')) : @js(__('Share'))">{{ __('Share') }}</span>
                        </button>
                        <x-save-button :property="$property" variant="button" />
                        <x-save-button :property="$property" list="compare" variant="button" />
                    </div>
                </div>
            </header>

            <ul class="mt-5 flex flex-wrap gap-x-6 gap-y-2 border-t border-line pt-4 text-xs text-[var(--color-muted)]" aria-label="{{ __('Listing facts') }}">
                @if($property->reference)
                    <li class="flex items-center gap-1.5"><x-icon name="tag" class="size-3.5" />{{ __('Reference') }} <strong class="text-ink uh-numeric">{{ $property->reference }}</strong></li>
                @endif
                <li class="flex items-center gap-1.5"><x-icon name="shield" class="size-3.5" />{{ __('Listed directly by Urban Haven') }}</li>
                @if($updatedAt)
                    <li class="flex items-center gap-1.5"><x-icon name="clock" class="size-3.5" />{{ __('Details updated :date', ['date' => $updatedAt->format('j M Y')]) }}</li>
                @endif
            </ul>
        </div>

        @if($media->isNotEmpty())
            <section class="bg-sand/60" x-data="uhGallery({{ $media->count() }})">
                <div class="uh-container py-4 sm:py-6">
                    <div class="grid gap-2 sm:grid-cols-4 sm:grid-rows-2 sm:h-[28rem]">
                        <button type="button"
                                class="uh-media relative overflow-hidden rounded-xl bg-ink sm:col-span-3 sm:row-span-2"
                                @click="open(0)"
                                aria-label="{{ __('Open image :number at full size', ['number' => 1]) }}">
                            <img src="{{ $media->first()->url(1280) }}"
                                 srcset="{{ $media->first()->url(768) }} 768w, {{ $media->first()->url(1280) }} 1280w, {{ $media->first()->url(1920) }} 1920w"
                                 sizes="(min-width: 1280px) 960px, 100vw"
                                 alt="{{ $media->first()->alt(app()->getLocale()) }}"
                                 class="size-full object-cover"
                                 fetchpriority="high" decoding="async">
                        </button>
                        @foreach($media->slice(1, 2)->values() as $index => $image)
                            <button type="button"
                                    class="uh-media relative hidden overflow-hidden rounded-xl bg-ink sm:block"
                                    @click="open({{ $index + 1 }})"
                                    aria-label="{{ __('Open image :number at full size', ['number' => $index + 2]) }}">
                                <img src="{{ $image->url(768) }}" alt="{{ $image->alt(app()->getLocale()) }}"
                                     class="size-full object-cover" loading="lazy" decoding="async">
                                @if($index === 1 && $media->count() > 3)
                                    <span class="absolute inset-0 flex items-center justify-center bg-ink/55 text-sm font-bold text-cream">
                                        + {{ __('More Photos') }}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                        @if($media->count() === 1)
                            <button type="button"
                                    class="hidden items-center justify-center rounded-xl bg-ink text-sm font-bold text-cream sm:flex sm:row-span-2"
                                    @click="open(0)">
                                + {{ __('More Photos') }}
                            </button>
                        @elseif($media->count() === 2)
                            <button type="button"
                                    class="hidden items-center justify-center rounded-xl bg-ink text-sm font-bold text-cream sm:flex"
                                    @click="open(0)">
                                + {{ __('More Photos') }}
                            </button>
                        @endif
                    </div>
                    <button type="button" class="uh-btn-outline uh-btn-sm mt-3 sm:hidden" @click="open(0)">
                        + {{ __('More Photos') }}
                    </button>
                </div>
                <div x-bind:class="lightbox ? 'flex' : 'hidden'" class="fixed inset-0 z-100 items-center justify-center bg-ink/95 p-4"
                     role="dialog" aria-modal="true" aria-label="{{ __('Property gallery') }}"
                     @keydown.escape.window="lightbox && close()" @keydown.left.window="lightbox && previous()" @keydown.right.window="lightbox && next()">
                    <button type="button" x-ref="closeLightbox" class="absolute right-4 top-4 uh-icon-btn text-cream hover:bg-white/10"
                            @click="close()" aria-label="{{ __('Close gallery') }}">
                        <x-icon name="close" class="size-6" />
                    </button>
                    @foreach($media as $index => $image)
                        <img x-show="active === {{ $index }}" src="{{ $image->url(1920) }}"
                             alt="{{ $image->alt(app()->getLocale()) }}" loading="lazy"
                             class="max-h-[85vh] max-w-full rounded-lg object-contain">
                    @endforeach
                    @if($media->count() > 1)
                        <button type="button" @click="previous()" class="absolute left-4 uh-icon-btn text-cream hover:bg-white/10" aria-label="{{ __('Previous image') }}">
                            <x-icon name="chevron-left" class="size-6" />
                        </button>
                        <button type="button" @click="next()" class="absolute right-4 top-1/2 uh-icon-btn text-cream hover:bg-white/10" aria-label="{{ __('Next image') }}">
                            <x-icon name="chevron-right" class="size-6" />
                        </button>
                    @endif
                </div>
            </section>
        @endif

        <div class="sticky top-16 z-20 border-b border-line bg-paper lg:top-[4.25rem]">
            <div class="uh-container">
                <nav class="-mx-1 flex gap-1 overflow-x-auto" aria-label="{{ __('Property details') }}">
                    <a href="#overview" class="uh-tab shrink-0">{{ __('Overview') }}</a>
                    @if(filled($property->description))
                        <a href="#description" class="uh-tab shrink-0">{{ __('Description') }}</a>
                    @endif
                    @if($amenities->isNotEmpty())
                        <a href="#amenities" class="uh-tab shrink-0">{{ __('Amenities') }}</a>
                    @endif
                    @if($floorPlans->isNotEmpty())
                        <a href="#floor-plans" class="uh-tab shrink-0">{{ __('Floor plans') }}</a>
                    @endif
                    @if($coordinates)
                        <a href="#location" class="uh-tab shrink-0">{{ __('Location') }}</a>
                    @endif
                    <a href="#contact" class="uh-tab shrink-0">{{ __('Contact') }}</a>
                </nav>
            </div>
        </div>

        <div class="uh-container grid gap-10 py-8 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-start lg:gap-12 lg:py-10">
            <div class="min-w-0 space-y-10">
                <section id="overview" class="scroll-mt-32" aria-labelledby="overview-heading">
                    <h2 id="overview-heading" class="uh-h2">{{ __('Overview') }}</h2>
                    <div class="uh-specs mt-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4">
                        <x-ui.spec :label="__('Property type')" icon="building">{{ $property->propertyType?->label ?? '—' }}</x-ui.spec>
                        <x-ui.spec :label="__('Size')" icon="area">{{ $area ?: '—' }}</x-ui.spec>
                        <x-ui.spec :label="__('Price')" icon="tag">{{ $exactPrice }}</x-ui.spec>
                        <x-ui.spec :label="__('Availability')" icon="check-circle">{{ $availabilityLabel }}</x-ui.spec>
                        @unless($isPlot)
                            @if($property->bedrooms !== null)
                                <x-ui.spec :label="__('Bedrooms')" icon="bed">{{ $property->bedrooms }}</x-ui.spec>
                            @endif
                            @if($property->bathrooms !== null)
                                <x-ui.spec :label="__('Bathrooms')" icon="bath">{{ $property->bathrooms }}</x-ui.spec>
                            @endif
                            @if($property->balconies !== null)
                                <x-ui.spec :label="__('Balconies')" icon="home">{{ $property->balconies }}</x-ui.spec>
                            @endif
                            @if($property->floor_number !== null)
                                <x-ui.spec :label="__('Floor')" icon="floor">{{ $property->floor_number }}</x-ui.spec>
                            @endif
                            <x-ui.spec :label="__('Furnishing')" icon="home">{{ $property->is_furnished ? __('Furnished') : __('Unfurnished') }}</x-ui.spec>
                        @endunless
                        @if($property->parking_spaces !== null)
                            <x-ui.spec :label="__('Parking')" icon="key">{{ $property->parking_spaces }}</x-ui.spec>
                        @endif
                        @if($property->facing)
                            <x-ui.spec :label="__('Facing')" icon="compass">{{ __(config('urbanhaven.facings.'.$property->facing, ucfirst($property->facing))) }}</x-ui.spec>
                        @endif
                        @if($property->road_width_ft)
                            <x-ui.spec :label="__('Road width')" icon="map">{{ $property->road_width_ft }} ft</x-ui.spec>
                        @endif
                    </div>
                </section>

                @if(filled($property->description))
                    <section id="description" class="scroll-mt-32" aria-labelledby="about-heading">
                        <h2 id="about-heading" class="uh-h2">{{ __('Description') }}</h2>
                        <div class="uh-panel mt-4">
                            <div class="uh-prose">{!! nl2br(e($property->description)) !!}</div>
                        </div>
                    </section>
                @endif

                @if($amenities->isNotEmpty())
                    <section id="amenities" class="scroll-mt-32" aria-labelledby="amenities-heading">
                        <h2 id="amenities-heading" class="uh-h2">{{ __('Amenities') }}</h2>
                        <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach($amenities as $amenity)
                                <li class="flex items-center gap-3 rounded-xl bg-sand px-3 py-3 text-sm font-medium">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-paper text-forest ring-1 ring-line">
                                        <x-icon name="check" class="size-4" />
                                    </span>
                                    {{ $amenity->label }}
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if($units->isNotEmpty())
                    <section aria-labelledby="units-heading">
                        <h2 id="units-heading" class="uh-h2">{{ __('Units') }}</h2>
                        <div class="uh-panel-flush mt-4 overflow-hidden">
                            <div class="uh-table-scroll">
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
                        </div>
                    </section>
                @endif

                @if($floorPlans->isNotEmpty())
                    <section id="floor-plans" class="scroll-mt-32" aria-labelledby="plans-heading">
                        <h2 id="plans-heading" class="uh-h2">{{ __('Floor plans') }}</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach($floorPlans as $plan)
                                <a href="{{ $plan->url() }}" target="_blank" rel="noopener" class="uh-card block overflow-hidden">
                                    <img src="{{ $plan->url(768) }}" alt="{{ $plan->alt(app()->getLocale()) }}" loading="lazy" decoding="async"
                                         class="aspect-4/3 w-full bg-white object-contain p-2">
                                    <span class="block border-t border-line px-4 py-2 text-xs font-semibold">{{ $plan->alt(app()->getLocale()) ?: __('Floor plan') }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($brochures->isNotEmpty())
                    <section aria-labelledby="brochure-heading">
                        <h2 id="brochure-heading" class="uh-h2">{{ __('Brochure') }}</h2>
                        <ul class="mt-4 space-y-2">
                            @foreach($brochures as $brochure)
                                <li>
                                    <a class="uh-btn-outline uh-btn-sm" href="{{ $brochure->url() }}" data-track="brochure_download" data-track-property-id="{{ $property->id }}">
                                        <x-icon name="download" class="size-4" />
                                        {{ $brochure->alt(app()->getLocale()) ?: __('Download brochure (PDF)') }}
                                        @if($brochure->size_bytes)
                                            <span class="text-[var(--color-muted)]">{{ number_format($brochure->size_bytes / 1048576, 1) }} MB</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if($videoEmbed)
                    <section aria-labelledby="video-heading">
                        <h2 id="video-heading" class="uh-h2">{{ __('Video') }}</h2>
                        <div class="mt-4 aspect-video overflow-hidden rounded-xl bg-black">
                            <iframe src="{{ $videoEmbed }}" title="{{ __('Video of :title', ['title' => $property->title]) }}"
                                    class="size-full" loading="lazy"
                                    allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                    referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                    </section>
                @endif

                @if($property->listing_type !== 'rent' && ! $onRequest && filled($property->price) && ! $isUnavailable)
                    <div id="emi" class="scroll-mt-32">
                        @include('public.partials.emi-calculator', ['price' => $property->price, 'headingId' => 'emi-heading'])
                    </div>
                @endif

                @if($coordinates)
                    <section id="location" class="scroll-mt-32" aria-labelledby="map-heading">
                        <h2 id="map-heading" class="uh-h2">{{ __('Location') }}</h2>
                        <div class="uh-panel-flush mt-4 overflow-hidden">
                            <div class="h-72 w-full sm:h-96" role="region" aria-label="{{ __('Map of :title', ['title' => $property->title]) }}"
                                 data-uh-map data-lat="{{ $coordinates['lat'] }}" data-lng="{{ $coordinates['lng'] }}"
                                 data-zoom="{{ $coordinates['approximate'] ? 14 : 16 }}"
                                 data-pin="{{ $coordinates['approximate'] ? 'false' : 'true' }}"
                                 data-approximate="{{ $coordinates['approximate'] ? 'true' : 'false' }}"
                                 data-tiles="{{ $mapTiles }}" data-attribution="{{ $mapAttribution }}"></div>
                            <p class="border-t border-line px-4 py-3 text-xs text-[var(--color-muted)]">
                                @if($coordinates['approximate'])
                                    {{ __('The map shows the approximate neighbourhood. The exact address is shared when you book a visit.') }}
                                @else
                                    {{ __('The map shows the location of this property.') }}
                                @endif
                            </p>
                        </div>
                    </section>
                @endif

                @if($property->project)
                    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-sand px-5 py-5">
                        <div>
                            <p class="uh-eyebrow">{{ __('Part of a project') }}</p>
                            <p class="uh-h4 mt-1.5">{{ $property->project->name }}</p>
                        </div>
                        <a class="uh-btn-outline uh-btn-sm" href="{{ route('projects.show', $property->project->slug) }}">
                            {{ __('View the project') }}
                            <x-icon name="arrow-right" class="size-4" />
                        </a>
                    </div>
                @endif
            </div>

            <aside id="contact" class="scroll-mt-32 lg:sticky lg:top-[8rem]" x-data="{ tab: '{{ $visitTab ? 'visit' : 'enquire' }}' }"
                   @uh:open-visit.window="tab = 'visit'; $el.scrollIntoView({ behavior: 'smooth' })">
                <div class="uh-panel" id="enquire">
                    <div class="flex items-center gap-3">
                        <span class="flex size-12 items-center justify-center rounded-full bg-forest text-sm font-bold text-cream">UH</span>
                        <div>
                            <p class="font-semibold">{{ $property->assignedContact?->name ?? __('Urban Haven sales team') }}</p>
                            <p class="text-xs text-[var(--color-muted)]">{{ \App\Models\Setting::get('company_name', 'Urban Haven') }}</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-2">
                        @if($callHref)
                            <a class="uh-btn-primary uh-btn-sm" href="{{ $callHref }}" data-track="phone_click" data-track-property-id="{{ $property->id }}" data-track-location="sidebar">
                                <x-icon name="phone" class="size-4" />
                                {{ __('Call') }}
                            </a>
                        @endif
                        @if(filled($whatsapp))
                            <a class="uh-btn-whatsapp uh-btn-sm" href="{{ $whatsapp }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-property-id="{{ $property->id }}" data-track-location="sidebar">
                                <x-icon name="whatsapp" class="size-4" />
                                {{ __('WhatsApp') }}
                            </a>
                        @endif
                    </div>

                    @if($isUnavailable)
                        <p class="mt-5 text-sm text-[var(--color-muted)]">{{ __('Tell us what you are looking for and we will suggest similar properties.') }}</p>
                        <div class="mt-4">
                            @include('public.partials.lead-form', [
                                'propertyId' => $property->id,
                                'leadType' => 'property_inquiry',
                                'prefix' => 'similar',
                                'submitLabel' => __('Ask about similar properties'),
                                'messagePlaceholder' => __('Budget, preferred area, size…'),
                            ])
                        </div>
                    @else
                        <div class="mt-5 grid grid-cols-2 gap-1 rounded-lg bg-sand p-1" role="tablist" aria-label="{{ __('Contact options') }}">
                            <button type="button" role="tab" id="tab-enquire" aria-controls="panel-enquire" @click="tab = 'enquire'" :aria-selected="(tab === 'enquire').toString()"
                                    class="min-h-10 rounded-md px-3 text-[0.8125rem] font-semibold transition"
                                    :class="tab === 'enquire' ? 'bg-paper text-ink shadow-sm' : 'text-[var(--color-muted)] hover:text-ink'">
                                {{ __('Enquire') }}
                            </button>
                            <button type="button" role="tab" id="tab-visit" aria-controls="panel-visit" @click="tab = 'visit'" :aria-selected="(tab === 'visit').toString()"
                                    class="min-h-10 rounded-md px-3 text-[0.8125rem] font-semibold transition"
                                    :class="tab === 'visit' ? 'bg-paper text-ink shadow-sm' : 'text-[var(--color-muted)] hover:text-ink'">
                                {{ __('Book a visit') }}
                            </button>
                        </div>

                        <div class="mt-5" id="panel-enquire" role="tabpanel" aria-labelledby="tab-enquire" x-show="tab === 'enquire'">
                            @include('public.partials.lead-form', ['propertyId' => $property->id, 'leadType' => 'property_inquiry', 'prefix' => 'enq'])
                        </div>
                        <div class="mt-5" id="panel-visit" role="tabpanel" aria-labelledby="tab-visit" x-show="tab === 'visit'" x-cloak>
                            @include('public.partials.lead-form', ['propertyId' => $property->id, 'formType' => 'visit', 'prefix' => 'visit'])
                        </div>
                    @endif
                </div>
            </aside>
        </div>

        @if($similar->isNotEmpty())
            <section class="border-t border-line bg-paper">
                <div class="uh-container uh-section-tight" x-data="{}">
                    <div class="flex items-end justify-between gap-4">
                        <h2 class="uh-h2">{{ $isUnavailable ? __('Available alternatives') : __('Similar properties') }}</h2>
                        <div class="flex gap-2">
                            <button type="button" class="uh-icon-btn size-11 border border-line-strong bg-paper"
                                    @click="$refs.similar.scrollBy({ left: -320, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' })"
                                    aria-label="{{ __('Previous homes') }}">
                                <x-icon name="chevron-left" class="size-5" />
                            </button>
                            <button type="button" class="uh-icon-btn size-11 border border-line-strong bg-paper"
                                    @click="$refs.similar.scrollBy({ left: 320, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' })"
                                    aria-label="{{ __('Next homes') }}">
                                <x-icon name="chevron-right" class="size-5" />
                            </button>
                        </div>
                    </div>
                    <div x-ref="similar" class="mt-6 flex snap-x snap-mandatory gap-4 overflow-x-auto pb-2" tabindex="0">
                        @foreach($similar as $item)
                            <div class="w-[min(100%,20rem)] shrink-0 snap-start">
                                @include('public.partials.property-card', ['property' => $item])
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <div class="fixed inset-x-0 bottom-0 z-40 flex gap-2 border-t border-white/10 bg-ink/95 p-3 backdrop-blur-sm lg:hidden">
            @if($callHref)
                <a class="uh-btn-ondark flex-1" href="{{ $callHref }}" data-track="phone_click" data-track-property-id="{{ $property->id }}" data-track-location="mobile_bar">
                    <x-icon name="phone" class="size-4" />
                    {{ __('Call') }}
                </a>
            @endif
            @if(filled($whatsapp))
                <a class="uh-btn-whatsapp flex-1" href="{{ $whatsapp }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-property-id="{{ $property->id }}" data-track-location="mobile_bar">
                    <x-icon name="whatsapp" class="size-4" />
                    {{ __('WhatsApp') }}
                </a>
            @endif
            @if($isUnavailable)
                <a class="uh-btn-ondark flex-1" href="#contact">{{ __('Similar homes') }}</a>
            @else
                <button type="button" class="uh-btn-ondark flex-1" x-data @click="$dispatch('uh:open-visit')">
                    <x-icon name="calendar" class="size-4" />
                    {{ __('Visit') }}
                </button>
            @endif
        </div>
    </article>
@endsection
