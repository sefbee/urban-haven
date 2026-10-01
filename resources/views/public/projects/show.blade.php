@extends('layouts.public')

@php
    $cover = $project->featuredImage();
    $gallery = $project->galleryImages()->filter(fn ($item) => $cover === null || $item->id !== $cover->id)->values();
    $highlights = array_filter((array) ($project->highlights ?? []));
    $available = $project->properties;
    $decimals = (int) config('urbanhaven.maps.approximate_decimals', 2);
    $hasMap = filled($project->lat) && filled($project->lng);
@endphp

@section('content')
    <article class="pb-28 lg:pb-0">
        {{-- Project hero --}}
        <header class="relative isolate overflow-hidden bg-ink text-cream">
            @if($cover)
                <img src="{{ $cover->url(1920) }}"
                     srcset="{{ $cover->url(1280) }} 1280w, {{ $cover->url(1920) }} 1920w"
                     sizes="100vw" alt="{{ $cover->alt(app()->getLocale()) }}"
                     fetchpriority="high" decoding="async"
                     class="absolute inset-0 -z-10 size-full object-cover opacity-45">
                <div class="absolute inset-0 -z-10 bg-linear-to-t from-ink via-ink/70 to-ink/30"></div>
            @endif

            <div class="uh-container py-14 md:py-20">
                <x-ui.breadcrumbs on-dark :items="[
                    ['label' => __('Home'), 'url' => route('home')],
                    ['label' => __('Projects'), 'url' => route('projects.index')],
                    ['label' => $project->name],
                ]" />

                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <x-ui.status :status="$project->development_stage" />
                    @if(filled($project->trust_label))
                        <x-ui.badge tone="info">
                            <x-icon name="shield" class="size-3.5" />
                            {{ $project->trust_label }}
                        </x-ui.badge>
                    @endif
                </div>

                <h1 class="uh-display mt-4 max-w-3xl text-cream">{{ $project->name }}</h1>

                <p class="mt-4 flex items-center gap-2 text-sm text-cream/70">
                    <x-icon name="pin" class="size-4 shrink-0 text-gold-soft" />
                    {{ $project->locationArea?->name }}@if($project->city), {{ $project->city }}@endif
                </p>

                <dl class="mt-8 grid max-w-3xl gap-x-8 gap-y-5 sm:grid-cols-3">
                    @if(filled($project->developer_name))
                        <div>
                            <dt class="uh-eyebrow-light">{{ __('Developer') }}</dt>
                            <dd class="mt-1.5 font-medium">{{ $project->developer_name }}</dd>
                        </div>
                    @endif
                    @if($project->completion_date)
                        <div>
                            <dt class="uh-eyebrow-light">{{ __('Completion') }}</dt>
                            <dd class="mt-1.5 font-medium">{{ \App\Support\DisplayTimezone::format($project->completion_date, 'F Y') }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="uh-eyebrow-light">{{ __('Availability') }}</dt>
                        <dd class="uh-numeric mt-1.5 font-medium">
                            @if($availability['total'] > 0)
                                {{ __(':available available · :reserved reserved · :sold sold', $availability) }}
                            @else
                                {{ __('Ask our team') }}
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="mt-9 flex flex-wrap gap-3">
                    @if($available->isNotEmpty())
                        <a class="uh-btn-gold" href="#homes">{{ __('See available homes') }}</a>
                    @endif
                    <a class="uh-btn-ondark" href="#enquire">{{ __('Inquire Now') }}</a>
                </div>
            </div>
        </header>

        <div class="uh-container grid gap-10 py-10 lg:grid-cols-[minmax(0,1fr)_23rem] lg:gap-12 lg:py-14">
            <div class="min-w-0">
                @if(filled($project->description))
                    <section aria-labelledby="about-heading">
                        <h2 id="about-heading" class="uh-h2">{{ __('About this project') }}</h2>
                        <div class="uh-prose mt-4">{!! nl2br(e($project->description)) !!}</div>
                    </section>
                @endif

                @if($highlights !== [])
                    <section class="mt-10" aria-labelledby="highlights-heading">
                        <h2 id="highlights-heading" class="uh-h2">{{ __('Project highlights') }}</h2>
                        <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach($highlights as $highlight)
                                <li class="flex gap-3 rounded-lg bg-sand px-4 py-3.5 text-sm">
                                    <x-icon name="sparkle" class="mt-0.5 size-4 shrink-0 text-[var(--color-gold-ink)]" />
                                    <span>{{ is_array($highlight) ? implode(' — ', $highlight) : $highlight }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if($amenities->isNotEmpty())
                    <section class="mt-10" aria-labelledby="amenities-heading">
                        <h2 id="amenities-heading" class="uh-h2">{{ __('Facilities in the development') }}</h2>
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

                @if(filled($project->handover_info))
                    <section class="mt-10">
                        <div class="uh-alert uh-alert-info">
                            <x-icon name="info" class="mt-px size-4 shrink-0" />
                            <div>
                                <p class="font-semibold">{{ __('Handover') }}</p>
                                <p class="mt-1">{{ $project->handover_info }}</p>
                            </div>
                        </div>
                    </section>
                @endif

                @if($gallery->isNotEmpty())
                    <section class="mt-10" x-data="uhGallery({{ $gallery->count() }})" aria-labelledby="gallery-heading">
                        <h2 id="gallery-heading" class="uh-h2">{{ __('Gallery') }}</h2>
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach($gallery as $index => $image)
                                <button type="button" class="uh-media uh-media-zoom aspect-4/3 overflow-hidden rounded-lg"
                                        @click="open({{ $index }})"
                                        aria-label="{{ __('Open image :number at full size', ['number' => $index + 1]) }}">
                                    <img src="{{ $image->url(768) }}" alt="{{ $image->alt(app()->getLocale()) }}" loading="lazy" decoding="async">
                                </button>
                            @endforeach
                        </div>

                        <div x-show="lightbox" x-cloak class="fixed inset-0 z-100 flex items-center justify-center bg-ink/95 p-4"
                             role="dialog" aria-modal="true" aria-label="{{ __('Project gallery') }}"
                             @keydown.escape.window="close()" @keydown.left.window="previous()" @keydown.right.window="next()">
                            <button type="button" class="absolute right-4 top-4 uh-icon-btn text-cream hover:bg-white/10"
                                    @click="close()" aria-label="{{ __('Close gallery') }}">
                                <x-icon name="close" class="size-6" />
                            </button>
                            @foreach($gallery as $index => $image)
                                <img x-show="active === {{ $index }}" src="{{ $image->url(1920) }}" loading="lazy"
                                     alt="{{ $image->alt(app()->getLocale()) }}"
                                     class="max-h-[85vh] max-w-full rounded-lg object-contain">
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($hasMap)
                    <section class="mt-10" aria-labelledby="location-heading">
                        <h2 id="location-heading" class="uh-h2">{{ __('Location') }}</h2>
                        <div class="uh-panel-flush mt-4 overflow-hidden">
                            <div data-uh-map class="h-72 w-full sm:h-96"
                                 data-lat="{{ round((float) $project->lat, $decimals) }}"
                                 data-lng="{{ round((float) $project->lng, $decimals) }}"
                                 data-zoom="14" data-radius="700"
                                 data-tiles="{{ config('urbanhaven.maps.tile_url') }}"
                                 data-attribution="{{ e(config('urbanhaven.maps.attribution')) }}"></div>
                            <p class="border-t border-line px-4 py-3 text-xs text-[var(--color-muted)]">
                                {{ __('The circle shows the neighbourhood. Our desk shares the site address for viewings.') }}
                            </p>
                        </div>
                    </section>
                @endif
            </div>

            {{-- Enquiry panel --}}
            <aside class="lg:min-w-0">
                <div class="lg:sticky lg:top-24">
                    <div class="uh-panel" id="enquire">
                        <h2 class="uh-h3">{{ __('Contact Urban Haven Agent') }}</h2>
                        <p class="mt-1.5 text-sm text-[var(--color-muted)]">
                            {{ __('Tell us what you are looking for and we will send the unit plans and current pricing.') }}
                        </p>

                        <div class="mt-5" x-data="{ tab: 'enquire' }" @uh:open-visit.window="tab = 'visit'">
                            <div class="mb-4 flex gap-1 rounded-lg bg-sand p-1" role="tablist" aria-label="{{ __('Contact options') }}">
                                <button type="button" role="tab" class="flex-1 rounded-md px-3 py-1.5 text-sm font-semibold" :class="tab === 'enquire' ? 'bg-paper shadow-sm' : 'text-[var(--color-muted)]'" :aria-selected="tab === 'enquire'" @click="tab = 'enquire'">{{ __('Enquire') }}</button>
                                <button type="button" role="tab" class="flex-1 rounded-md px-3 py-1.5 text-sm font-semibold" :class="tab === 'visit' ? 'bg-paper shadow-sm' : 'text-[var(--color-muted)]'" :aria-selected="tab === 'visit'" @click="tab = 'visit'">{{ __('Book a visit') }}</button>
                            </div>
                            <div x-show="tab === 'enquire'">
                                @include('public.partials.lead-form', [
                                    'formType' => 'inquiry',
                                    'prefix' => 'proj',
                                    'leadType' => 'property_inquiry',
                                    'source' => 'project_page',
                                    'projectId' => $project->id,
                                    'submitLabel' => __('Inquire Now'),
                                    'messageLabel' => __('What are you looking for?'),
                                    'messagePlaceholder' => __('Number of bedrooms, budget, timeline…'),
                                ])
                            </div>
                            <div x-show="tab === 'visit'" x-cloak>
                                @include('public.partials.lead-form', [
                                    'formType' => 'visit',
                                    'prefix' => 'proj-visit',
                                    'source' => 'project_page',
                                    'projectId' => $project->id,
                                ])
                            </div>
                        </div>

                        @if($whatsapp)
                            <div class="mt-5 border-t border-line pt-5">
                                <a class="uh-btn-gold uh-btn-block" href="{{ $whatsapp }}" rel="noopener" target="_blank"
                                   data-track="whatsapp_click" data-track-project_id="{{ $project->id }}" data-track-location="project_aside">
                                    <x-icon name="whatsapp" class="size-4" />
                                    {{ __('WhatsApp Us') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </aside>
        </div>

        {{-- Homes in the project --}}
        <section id="homes" class="border-t border-line bg-paper scroll-mt-20">
            <div class="uh-container uh-section-tight">
                <h2 class="uh-h2">{{ __('Homes in this project') }}</h2>

                @if($available->isNotEmpty())
                    <p class="mt-2 text-sm text-[var(--color-muted)]">
                        {{ __('Each home below is listed and sold directly by Urban Haven.') }}
                    </p>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($available as $property)
                            @include('public.partials.property-card', ['property' => $property])
                        @endforeach
                    </div>
                @else
                    <x-ui.empty icon="home" :title="__('No homes are listed right now')"
                                :description="__('Every home in this project is currently reserved or sold. Ask our desk to be told first when one becomes available.')">
                        <a class="uh-btn-primary uh-btn-sm" href="#enquire">{{ __('Inquire Now') }}</a>
                    </x-ui.empty>
                @endif
            </div>
        </section>

        {{-- Mobile conversion bar --}}
        <div class="fixed inset-x-0 bottom-0 z-40 flex gap-2 border-t border-white/10 bg-ink/95 p-3 backdrop-blur-sm lg:hidden">
            @if($whatsapp)
                <a class="uh-btn-gold flex-1" href="{{ $whatsapp }}" rel="noopener" target="_blank"
                   data-track="whatsapp_click" data-track-project_id="{{ $project->id }}" data-track-location="project_mobile_bar">
                    <x-icon name="whatsapp" class="size-4" />
                    {{ __('WhatsApp Us') }}
                </a>
            @endif
            <a class="uh-btn-ondark flex-1" href="#enquire">{{ __('Inquire Now') }}</a>
            <a class="uh-btn-ondark flex-1" href="#enquire" @click="$dispatch('uh:open-visit')">{{ __('Book a visit') }}</a>
        </div>
    </article>
@endsection
