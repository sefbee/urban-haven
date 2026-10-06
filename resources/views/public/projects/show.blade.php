@extends('layouts.public')

@php
    $cover = $project->featuredImage();
    $gallery = $project->galleryImages()->filter(fn ($item) => $cover === null || $item->id !== $cover->id)->values();
    $photos = collect([$cover])->filter()->concat($gallery)->values();
    $thumbs = $photos->slice(1, 2);
    $hiddenPhotos = max(0, $photos->count() - 3);
    $highlights = array_filter((array) ($project->highlights ?? []));
    $available = $project->properties;
    $decimals = (int) config('urbanhaven.maps.approximate_decimals', 2);
    $hasMap = filled($project->lat) && filled($project->lng);
    $mapPoint = $hasMap ? ['lat' => round((float) $project->lat, $decimals), 'lng' => round((float) $project->lng, $decimals), 'approximate' => true] : null;
    $videoEmbed = \App\Support\VideoEmbed::embedUrl($project->video_url);
    $place = collect([$project->locationArea?->name, $project->city])->filter()->implode(', ');
    $stage = match ($project->development_stage) {
        'completed' => __('Completed'),
        'ongoing' => __('Under Construction'),
        'upcoming' => __('Upcoming'),
        default => $project->development_stage,
    };
    $completion = $project->completion_date ? \App\Support\DisplayTimezone::format($project->completion_date, 'F Y') : null;
    $startingPrice = $available->where('price', '>', 0)->min('price');
    $startingLabel = $startingPrice ? \App\Support\MoneyFormatter::formatBdt($startingPrice) : null;
    $compactStart = $startingPrice ? \App\Support\MoneyFormatter::compactBdt($startingPrice) : null;
    $headlineStart = $compactStart ? 'BDT '.$compactStart : $startingLabel;
    $quickFacts = array_values(array_filter([
        ['icon' => 'building', 'text' => $stage],
        $completion ? ['icon' => 'calendar', 'text' => __('Handover :date', ['date' => $completion])] : null,
        $availability['total'] > 0 ? ['icon' => 'home', 'text' => trans_choice(':count home available|:count homes available', $availability['available'], ['count' => $availability['available']])] : null,
    ]));
    $overview = array_values(array_filter([
        ['icon' => 'building', 'label' => __('Stage'), 'value' => $stage],
        $completion ? ['icon' => 'calendar', 'label' => __('Completion'), 'value' => $completion] : null,
        $place ? ['icon' => 'pin', 'label' => __('Location'), 'value' => $place] : null,
        filled($project->developer_name) ? ['icon' => 'shield', 'label' => __('Developer'), 'value' => $project->developer_name] : null,
        $availability['total'] > 0 ? ['icon' => 'home', 'label' => __('Available homes'), 'value' => $availability['available'].' / '.$availability['total']] : null,
        $availability['reserved'] > 0 ? ['icon' => 'key', 'label' => __('Reserved'), 'value' => (string) $availability['reserved']] : null,
        $startingLabel ? ['icon' => 'tag', 'label' => __('Starting price'), 'value' => $startingLabel] : null,
    ]));
    $tabs = array_filter([
        'overview' => __('Overview'),
        'about' => filled($project->description) ? __('About') : null,
        'highlights' => $highlights !== [] ? __('Highlights') : null,
        'facilities' => $amenities->isNotEmpty() ? __('Facilities') : null,
        'handover' => filled($project->handover_info) ? __('Handover') : null,
        'location' => $hasMap ? __('Location') : null,
        'contact' => __('Contact us'),
    ]);
    $contactName = __('Urban Haven sales team');
    $contactInitials = \Illuminate\Support\Str::of($contactName)->explode(' ')->filter()->take(2)->map(fn ($word) => mb_substr($word, 0, 1))->implode('');
    $officeHours = \App\Models\Setting::get('office_hours');
    $focusName = "setTimeout(() => \$el.querySelector('#panel-enquire [name=name]')?.focus({ preventScroll: true }), 500)";
    $scrollToContact = "\$el.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' })";
@endphp

