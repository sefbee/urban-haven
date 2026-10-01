@extends('layouts.public')

@php
    $media = $property->media;
    $amenities = $property->amenities();
    $units = $property->units;
    $area = $property->area_value ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit) : null;
    $compactPrice = \App\Support\MoneyFormatter::compactBdt($property->price);
    $exactPrice = \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headlinePrice = $property->listing_type !== 'rent' && $compactPrice ? 'BDT '.$compactPrice : $exactPrice;
    $decimals = (int) config('urbanhaven.maps.approximate_decimals', 2);
    $hasMap = filled($property->lat) && filled($property->lng);
    $mapLat = $hasMap ? round((float) $property->lat, $property->map_approximation ? $decimals : 6) : null;
    $mapLng = $hasMap ? round((float) $property->lng, $property->map_approximation ? $decimals : 6) : null;
    $visitTab = $errors->hasAny(['preferred_at', 'notes']);
    $videoEmbed = \App\Support\VideoEmbed::embedUrl($property->video_url);
    $contactPhone = \App\Models\Setting::get('phone');
    $contactEmail = \App\Models\Setting::get('email');
    $saved = in_array($property->id, array_map('intval', session('shortlist', [])), true);
    $shareUrl = route('properties.show', $property->slug);
    $whatsapp = $property->whatsappEnquiryUrl();
    $purpose = $property->listing_type === 'rent' ? __('For rent') : __('For sale');
    $completion = match ($property->availability) {
        'available' => __('Ready'),
        'reserved' => __('Reserved'),
        default => \Illuminate\Support\Str::headline((string) $property->availability),
    };
