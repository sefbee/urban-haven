@extends('layouts.public')

@php
    $media = $project->galleryImages();
    $cover = $project->featuredImage();
    if ($cover) {
        $media = $media->sortBy(fn ($image) => $image->is($cover) ? 0 : 1)->values();
    }
    $brochures = $project->brochures();
    $thumbs = $media->slice(1, 2)->values();
    $hiddenPhotos = max(0, $media->count() - 1 - $thumbs->count());
    $place = collect([$project->locationArea?->name, $project->city, $project->locationArea?->country])->filter()->unique()->implode(', ');
    $addressMode = (string) \App\Models\Setting::get('address_display_mode', 'approximate');
    $publicAddress = match ($addressMode) {
        'exact' => $project->address ?: $place,
        'hidden' => null,
        default => $place,
    };
    $coordinates = $project->publicCoordinates();
    $mapUrl = $coordinates ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($coordinates['lat'].','.$coordinates['lng']) : null;
    $videoEmbed = \App\Support\VideoEmbed::embedUrl($project->video_url);
    $stage = \App\Models\Project::STAGE_LABELS[$project->development_stage] ?? ucfirst($project->development_stage);
    $category = $project->property_category
        ? (\App\Models\PropertyType::categoryLabels()[$project->property_category] ?? ucfirst($project->property_category))
        : null;
    $whatsappUrl = $project->whatsappEnquiryUrl();
    $shareUrl = route('projects.show', $project->slug);
    $availabilityFacts = [
        ['icon' => 'check-circle', 'label' => __('Available homes'), 'value' => number_format($availability['available'])],
        ['icon' => 'clock', 'label' => __('Reserved homes'), 'value' => number_format($availability['reserved'])],
        ['icon' => 'home', 'label' => __('Homes in development'), 'value' => number_format($availability['total'])],
    ];
    $projectFacts = array_values(array_filter([
        ['icon' => 'building', 'label' => __('Development type'), 'value' => $category],
        ['icon' => 'compass', 'label' => __('Development stage'), 'value' => $stage],
        ['icon' => 'user', 'label' => __('Developer'), 'value' => $project->developer_name],
        ['icon' => 'pin', 'label' => __('Location'), 'value' => $place],
        $project->completion_date ? ['icon' => 'calendar', 'label' => __('Expected completion'), 'value' => $project->completion_date->format('F Y')] : null,
        ['icon' => 'home', 'label' => __('Homes in development'), 'value' => number_format($availability['total'])],
    ], fn ($fact) => $fact !== null && filled($fact['value'])));
    $hasDescription = filled($project->description);
    $tabs = array_filter([
        'overview' => __('Overview'),
        'about' => $hasDescription ? __('About') : null,
        'highlights' => $project->highlights ? __('Highlights') : null,
        'facilities' => $amenities->isNotEmpty() ? __('Facilities') : null,
        'details' => ($project->handover_info || $project->trust_label) ? __('Details') : null,
        'homes' => $publicProperties->isNotEmpty() ? __('Homes') : null,
        'brochures' => $brochures->isNotEmpty() ? __('Brochures') : null,
        'contact' => __('Enquire'),
    ]);
@endphp