@section('content')
    <article class="uh-pd pb-24 lg:pb-0" x-data="uhGallery({{ $photos->count() }})">
        <div class="uh-container">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Projects'), 'url' => route('projects.index')],
                ['label' => $project->name],
            ]" />

            <header class="uh-pd-head">
                <div class="min-w-0">
                    <div class="uh-pd-tags">
                        <span class="uh-pd-tag is-dark">{{ $stage }}</span>
                        @if(filled($project->trust_label))
                            <span class="uh-pd-tag">
                                <x-icon name="shield" class="size-3.5" />
                                {{ $project->trust_label }}
                            </span>
                        @endif
                    </div>
                    <h1 class="uh-pd-title">{{ $project->name }}</h1>
                    @if($place)
                        <p class="uh-pd-address">
                            <x-icon name="pin" class="size-4" />
                            {{ $place }}
                        </p>
                    @endif
                    <ul class="uh-pd-quick" aria-label="{{ __('Key facts') }}">
                        @foreach($quickFacts as $fact)
                            <li>
                                <x-icon :name="$fact['icon']" class="size-[1.125rem]" />
                                <span class="uh-numeric">{{ $fact['text'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if($startingLabel)
                    <div class="uh-pd-head-side">
                        <p class="uh-pd-updated">{{ __('Homes from') }}</p>
                        <p class="uh-pd-price uh-numeric">{{ $headlineStart }}</p>
                    </div>
                @endif
            </header>

            <div class="uh-pd-layout">
                <x-media-stage class="uh-pd-stage" :title="$project->name" :photo-count="$photos->count()"
                               :video="$videoEmbed" :coordinates="$mapPoint">
                    <section @class(['uh-pd-gallery', 'has-thumbs' => $thumbs->isNotEmpty(), 'has-two' => $thumbs->count() === 2]) aria-label="{{ __('Photographs') }}">
                        @if($photos->isNotEmpty())
                            <button type="button" class="uh-pd-shot is-main" @click="open(0)"
                                    aria-label="{{ __('Open image :number at full size', ['number' => 1]) }}">
                                <img src="{{ $photos->first()->url(1280) }}"
                                     srcset="{{ $photos->first()->url(768) }} 768w, {{ $photos->first()->url(1280) }} 1280w, {{ $photos->first()->url(1920) }} 1920w"
                                     sizes="(min-width: 1100px) 60vw, 100vw"
                                     alt="{{ $photos->first()->alt(app()->getLocale()) }}"
                                     fetchpriority="high" decoding="async">
                            </button>
                            @foreach($thumbs as $index => $image)
                                <button type="button" class="uh-pd-shot" @click="open({{ $index }})"
                                        aria-label="{{ __('Open image :number at full size', ['number' => $index + 1]) }}">
                                    <img src="{{ $image->url(768) }}" alt="{{ $image->alt(app()->getLocale()) }}" loading="lazy" decoding="async">
                                    @if($loop->last && $hiddenPhotos > 0)
                                        <span class="uh-pd-shot-more">+{{ $hiddenPhotos }}</span>
                                    @endif
                                </button>
                            @endforeach
                            @if($photos->count() > 1)
                                <button type="button" class="uh-pd-gallery-all" @click="open(0)">
                                    <x-icon name="grid" class="size-4" />
                                    {{ __('View all :count photos', ['count' => $photos->count()]) }}
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

                <aside class="uh-pd-summary uh-surface" aria-label="{{ __('Pricing and next steps') }}">
                    <div>
                        <p class="uh-pd-summary-label">{{ $startingLabel ? __('Homes from') : __('Pricing') }}</p>
                        <p class="uh-pd-summary-price uh-numeric">{{ $startingLabel ?? __('On request') }}</p>
                        @if($availability['total'] > 0)
                            <p class="uh-pd-summary-note uh-numeric">{{ __(':available of :total homes available', ['available' => $availability['available'], 'total' => $availability['total']]) }}</p>
                        @endif
                    </div>

                    <dl class="uh-pd-summary-facts">
                        <div>
                            <dt>{{ __('Stage') }}</dt>
                            <dd>{{ $stage }}</dd>
                        </div>
                        @if($completion)
                            <div>
                                <dt>{{ __('Completion') }}</dt>
                                <dd class="uh-numeric">{{ $completion }}</dd>
                            </div>
                        @endif
                        @if(filled($project->developer_name))
                            <div>
                                <dt>{{ __('Developer') }}</dt>
                                <dd>{{ $project->developer_name }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="uh-pd-summary-actions">
                        <button type="button" class="uh-btn-primary w-full" @click="$dispatch('uh:open-enquire')">
                            <x-icon name="phone" class="size-4" />
                            {{ __('Get a callback') }}
                        </button>
                        @if($available->isNotEmpty())
                            <a class="uh-btn-secondary w-full" href="#homes">
                                <x-icon name="home" class="size-4" />
                                {{ __('See available homes') }}
                            </a>
                        @endif
                        @if($whatsapp)
                            <div class="uh-pd-summary-direct">
                                <a href="{{ $whatsapp }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-project_id="{{ $project->id }}" data-track-location="project_summary">
                                    <x-icon name="whatsapp" class="size-4" />
                                    {{ __('WhatsApp') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </aside>

                <div class="uh-pd-body">
                    <nav class="uh-pd-tabs" x-data="uhSectionTabs" aria-label="{{ __('Project details') }}">
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

                    @if(filled($project->description))
                        <section id="about" class="uh-pd-card" aria-labelledby="about-heading">
                            <h2 id="about-heading" class="uh-pd-card-title">{{ __('About this project') }}</h2>
                            <div class="uh-prose uh-pd-prose">{!! nl2br(e($project->description)) !!}</div>
                        </section>
                    @endif

                    @if($highlights !== [])
                        <section id="highlights" class="uh-pd-card" aria-labelledby="highlights-heading">
                            <h2 id="highlights-heading" class="uh-pd-card-title">{{ __('Project highlights') }}</h2>
                            <ul class="uh-check-list mt-5">
                                @foreach($highlights as $highlight)
                                    <li>
                                        <x-icon name="check-circle" class="size-5" />
                                        {{ is_array($highlight) ? implode(' — ', $highlight) : $highlight }}
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if($amenities->isNotEmpty())
                        <section id="facilities" class="uh-pd-card" aria-labelledby="facilities-heading">
                            <h2 id="facilities-heading" class="uh-pd-card-title">{{ __('Facilities in the development') }}</h2>
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

                    @if(filled($project->handover_info))
                        <section id="handover" class="uh-pd-card" aria-labelledby="handover-heading">
                            <h2 id="handover-heading" class="uh-pd-card-title">{{ __('Handover') }}</h2>
                            <p class="uh-prose uh-pd-prose">{{ $project->handover_info }}</p>
                        </section>
                    @endif

                    @if($hasMap)
                        <section id="location" class="uh-pd-card" aria-labelledby="location-heading">
                            <h2 id="location-heading" class="uh-pd-card-title">{{ __('Location') }}</h2>
                            @if($place)
                                <p class="uh-pd-card-sub">{{ $place }}</p>
                            @endif
                            <div class="uh-pd-media mt-5">
                                <div data-uh-map class="h-80 w-full sm:h-[24rem]"
                                     data-lat="{{ round((float) $project->lat, $decimals) }}"
                                     data-lng="{{ round((float) $project->lng, $decimals) }}"
                                     data-zoom="14" data-radius="700"
                                     data-tiles="{{ config('urbanhaven.maps.tile_url') }}"
                                     data-attribution="{{ e(config('urbanhaven.maps.attribution')) }}"></div>
                            </div>
                            <p class="uh-map-note">{{ __('The circle shows the neighbourhood. Our desk shares the site address for viewings.') }}</p>
                        </section>
                    @endif
                </div>

                <aside id="contact" class="uh-pd-contact" x-data="{ tab: 'enquire' }"
                       @uh:open-visit.window="tab = 'visit'; {{ $scrollToContact }}"
                       @uh:open-enquire.window="tab = 'enquire'; {{ $scrollToContact }}; {{ $focusName }}">
                    <div class="uh-convert uh-surface" id="enquire">
                        <div class="uh-pd-agent">
                            <span class="uh-pd-agent-avatar" aria-hidden="true">{{ $contactInitials }}</span>
                            <span class="min-w-0">
                                <span class="uh-pd-agent-name">{{ $contactName }}</span>
                                <span class="uh-pd-agent-meta">
                                    <x-icon name="shield" class="size-3.5" />
                                    {{ __('Sold directly by Urban Haven') }}
                                </span>
                            </span>
                        </div>

                        <h2 class="uh-convert-title">{{ __('Ask about :name', ['name' => $project->name]) }}</h2>
                        <p class="uh-convert-who">
                            {{ __('Tell us what you are looking for and we will send the unit plans and current pricing.') }}
                            @if($officeHours)
                                {{ __('Available :hours', ['hours' => $officeHours]) }}
                            @endif
                        </p>

                        @if($whatsapp)
                            <div class="uh-convert-direct">
                                <a class="uh-btn-secondary uh-btn-sm" href="{{ $whatsapp }}" rel="noopener" target="_blank"
                                   data-track="whatsapp_click" data-track-project_id="{{ $project->id }}" data-track-location="project_aside">
                                    <x-icon name="whatsapp" class="size-4" />
                                    {{ __('WhatsApp') }}
                                </a>
                            </div>
                        @endif

                        <div class="uh-convert-body">
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
                                @include('public.partials.lead-form', [
                                    'formType' => 'inquiry',
                                    'prefix' => 'proj',
                                    'leadType' => 'property_inquiry',
                                    'source' => 'project_page',
                                    'projectId' => $project->id,
                                    'submitLabel' => __('Send my questions'),
                                    'messageLabel' => __('What are you looking for?'),
                                    'messagePlaceholder' => __('Number of bedrooms, budget, timeline…'),
                                ])
                            </div>
                            <div class="mt-6" id="panel-visit" role="tabpanel" aria-labelledby="tab-visit" x-show="tab === 'visit'" x-cloak>
                                @include('public.partials.lead-form', [
                                    'formType' => 'visit',
                                    'prefix' => 'proj-visit',
                                    'source' => 'project_page',
                                    'projectId' => $project->id,
                                ])
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <section id="homes" class="uh-pd-similar scroll-mt-20" aria-labelledby="homes-title">
            <div class="uh-container uh-section-tight">
                <header class="uh-section-head">
                    <h2 id="homes-title" class="uh-h2">{{ __('Homes in this project') }}</h2>
                    @if($available->isNotEmpty())
                        <p class="uh-lede">{{ __('Each home below is listed and sold directly by Urban Haven.') }}</p>
                    @endif
                </header>

                @if($available->isNotEmpty())
                    <div class="uh-grid-cards">
                        @foreach($available as $property)
                            @include('public.partials.property-card', ['property' => $property, 'revealIndex' => $loop->index % 3])
                        @endforeach
                    </div>
                @else
                    <x-ui.empty icon="home" :title="__('No homes are listed right now')"
                                :description="__('Every home in this project is currently reserved or sold. Ask our desk to be told first when one becomes available.')">
                        <button type="button" class="uh-btn-primary uh-btn-sm" @click="$dispatch('uh:open-enquire')">{{ __('Tell me when one is available') }}</button>
                    </x-ui.empty>
                @endif
            </div>
        </section>

        <div class="uh-dock lg:hidden">
            @if($whatsapp)
                <a class="uh-btn-secondary flex-1" href="{{ $whatsapp }}" rel="noopener" target="_blank"
                   data-track="whatsapp_click" data-track-project_id="{{ $project->id }}" data-track-location="project_mobile_bar">
                    {{ __('WhatsApp') }}
                </a>
            @endif
            <button type="button" class="uh-btn-secondary flex-1" @click="$dispatch('uh:open-enquire')">{{ __('Ask') }}</button>
            <button type="button" class="uh-btn-primary flex-1" @click="$dispatch('uh:open-visit')">{{ __('Book a visit') }}</button>
        </div>

        @if($photos->isNotEmpty())
            <div x-show="lightbox" x-cloak x-transition.opacity.duration.400ms class="uh-lightbox"
                 role="dialog" aria-modal="true" aria-label="{{ __('Project gallery') }}"
                 @keydown.escape.window="lightbox && close()" @keydown.left.window="lightbox && previous()" @keydown.right.window="lightbox && next()">
                <div class="uh-lightbox-bar">
                    <p class="uh-numeric">
                        <span x-text="active + 1">1</span>
                        <span class="opacity-50"> / {{ $photos->count() }}</span>
                    </p>
                    <p class="hidden truncate px-6 sm:block">{{ $project->name }}</p>
                    <button type="button" x-ref="closeLightbox" class="uh-lightbox-close"
                            @click="close()" aria-label="{{ __('Close gallery') }}">
                        <x-icon name="close" class="size-5" />
                    </button>
                </div>
                <div class="uh-lightbox-stage">
                    @foreach($photos as $index => $image)
                        <img x-show="active === {{ $index }}" x-transition.opacity.duration.500ms src="{{ $image->url(1920) }}"
                             alt="{{ $image->alt(app()->getLocale()) }}" loading="lazy" @if($index) x-cloak @endif>
                    @endforeach
                    @if($photos->count() > 1)
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
