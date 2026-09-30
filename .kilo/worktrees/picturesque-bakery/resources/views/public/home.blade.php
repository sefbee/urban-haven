@extends('layouts.public')

@php
    $heroContent = $hero?->content ?? [];
    $aboutContent = $about?->content ?? [];
    $intentItems = $intents?->content['items'] ?? [];
    $heroImage = $properties->first(fn ($property) => $property->featuredImage() !== null)?->featuredImage();
@endphp

@section('content')
    {{-- 1. Hero + search --}}
    <section class="relative isolate overflow-hidden bg-ink text-cream">
        @if($heroImage)
            <img src="{{ $heroImage->url(1920) }}"
                 srcset="{{ $heroImage->url(768) }} 768w, {{ $heroImage->url(1280) }} 1280w, {{ $heroImage->url(1920) }} 1920w"
                 sizes="100vw" alt="" fetchpriority="high" decoding="async"
                 class="absolute inset-0 -z-10 size-full object-cover">
            <div class="absolute inset-0 -z-10 bg-linear-to-b from-ink/88 via-ink/70 to-ink/92"></div>
        @else
            <div class="uh-hero-flat absolute inset-0 -z-10"></div>
        @endif

        <div class="uh-container py-16 md:py-24 lg:py-28">
            <div class="max-w-3xl">
                <p class="uh-eyebrow-light">{{ $heroContent['eyebrow'] ?? __('Urban Haven Properties Ltd.') }}</p>
                <h1 class="uh-display mt-4 text-cream">{{ $heroContent['title'] ?? __('Homes with quiet confidence in Dhaka.') }}</h1>
                @if(filled($heroContent['body'] ?? null))
                    <p class="mt-5 max-w-2xl text-base leading-relaxed text-cream/80 sm:text-lg">{{ $heroContent['body'] }}</p>
                @endif
            </div>

            {{-- Search --}}
            <form method="GET" action="{{ route('properties.index') }}"
                  class="mt-9 rounded-xl bg-paper/97 p-4 shadow-[0_24px_60px_-30px_rgba(0,0,0,0.6)] ring-1 ring-white/20 backdrop-blur-sm sm:p-5"
                  x-data="uhForm" @submit="submit">
                <h2 class="sr-only">{{ __('Search properties') }}</h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto]">
                    <x-ui.select name="listing_type" :label="__('I want to')" sr-label>
                        <option value="">{{ __('Buy or rent') }}</option>
                        <option value="sale">{{ __('Buy') }}</option>
                        <option value="rent">{{ __('Rent') }}</option>
                    </x-ui.select>

                    <x-ui.select name="location_area_id" :label="__('Area')" sr-label>
                        <option value="">{{ __('Any area') }}</option>
                        @foreach($searchAreas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select name="property_type_id" :label="__('Property type')" sr-label>
                        <option value="">{{ __('Any type') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}">{{ $type->label }}</option>
                        @endforeach
                    </x-ui.select>

                    <button type="submit" class="uh-btn-primary lg:px-7" :disabled="submitting">
                        <x-icon name="search" class="size-4" x-show="!submitting" />
                        <span class="uh-spinner" x-show="submitting" x-cloak></span>
                        {{ __('Search') }}
                    </button>
                </div>
            </form>

            {{-- Trust strip: factual, no invented claims --}}
            <ul class="mt-8 flex flex-wrap items-center gap-x-7 gap-y-3 text-sm text-cream/75">
                <li class="flex items-center gap-2">
                    <x-icon name="shield" class="size-4 shrink-0 text-gold" />
                    {{ __('We own every home we list') }}
                </li>
                <li class="flex items-center gap-2">
                    <x-icon name="check-circle" class="size-4 shrink-0 text-gold" />
                    {{ __('Published by our own team') }}
                </li>
                <li class="flex items-center gap-2">
                    <x-icon name="phone" class="size-4 shrink-0 text-gold" />
                    {{ __('Direct line to the sales desk') }}
                </li>
            </ul>
        </div>
    </section>

    {{-- 2. Featured properties --}}
    <section class="uh-section">
        <div class="uh-container">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="uh-eyebrow">{{ __('Featured') }}</p>
                    <h2 class="uh-h2 mt-2">{{ __('Homes in view') }}</h2>
                </div>
                <a class="uh-link-quiet inline-flex items-center gap-1.5 text-sm" href="{{ route('properties.index') }}">
                    {{ __('All listings') }}
                    <x-icon name="arrow-right" class="size-4" />
                </a>
            </div>

            @if($properties->isNotEmpty())
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($properties as $property)
                        @include('public.partials.property-card', ['property' => $property])
                    @endforeach
                </div>
            @else
                <x-ui.empty class="mt-8" icon="home" :title="__('No featured homes yet')"
                            :description="__('Our sales desk is preparing the next release. Browse the full list in the meantime.')">
                    <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Browse all homes') }}</a>
                </x-ui.empty>
            @endif
        </div>
    </section>

    {{-- 3. Featured projects --}}
    @if($projects->isNotEmpty())
        <section class="border-t border-line bg-paper">
            <div class="uh-container uh-section">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="uh-eyebrow">{{ __('Developments') }}</p>
                        <h2 class="uh-h2 mt-2">{{ __('Our projects') }}</h2>
                        <p class="uh-lede mt-3 max-w-xl">{{ __('A project is a whole building or community. Open one to see the homes still available inside it.') }}</p>
                    </div>
                    <a class="uh-link-quiet inline-flex items-center gap-1.5 text-sm" href="{{ route('projects.index') }}">
                        {{ __('All projects') }}
                        <x-icon name="arrow-right" class="size-4" />
                    </a>
                </div>

                <div class="mt-8 grid gap-5 md:grid-cols-3">
                    @foreach($projects as $project)
                        @include('public.partials.project-card', ['project' => $project])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 4. Why Urban Haven --}}
    <section class="border-t border-line bg-paper">
        <div class="uh-container uh-section grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <p class="uh-eyebrow">{{ __('Why Urban Haven') }}</p>
                <h2 class="uh-h2 mt-2">{{ $aboutContent['title'] ?? __('Built for buyers who want facts, not theatre.') }}</h2>
                @if(filled($aboutContent['body'] ?? null))
                    <p class="uh-lede mt-5">{{ $aboutContent['body'] }}</p>
                @endif
                <a class="uh-btn-outline mt-7" href="{{ route('cms.show', 'about') }}">{{ __('About the company') }}</a>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:col-span-7 lg:content-start">
                @foreach([
                    ['icon' => 'key', 'title' => __('Single-owner inventory'), 'body' => __('Every home here belongs to Urban Haven Properties Ltd. There is no third-party agent between you and the seller.')],
                    ['icon' => 'document', 'title' => __('Specifications you can check'), 'body' => __('Size, floor, orientation and unit availability come from our own records and are updated when they change.')],
                    ['icon' => 'users', 'title' => __('One accountable desk'), 'body' => __('Enquiries reach our sales team directly, and the same team handles your viewing and paperwork.')],
                    ['icon' => 'pin', 'title' => __('Dhaka specialists'), 'body' => __('We build and sell in the neighbourhoods we know, so we can talk about the street, not just the floor plan.')],
                ] as $pillar)
                    <div class="rounded-xl bg-cream p-5 ring-1 ring-line">
                        <span class="flex size-10 items-center justify-center rounded-lg bg-forest/10 text-forest">
                            <x-icon :name="$pillar['icon']" class="size-5" />
                        </span>
                        <h3 class="uh-h4 mt-4">{{ $pillar['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-[var(--color-muted)]">{{ $pillar['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 5. Location --}}
    @if($areas->isNotEmpty())
        <section class="border-t border-line bg-sand">
            <div class="uh-container uh-section-tight">
                <p class="uh-eyebrow">{{ __('Where we build') }}</p>
                <h2 class="uh-h2 mt-2">{{ __('Browse by area') }}</h2>
                <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($areas as $area)
                        <a href="{{ route('properties.index', ['location_area_id' => $area->id]) }}"
                           class="group flex items-center justify-between gap-3 rounded-xl bg-paper px-4 py-4 ring-1 ring-line transition hover:ring-forest">
                            <span class="min-w-0">
                                <span class="block truncate text-[0.9375rem] font-semibold">{{ $area->name }}</span>
                                <span class="mt-0.5 block text-xs text-[var(--color-muted)]">{{ $area->city }}</span>
                            </span>
                            <span class="flex shrink-0 items-center gap-2 text-xs font-semibold text-[var(--color-muted)]">
                                <span class="uh-numeric">{{ $area->properties_count }}</span>
                                <x-icon name="arrow-right" class="size-4 text-[var(--color-gold-ink)] transition group-hover:translate-x-0.5" />
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 6. Discovery shortcuts --}}
    @if(filled($intentItems))
        <section class="uh-section-tight">
            <div class="uh-container">
                <p class="uh-eyebrow">{{ __('Start with a shortlist') }}</p>
                <h2 class="uh-h2 mt-2">{{ __('What are you looking for?') }}</h2>
                <nav class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" aria-label="{{ __('Quick links') }}">
                    @foreach($intentItems as $intent)
                        <a class="group flex items-center justify-between gap-3 rounded-xl bg-paper px-5 py-5 ring-1 ring-line transition hover:ring-forest"
                           href="{{ $intent['url'] ?? route('properties.index') }}">
                            <span class="font-semibold">{{ $intent['label'] ?? __('Browse') }}</span>
                            <x-icon name="arrow-right" class="size-4 text-[var(--color-gold-ink)] transition group-hover:translate-x-0.5" />
                        </a>
                    @endforeach
                </nav>
            </div>
        </section>
    @endif

    {{-- 7. Conversion CTA --}}
    @php($whatsapp = preg_replace('/\D+/', '', (string) config('urbanhaven.whatsapp.number')))
    <section class="uh-hero-flat text-cream">
        <div class="uh-container flex flex-col items-start gap-7 py-14 md:flex-row md:items-center md:justify-between md:py-16">
            <div class="max-w-xl">
                <h2 class="uh-h2 text-cream">{{ __('Tell us what you are looking for') }}</h2>
                <p class="mt-3 text-cream/80">{{ __('Send a short brief and our sales desk will come back with the homes that actually match it.') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a class="uh-btn-gold uh-btn-lg" href="{{ route('cms.show', 'contact') }}">{{ __('Talk to an advisor') }}</a>
                @if(filled($whatsapp))
                    <a class="uh-btn-ondark uh-btn-lg" href="https://wa.me/{{ $whatsapp }}" rel="noopener">
                        <x-icon name="whatsapp" class="size-4" />
                        {{ __('WhatsApp') }}
                    </a>
                @endif
            </div>
        </div>
    </section>
@endsection
