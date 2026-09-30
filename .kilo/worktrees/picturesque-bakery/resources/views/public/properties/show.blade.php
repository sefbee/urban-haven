@extends('layouts.public')

@php
    $media = $property->media;
    $amenities = $property->amenities();
    $units = $property->units;
    $area = $property->area_value ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit) : null;
    $compactPrice = \App\Support\MoneyFormatter::compactBdt($property->price);
    $decimals = (int) config('urbanhaven.maps.approximate_decimals', 2);
    $hasMap = filled($property->lat) && filled($property->lng);
    $mapLat = $hasMap ? round((float) $property->lat, $property->map_approximation ? $decimals : 6) : null;
    $mapLng = $hasMap ? round((float) $property->lng, $property->map_approximation ? $decimals : 6) : null;
    $visitTab = $errors->hasAny(['preferred_at', 'notes']);
@endphp

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container py-4">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Buy & rent'), 'url' => route('properties.index')],
                ['label' => $property->locationArea?->name, 'url' => $property->locationArea ? route('properties.index', ['location_area_id' => $property->locationArea->id]) : null],
                ['label' => $property->title],
            ]" />
        </div>
    </div>

    <article class="pb-28 lg:pb-0">
        {{-- Gallery --}}
        @if($media->isNotEmpty())
            <section x-data="uhGallery({{ $media->count() }})" class="bg-ink">
                <div class="uh-container py-4 sm:py-6">
                    <div class="relative overflow-hidden rounded-xl bg-black/20">
                        @foreach($media as $index => $image)
                            <button type="button" x-show="active === {{ $index }}" @if($index) x-cloak @endif
                                    class="uh-media block aspect-16/10 w-full cursor-zoom-in sm:aspect-16/9"
                                    @click="open({{ $index }})"
                                    aria-label="{{ __('Open image :number at full size', ['number' => $index + 1]) }}">
                                <img src="{{ $image->url(1280) }}"
                                     srcset="{{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w, {{ $image->url(1920) }} 1920w"
                                     sizes="(min-width: 1280px) 1280px, 100vw"
                                     alt="{{ $image->alt(app()->getLocale()) }}"
                                     @if($index === 0) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                            </button>
                        @endforeach

                        @if($media->count() > 1)
                            <button type="button" @click="previous()"
                                    class="absolute left-3 top-1/2 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-ink/70 text-cream transition hover:bg-ink"
                                    aria-label="{{ __('Previous image') }}">
                                <x-icon name="chevron-left" class="size-5" />
                            </button>
                            <button type="button" @click="next()"
                                    class="absolute right-3 top-1/2 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-ink/70 text-cream transition hover:bg-ink"
                                    aria-label="{{ __('Next image') }}">
                                <x-icon name="chevron-right" class="size-5" />
                            </button>
                            <p class="absolute bottom-3 right-3 rounded-md bg-ink/80 px-2.5 py-1 text-xs font-semibold text-cream">
                                <span class="uh-numeric" x-text="active + 1">1</span> / <span class="uh-numeric">{{ $media->count() }}</span>
                            </p>
                        @endif
                    </div>

                    @if($media->count() > 1)
                        <div class="mt-3 flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="{{ __('Gallery thumbnails') }}">
                            @foreach($media as $index => $image)
                                <button type="button" role="tab" @click="select({{ $index }})"
                                        :aria-selected="(active === {{ $index }}).toString()"
                                        class="uh-media size-16 shrink-0 overflow-hidden rounded-lg transition sm:size-20"
                                        :class="active === {{ $index }} ? 'ring-2 ring-gold' : 'opacity-60 hover:opacity-100'">
                                    <img src="{{ $image->thumbUrl() }}" alt="{{ __('Image :number', ['number' => $index + 1]) }}" loading="lazy" decoding="async">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Lightbox --}}
                <div x-show="lightbox" x-cloak class="fixed inset-0 z-100 flex items-center justify-center bg-ink/95 p-4"
                     role="dialog" aria-modal="true" aria-label="{{ __('Property gallery') }}"
                     @keydown.escape.window="close()" @keydown.left.window="previous()" @keydown.right.window="next()">
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

        <div class="uh-container grid gap-10 py-8 lg:grid-cols-[minmax(0,1fr)_23rem] lg:gap-12 lg:py-12">
            <div class="min-w-0">
                {{-- Title block --}}
                <header>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.badge tone="gold">{{ $property->listing_type === 'rent' ? __('For rent') : __('For sale') }}</x-ui.badge>
                        <x-ui.status :status="$property->availability" />
                        @if($property->propertyType)
                            <x-ui.badge tone="outline">{{ $property->propertyType->label }}</x-ui.badge>
                        @endif
                        @if(filled($property->trust_label))
                            <x-ui.badge tone="info">
                                <x-icon name="shield" class="size-3.5" />
                                {{ $property->trust_label }}
                            </x-ui.badge>
                        @endif
                    </div>

                    <h1 class="uh-h1 mt-4">{{ $property->title }}</h1>

                    <p class="mt-3 flex items-center gap-2 text-sm text-[var(--color-muted)]">
                        <x-icon name="pin" class="size-4 shrink-0 text-[var(--color-gold-ink)]" />
                        {{ $property->locationArea?->name }}@if($property->locationArea?->city), {{ $property->locationArea->city }}@endif
                    </p>

                    <div class="mt-5 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                        <p class="font-display text-3xl tracking-tight text-forest sm:text-4xl">
                            {{ \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis) }}
                        </p>
                        @if($compactPrice)
                            <p class="text-sm text-[var(--color-muted)]">≈ {{ $compactPrice }}</p>
                        @endif
                    </div>

                    @if($property->reference || $property->last_updated_at)
                        <p class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[var(--color-muted)]">
                            @if($property->reference)
                                <span>{{ __('Reference') }}: <span class="font-medium text-ink">{{ $property->reference }}</span></span>
                            @endif
                            @if($property->last_updated_at)
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="clock" class="size-3.5" />
                                    {{ __('Updated :date', ['date' => \App\Support\DisplayTimezone::format($property->last_updated_at, 'd M Y')]) }}
                                </span>
                            @endif
                        </p>
                    @endif
                </header>

                {{-- Specifications --}}
                <section class="mt-8" aria-labelledby="spec-heading">
                    <h2 id="spec-heading" class="uh-h4">{{ __('Specifications') }}</h2>
                    <div class="uh-specs mt-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4">
                        @if($property->bedrooms)
                            <x-ui.spec :label="__('Bedrooms')" icon="bed">{{ $property->bedrooms }}</x-ui.spec>
                        @endif
                        @if($property->bathrooms)
                            <x-ui.spec :label="__('Bathrooms')" icon="bath">{{ $property->bathrooms }}</x-ui.spec>
                        @endif
                        @if($area)
                            <x-ui.spec :label="__('Size')" icon="area">{{ $area }}</x-ui.spec>
                        @endif
                        @if($property->floor_number)
                            <x-ui.spec :label="__('Floor')" icon="floor">{{ $property->floor_number }}</x-ui.spec>
                        @endif
                        <x-ui.spec :label="__('Furnishing')" icon="key">{{ $property->is_furnished ? __('Furnished') : __('Unfurnished') }}</x-ui.spec>
                        @if($property->propertyType)
                            <x-ui.spec :label="__('Type')" icon="building">{{ $property->propertyType->label }}</x-ui.spec>
                        @endif
                    </div>
                </section>

                {{-- Description --}}
                @if(filled($property->description))
                    <section class="mt-10" aria-labelledby="about-heading">
                        <h2 id="about-heading" class="uh-h2">{{ __('About this home') }}</h2>
                        <div class="uh-prose mt-4">{!! nl2br(e($property->description)) !!}</div>
                    </section>
                @endif

                {{-- Amenities --}}
                @if($amenities->isNotEmpty())
                    <section class="mt-10" aria-labelledby="amenities-heading">
                        <h2 id="amenities-heading" class="uh-h2">{{ __('Amenities') }}</h2>
                        <ul class="mt-4 grid gap-x-6 gap-y-2.5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($amenities as $amenity)
                                <li class="flex items-center gap-2.5 text-sm">
                                    <span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-forest/10 text-forest">
                                        <x-icon name="check" class="size-3" />
                                    </span>
                                    {{ $amenity->label }}
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Units --}}
                @if($units->isNotEmpty())
                    <section class="mt-10" aria-labelledby="units-heading">
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

                {{-- Location --}}
                @if($hasMap)
                    <section class="mt-10" aria-labelledby="location-heading">
                        <h2 id="location-heading" class="uh-h2">{{ __('Location') }}</h2>
                        <div class="uh-panel-flush mt-4 overflow-hidden">
                            <div data-uh-map class="h-72 w-full sm:h-96"
                                 data-lat="{{ $mapLat }}" data-lng="{{ $mapLng }}"
                                 data-zoom="{{ $property->map_approximation ? 14 : 16 }}"
                                 @if($property->map_approximation) data-radius="600" @endif
                                 data-tiles="{{ config('urbanhaven.maps.tile_url') }}"
                                 data-attribution="{{ e(config('urbanhaven.maps.attribution')) }}"></div>
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
                    <section class="mt-10">
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
                    </section>
                @endif
            </div>

            {{-- Conversion panel --}}
            <aside class="lg:min-w-0">
                <div class="lg:sticky lg:top-24" x-data="{ tab: '{{ $visitTab ? 'visit' : 'enquire' }}' }">
                    <div class="uh-panel" id="enquire">
                        <h2 class="uh-h3">{{ __('Interested in this home?') }}</h2>
                        <p class="mt-1.5 text-sm text-[var(--color-muted)]">{{ __('Our sales desk replies during business hours, usually the same day.') }}</p>

                        <div class="mt-5 grid grid-cols-2 gap-1 rounded-lg bg-sand p-1" role="tablist" aria-label="{{ __('Contact options') }}">
                            <button type="button" role="tab" @click="tab = 'enquire'" :aria-selected="(tab === 'enquire').toString()"
                                    class="min-h-9 rounded-md px-3 text-[0.8125rem] font-semibold transition"
                                    :class="tab === 'enquire' ? 'bg-paper text-ink shadow-sm' : 'text-[var(--color-muted)] hover:text-ink'">
                                {{ __('Enquire') }}
                            </button>
                            <button type="button" role="tab" @click="tab = 'visit'" :aria-selected="(tab === 'visit').toString()"
                                    class="min-h-9 rounded-md px-3 text-[0.8125rem] font-semibold transition"
                                    :class="tab === 'visit' ? 'bg-paper text-ink shadow-sm' : 'text-[var(--color-muted)] hover:text-ink'">
                                {{ __('Book a visit') }}
                            </button>
                        </div>

                        {{-- Enquiry --}}
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
                                <span x-text="submitting ? '{{ __('Sending…') }}' : '{{ __('Enquire about this property') }}'">{{ __('Enquire about this property') }}</span>
                            </button>
                            <p class="uh-hint">{{ __('We only use your number to answer this enquiry.') }}</p>
                        </form>

                        {{-- Visit --}}
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
                                <span x-text="submitting ? '{{ __('Sending…') }}' : '{{ __('Schedule a visit') }}'">{{ __('Schedule a visit') }}</span>
                            </button>
                            <p class="uh-hint">{{ __('We confirm the slot by phone before you travel.') }}</p>
                        </form>

                        <div class="mt-5 border-t border-line pt-5">
                            <a class="uh-btn-gold uh-btn-block" href="{{ $whatsapp }}" rel="noopener">
                                <x-icon name="whatsapp" class="size-4" />
                                {{ __('WhatsApp about this property') }}
                            </a>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        {{-- Similar --}}
        @if($similar->isNotEmpty())
            <section class="border-t border-line bg-paper">
                <div class="uh-container uh-section-tight">
                    <h2 class="uh-h2">{{ __('Similar homes') }}</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($similar as $item)
                            @include('public.partials.property-card', ['property' => $item])
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Mobile conversion bar --}}
        <div class="fixed inset-x-0 bottom-0 z-40 flex gap-2 border-t border-white/10 bg-ink/95 p-3 backdrop-blur-sm lg:hidden">
            <a class="uh-btn-gold flex-1" href="{{ $whatsapp }}" rel="noopener">
                <x-icon name="whatsapp" class="size-4" />
                {{ __('WhatsApp') }}
            </a>
            <a class="uh-btn-ondark flex-1" href="#enquire">{{ __('Enquire') }}</a>
        </div>
    </article>
@endsection