@endphp

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container py-4">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => $property->listing_type === 'rent' ? __('Rent') : __('Buy'), 'url' => route('properties.index', ['listing_type' => $property->listing_type])],
                ['label' => $property->locationArea?->name, 'url' => $property->locationArea ? route('properties.index', ['location_area_id' => $property->locationArea->id]) : null],
                ['label' => $property->title],
            ]" />
        </div>
    </div>

    <article class="pb-28 lg:pb-0">
        <div class="uh-container py-6 lg:py-8">
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
                    <p class="mt-2 flex items-center gap-2 text-sm text-[var(--color-muted)]">
                        <x-icon name="pin" class="size-4 shrink-0 text-[var(--color-gold-ink)]" />
                        {{ $property->locationArea?->name }}@if($property->locationArea?->city), {{ $property->locationArea->city }}@endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 lg:flex-col lg:items-end">
                    <p class="font-display text-3xl tracking-tight text-forest sm:text-4xl">{{ $headlinePrice }}</p>
                    @if($headlinePrice !== $exactPrice)
                        <p class="text-sm text-[var(--color-muted)] uh-numeric">{{ $exactPrice }}</p>
                    @endif
                    <div class="flex gap-2">
                        <button type="button" class="uh-btn-outline uh-btn-sm" x-data="uhShare({{ \Illuminate\Support\Js::from($shareUrl) }}, {{ \Illuminate\Support\Js::from($property->title) }})" @click="share()">
                            <x-icon name="share" class="size-4" />
                            <span x-text="copied ? '{{ __('Link copied') }}' : '{{ __('Share') }}'">{{ __('Share') }}</span>
                        </button>
                        @if($saved)
                            <form method="POST" action="{{ route('shortlist.remove', $property->id) }}" x-data="uhForm" @submit="submit">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="uh-btn-outline uh-btn-sm" :disabled="submitting" aria-label="{{ __('Saved') }}">
                                    <x-icon name="heart-solid" class="size-4 text-[var(--color-danger)]" x-show="!submitting" />
                                    <span class="uh-spinner" x-show="submitting" x-cloak></span>
                                    {{ __('Favorite') }}
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('shortlist.add') }}" x-data="uhForm" @submit="submit">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $property->id }}">
                                <button type="submit" class="uh-btn-outline uh-btn-sm" :disabled="submitting">
                                    <x-icon name="heart" class="size-4" x-show="!submitting" />
                                    <span class="uh-spinner" x-show="submitting" x-cloak></span>
                                    {{ __('Favorite') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </header>
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

        <div class="sticky top-[5.75rem] z-20 border-b border-line bg-paper lg:top-[6.25rem]">
            <div class="uh-container">
                <nav class="-mx-1 flex gap-1 overflow-x-auto" aria-label="{{ __('Property details') }}">
                    <a href="#overview" class="uh-tab shrink-0">{{ __('Overview') }}</a>
                    @if(filled($property->description))
                        <a href="#description" class="uh-tab shrink-0">{{ __('Description') }}</a>
                    @endif
                    @if($amenities->isNotEmpty())
                        <a href="#amenities" class="uh-tab shrink-0">{{ __('Amenities') }}</a>
                    @endif
                    @if($property->listing_type !== 'rent' && filled($property->price))
                        <a href="#emi" class="uh-tab shrink-0">{{ __('EMI Calculator') }}</a>
                    @endif
                    @if($hasMap)
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
                    <div class="uh-specs mt-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-5">
                        <x-ui.spec :label="__('Property Type')" icon="building">{{ $property->propertyType?->label ?? '—' }}</x-ui.spec>
                        <x-ui.spec :label="__('Size')" icon="area">{{ $area ?: '—' }}</x-ui.spec>
                        <x-ui.spec :label="__('Price')" icon="tag">{{ $headlinePrice }}</x-ui.spec>
                        <x-ui.spec :label="__('Purpose')" icon="key">{{ $purpose }}</x-ui.spec>
                        <x-ui.spec :label="__('Completion')" icon="check-circle">{{ $completion }}</x-ui.spec>
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
                        <h2 id="units-heading" class="uh-h2">{{ __('Available units') }}</h2>
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

                @if($videoEmbed)
                    <section aria-labelledby="video-heading">
                        <h2 id="video-heading" class="uh-h2">{{ __('Video') }}</h2>
                        <div class="mt-4 aspect-video overflow-hidden rounded-xl bg-black">
                            <iframe src="{{ $videoEmbed }}" title="{{ __('Video of :title', ['title' => $property->title]) }}"
                                    class="size-full" loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                    referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                    </section>
                @endif

                @if($property->listing_type !== 'rent' && filled($property->price))
                    <div id="emi" class="scroll-mt-32">
                        @include('public.partials.emi-calculator', ['price' => $property->price, 'headingId' => 'emi-heading'])
                    </div>
                @endif

                @if($hasMap)
                    <section id="location" class="scroll-mt-32" aria-labelledby="map-heading">
                        <h2 id="map-heading" class="uh-h2">{{ __('Location') }}</h2>
                        <div class="uh-panel-flush mt-4 overflow-hidden">
                            <iframe class="h-72 w-full border-0 sm:h-96"
                                    title="{{ __('Map of :title', ['title' => $property->title]) }}"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://maps.google.com/maps?q={{ rawurlencode($mapLat.','.$mapLng) }}&z={{ $property->map_approximation ? 14 : 16 }}&output=embed"></iframe>
                            <p class="border-t border-line px-4 py-3 text-xs text-[var(--color-muted)]">
                                @if($property->map_approximation)
                                    {{ __('This map shows the approximate neighbourhood. The exact address is shared when you book a viewing.') }}
                                @else
                                    {{ __('Map shows the location of this home.') }}
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

            <aside id="contact" class="scroll-mt-32 lg:sticky lg:top-[10.5rem]" x-data="{ tab: '{{ $visitTab ? 'visit' : 'enquire' }}' }">
                <div class="uh-panel" id="enquire">
                    <div class="flex items-center gap-3">
                        <span class="flex size-12 items-center justify-center rounded-full bg-forest text-sm font-bold text-cream">UH</span>
                        <div>
                            <p class="font-semibold">{{ __('Contact Urban Haven Agent') }}</p>
                            <p class="text-xs text-[var(--color-muted)]">Urban Haven Properties Ltd. · {{ __('Dhaka') }}</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-2">
                        @if(filled($whatsapp))
                            <a class="uh-btn-whatsapp uh-btn-sm" href="{{ $whatsapp }}" rel="noopener">
                                <x-icon name="whatsapp" class="size-4" />
                                {{ __('WhatsApp Us') }}
                            </a>
                        @endif
                        @if(filled($contactPhone))
                            <a class="uh-btn-primary uh-btn-sm" href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}">
                                <x-icon name="phone" class="size-4" />
                                {{ __('Call Now') }}
                            </a>
                        @endif
                    </div>

                    <button type="button" class="uh-btn-primary uh-btn-block mt-5" @click="tab = 'visit'">
                        <x-icon name="calendar" class="size-4" />
                        {{ __('Schedule a Viewing') }}
                    </button>

                    <div class="mt-5 grid grid-cols-2 gap-1 rounded-lg bg-sand p-1" role="tablist" aria-label="{{ __('Contact options') }}">
                        <button type="button" role="tab" @click="tab = 'enquire'" :aria-selected="(tab === 'enquire').toString()"
                                class="min-h-9 rounded-md px-3 text-[0.8125rem] font-semibold transition"
                                :class="tab === 'enquire' ? 'bg-paper text-ink shadow-sm' : 'text-[var(--color-muted)] hover:text-ink'">
                            {{ __('Inquire Now') }}
                        </button>
                        <button type="button" role="tab" @click="tab = 'visit'" :aria-selected="(tab === 'visit').toString()"
                                class="min-h-9 rounded-md px-3 text-[0.8125rem] font-semibold transition"
                                :class="tab === 'visit' ? 'bg-paper text-ink shadow-sm' : 'text-[var(--color-muted)] hover:text-ink'">
                            {{ __('Schedule a Viewing') }}
                        </button>
                    </div>

                    <form method="POST" action="{{ route('inquiries.store') }}" class="mt-5 space-y-4"
                          x-show="tab === 'enquire'" x-data="uhForm" @submit="submit">
                        @csrf
                        <input type="hidden" name="property_id" value="{{ $property->id }}">
                        <x-ui.input name="name" id="enq-name" :label="__('Your name')" autocomplete="name" required />
                        <x-ui.input name="phone" id="enq-phone" :label="__('Mobile number')" type="tel" dir="ltr"
                                    inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX"
                                    :hint="__('A Bangladeshi mobile number, e.g. 01712345678')" required />
                        <x-ui.input name="email" id="enq-email" :label="__('Email')" type="email" dir="ltr"
                                    autocomplete="email" optional />
                        <x-ui.select name="preferred_contact" id="enq-contact" :label="__('Best way to reach you')">
                            <option value="phone" @selected(old('preferred_contact') === 'phone')>{{ __('Phone call') }}</option>
                            <option value="whatsapp" @selected(old('preferred_contact') === 'whatsapp')>{{ __('WhatsApp') }}</option>
                            <option value="email" @selected(old('preferred_contact') === 'email')>{{ __('Email') }}</option>
                        </x-ui.select>
                        <x-ui.textarea name="message" id="enq-message" :label="__('Anything we should know?')" rows="3" optional
                                       :placeholder="__('Preferred floor, timeline, budget…')" />
                        <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">
                            <span class="uh-spinner" x-show="submitting" x-cloak></span>
                            <span x-text="submitting ? '{{ __('Sending…') }}' : '{{ __('Inquire Now') }}'">{{ __('Inquire Now') }}</span>
                        </button>
                        <p class="uh-hint">{{ __('We only use your number to answer this enquiry.') }}</p>
                    </form>

                    <form method="POST" action="{{ route('visits.store') }}" class="mt-5 space-y-4"
                          x-show="tab === 'visit'" x-cloak x-data="uhForm" @submit="submit">
                        @csrf
                        <input type="hidden" name="property_id" value="{{ $property->id }}">
                        <x-ui.input name="name" id="visit-name" :label="__('Your name')" autocomplete="name" required />
                        <x-ui.input name="phone" id="visit-phone" :label="__('Mobile number')" type="tel" dir="ltr"
                                    inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" required />
                        <x-ui.input name="preferred_at" id="visit-at" type="datetime-local"
                                    :label="__('Preferred date and time')" optional />
                        <x-ui.textarea name="notes" id="visit-notes" :label="__('Notes for the visit')" rows="2" optional />
                        <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">
                            <span class="uh-spinner" x-show="submitting" x-cloak></span>
                            <span x-text="submitting ? '{{ __('Sending…') }}' : '{{ __('Schedule a Viewing') }}'">{{ __('Schedule a Viewing') }}</span>
                        </button>
                        <p class="uh-hint">{{ __('We confirm the slot by phone before you travel.') }}</p>
                    </form>
                </div>
            </aside>
        </div>

        @if($similar->isNotEmpty())
            <section class="border-t border-line bg-paper">
                <div class="uh-container uh-section-tight" x-data="{}">
                    <div class="flex items-end justify-between gap-4">
                        <h2 class="uh-h2">{{ __('Similar homes') }}</h2>
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
            @if(filled($contactPhone))
                <a class="uh-btn-ondark flex-1" href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}">
                    <x-icon name="phone" class="size-4" />
                    {{ __('Call') }}
                </a>
            @endif
            @if(filled($whatsapp))
                <a class="uh-btn-whatsapp flex-1" href="{{ $whatsapp }}" rel="noopener">
                    <x-icon name="whatsapp" class="size-4" />
                    {{ __('WhatsApp Us') }}
                </a>
            @endif
            <a class="uh-btn-ondark flex-1" href="#enquire">{{ __('Inquire Now') }}</a>
        </div>
    </article>
@endsection