@section('content')
    <article class="uh-pd pb-24 lg:pb-0" x-data="uhGallery({{ $media->count() }})">
        <div class="uh-container">

            <header id="identity" class="uh-pd-head">
                <div class="min-w-0">
                    <div class="uh-pd-tags" aria-label="{{ __('Project identity') }}">
                        <span class="uh-pd-tag is-dark"><x-icon name="building" class="size-4" />{{ __('Development') }}</span>
                        <span class="uh-pd-tag">{{ $stage }}</span>
                        @if($category)<span class="uh-pd-tag">{{ $category }}</span>@endif
                        @if($project->developer_name)<span class="uh-pd-tag">{{ $project->developer_name }}</span>@endif
                    </div>
                    <h1 id="project-title" class="uh-pd-title mt-3">{{ $project->name }}</h1>
                    @if($publicAddress)
                        <p class="uh-pd-address">
                            <x-icon name="pin" class="size-4" />
                            {{ $publicAddress }}
                        </p>
                    @endif
                    @if($mapUrl)
                        <a class="uh-link mt-3 inline-flex items-center gap-2" href="{{ $mapUrl }}" rel="noopener" target="_blank">
                            <x-icon name="pin" class="size-4" /> {{ $coordinates['approximate'] ? __('View approximate location') : __('View location') }}
                        </a>
                    @endif
                    <ul class="uh-pd-quick" aria-label="{{ __('Development availability') }}">
                        <li><x-icon name="check-circle" class="size-4" /><span>{{ number_format($availability['available']) }} {{ __('available') }}</span></li>
                        <li><x-icon name="clock" class="size-4" /><span>{{ number_format($availability['reserved']) }} {{ __('reserved') }}</span></li>
                        <li><x-icon name="home" class="size-4" /><span>{{ number_format($availability['total']) }} {{ __('total homes') }}</span></li>
                    </ul>
                </div>

                <div class="uh-pd-head-side">
                    @if($project->completion_date)
                        <p class="uh-pd-price uh-numeric">{{ $project->completion_date->format('Y') }}</p>
                        <p class="uh-pd-updated">{{ __('Expected completion') }} · {{ $project->completion_date->format('F Y') }}</p>
                    @else
                        <p class="uh-pd-price">{{ __('New development') }}</p>
                        <p class="uh-pd-updated">{{ $stage }}</p>
                    @endif
                    <div class="uh-pd-actions">
                        <x-share-menu :url="$shareUrl" :title="$project->name" />
                    </div>
                    <div class="uh-pd-head-highlights">
                        <span class="uh-listing-row-badge is-type">{{ __('Project') }}</span>
                        @if($project->trust_label)<span class="uh-listing-row-badge is-featured">{{ $project->trust_label }}</span>@endif
                    </div>
                </div>
            </header>

            <div class="uh-pd-layout">
                <x-media-stage class="uh-pd-stage" :title="$project->name" :photo-count="$media->count()"
                               :video="$videoEmbed" :coordinates="$coordinates">
                    <div class="uh-pd-gallery-wrap">
                        <section @class(['uh-pd-gallery', 'has-thumbs' => $thumbs->isNotEmpty(), 'has-two' => $thumbs->count() === 2]) aria-label="{{ __('Project photographs') }}">
                            @if($media->isNotEmpty())
                                <button type="button" class="uh-pd-shot is-main"
                                        style="view-transition-name: uh-project-{{ $project->id }}"
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
                            @else
                                <div class="uh-pd-shot is-main is-empty">
                                    <x-icon name="building" class="size-8" />
                                    <span>{{ __('Development photographs coming soon') }}</span>
                                </div>
                            @endif
                        </section>
                        @if($media->count() > 1)
                            <button type="button" class="uh-pd-gallery-all" @click="open(0)">
                                <x-icon name="grid" class="size-4" />
                                {{ __('View all :count photos', ['count' => $media->count()]) }}
                            </button>
                        @endif
                    </div>
                </x-media-stage>

                <div class="uh-pd-body">
                    <nav class="uh-pd-tabs" x-data="uhSectionTabs" aria-label="{{ __('Project details') }}">
                        <div class="uh-pd-tabs-strip">
                            @foreach($tabs as $anchor => $label)
                                <a href="#{{ $anchor }}" :class="{ 'is-active': current === '{{ $anchor }}' }" @if($loop->first) class="is-active" @endif>{{ $label }}</a>
                            @endforeach
                        </div>
                    </nav>

                    <section id="overview" class="uh-pd-card" aria-labelledby="overview-heading">
                        <h2 id="overview-heading" class="uh-pd-card-title">{{ __('Development overview') }}</h2>
                        <ul class="uh-pd-overview">
                            @foreach($projectFacts as $fact)
                                <li>
                                    <span class="uh-pd-overview-icon"><x-icon :name="$fact['icon']" class="size-5" /></span>
                                    <span class="uh-pd-overview-label">{{ $fact['label'] }}</span>
                                    <span class="uh-pd-overview-value">{{ $fact['value'] }}</span>
                                </li>
                            @endforeach
                            @foreach($availabilityFacts as $fact)
                                <li>
                                    <span class="uh-pd-overview-icon"><x-icon :name="$fact['icon']" class="size-5" /></span>
                                    <span class="uh-pd-overview-label">{{ $fact['label'] }}</span>
                                    <span class="uh-pd-overview-value uh-numeric">{{ $fact['value'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>

                    @if($hasDescription)
                        <section id="about" class="uh-pd-card" aria-labelledby="about-heading">
                            <h2 id="about-heading" class="uh-pd-card-title">{{ __('About this development') }}</h2>
                            @php($description = (string) $project->description)
                            <div class="uh-prose uh-pd-prose">{!! strip_tags($description) === $description ? nl2br(e($description)) : \Stevebauman\Purify\Facades\Purify::clean($description) !!}</div>
                        </section>
                    @endif

                    @if($project->highlights)
                        <section id="highlights" class="uh-pd-card" aria-labelledby="highlights-heading">
                            <h2 id="highlights-heading" class="uh-pd-card-title">{{ __('Project highlights') }}</h2>
                            <ul class="uh-pd-highlights" aria-label="{{ __('Development highlights') }}">
                                @foreach($project->highlights as $highlight)
                                    <li><x-icon name="check-circle" class="size-5" /><span><strong>{{ $highlight }}</strong></span></li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if($amenities->isNotEmpty())
                        <section id="facilities" class="uh-pd-card" aria-labelledby="facilities-heading">
                            <h2 id="facilities-heading" class="uh-pd-card-title">{{ __('Facilities') }}</h2>
                            <ul class="uh-pd-amenities">
                                @foreach($amenities as $amenity)
                                    <li>
                                        @if($amenity->iconUrl())
                                            <img src="{{ $amenity->iconUrl() }}" alt="" loading="lazy" decoding="async">
                                        @else
                                            <x-icon name="sparkle" class="size-6" />
                                        @endif
                                        <span>{{ $amenity->label }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if($project->handover_info || $project->trust_label)
                        <section id="details" class="uh-pd-card" aria-labelledby="details-heading">
                            <h2 id="details-heading" class="uh-pd-card-title">{{ __('Project details') }}</h2>
                            @if($project->trust_label)<p class="uh-pd-card-lede">{{ $project->trust_label }}</p>@endif
                            @if($project->handover_info)
                                @php($handoverInfo = (string) $project->handover_info)
                                <div class="uh-prose uh-pd-prose">{!! strip_tags($handoverInfo) === $handoverInfo ? nl2br(e($handoverInfo)) : \Stevebauman\Purify\Facades\Purify::clean($handoverInfo) !!}</div>
                            @endif
                        </section>
                    @endif

                    @if($brochures->isNotEmpty())
                        <section id="brochures" class="uh-pd-card" aria-labelledby="brochures-heading">
                            <h2 id="brochures-heading" class="uh-pd-card-title">{{ __('Project brochures') }}</h2>
                            <ul class="uh-pd-files">
                                @foreach($brochures as $brochure)
                                    <li>
                                        <a href="{{ route('media.download', $brochure) }}" data-track="brochure_download">
                                            <x-icon name="document" class="size-5" />
                                            <span class="min-w-0 flex-1">{{ $brochure->alt(app()->getLocale()) ?: __('Download project brochure (PDF)') }}</span>
                                            @if($brochure->size_bytes)<span class="uh-numeric text-[var(--uh-faint)]">{{ number_format($brochure->size_bytes / 1048576, 1) }} MB</span>@endif
                                            <x-icon name="download" class="size-4" />
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if($publicProperties->isNotEmpty())
                        <section id="homes" class="uh-pd-card" aria-labelledby="homes-heading">
                            <h2 id="homes-heading" class="uh-pd-card-title">{{ __('Available homes in this project') }}</h2>
                            <div class="mt-5 grid gap-4">
                                @foreach($publicProperties as $property)
                                    @include('public.partials.property-card', ['property' => $property, 'showContact' => true, 'showDetails' => true])
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section id="contact" class="uh-pd-card uh-pd-contact-info" aria-labelledby="contact-heading">
                        <h2 id="contact-heading" class="uh-pd-card-title">{{ __('Ask about this development') }}</h2>
                        <p class="uh-pd-card-lede">{{ __('Our team can share availability and arrange a conversation.') }}</p>
                        <div class="mt-5">
                            @include('public.partials.lead-form', [
                                'leadType' => 'general_contact',
                                'source' => 'project_page',
                                'prefix' => 'project',
                                'projectId' => $project->id,
                                'messageLabel' => __('What would you like to know?'),
                                'submitLabel' => __('Send enquiry'),
                                'compact' => true,
                            ])
                        </div>
                    </section>
                </div>

                <aside class="uh-pd-contact" aria-label="{{ __('Ask about this project') }}"
                       @uh:open-enquire.window="$el.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' })">
                    <div class="uh-convert uh-surface">
                        <div class="uh-pd-agent">
                            <span class="uh-pd-agent-avatar" aria-hidden="true"><x-icon name="building" class="size-5" /></span>
                            <span class="min-w-0">
                                <span class="uh-pd-agent-name">{{ $project->developer_name ?: __('Urban Haven project team') }}</span>
                                <span class="uh-pd-agent-meta"><x-icon name="shield" class="size-3.5" />{{ __('Project information from Urban Haven') }}</span>
                            </span>
                        </div>
                        <h2 class="uh-convert-title">{{ __('Ask about this project') }}</h2>
                        <p class="uh-convert-who">{{ __('Our team can share availability and arrange a conversation.') }}</p>
                        <div class="uh-convert-body mt-4">
                            @include('public.partials.lead-form', [
                                'leadType' => 'general_contact',
                                'source' => 'project_page',
                                'prefix' => 'project-sidebar',
                                'projectId' => $project->id,
                                'messageLabel' => __('What would you like to know?'),
                                'submitLabel' => __('Ask about this project'),
                                'compact' => true,
                            ])
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        @if($media->isNotEmpty())
            <div x-show="lightbox" x-cloak x-transition.opacity.duration.400ms class="uh-lightbox"
                 tabindex="-1" role="dialog" aria-modal="true" aria-label="{{ __('Project gallery') }}"
                 @keydown.escape.window="lightbox && close()" @keydown.left.window="lightbox && previous()" @keydown.right.window="lightbox && next()"
                 @keydown.tab.prevent="lightbox && $trapFocus($el, $event)">
                <div class="uh-lightbox-bar">
                    <p class="uh-numeric"><span x-text="active + 1">1</span><span class="opacity-50"> / {{ $media->count() }}</span></p>
                    <p class="hidden truncate px-6 sm:block">{{ $project->name }}</p>
                    <button type="button" x-ref="closeLightbox" class="uh-lightbox-close" @click="close()" aria-label="{{ __('Close gallery') }}">
                        <x-icon name="close" class="size-5" />
                    </button>
                </div>
                <div class="uh-lightbox-stage">
                    @foreach($media as $index => $image)
                        <img x-show="active === {{ $index }}" x-transition.opacity.duration.500ms src="{{ $image->url(1920) }}"
                             alt="{{ $image->alt(app()->getLocale()) }}" loading="lazy" @if($index) x-cloak @endif>
                    @endforeach
                    @if($media->count() > 1)
                        <button type="button" @click="previous()" class="uh-lightbox-nav left-2 sm:left-5" aria-label="{{ __('Previous image') }}"><x-icon name="chevron-left" class="size-5" /></button>
                        <button type="button" @click="next()" class="uh-lightbox-nav right-2 sm:right-5" aria-label="{{ __('Next image') }}"><x-icon name="chevron-right" class="size-5" /></button>
                    @endif
                </div>
            </div>
        @endif

        <div class="uh-dock lg:hidden">
            @if($whatsappUrl)
                <a class="uh-btn-secondary flex-1" href="{{ $whatsappUrl }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="project_mobile_bar">
                    <x-icon name="whatsapp" class="size-4" />{{ __('WhatsApp') }}
                </a>
            @endif
            <button type="button" class="uh-btn-primary flex-1" x-data @click="$dispatch('uh:open-enquire')">{{ __('Ask about this project') }}</button>
        </div>
    </article>
@endsection
