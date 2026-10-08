@extends('layouts.public')

@php
    $cover = $project->featuredImage();
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
    $whatsappUrl = $project->whatsappEnquiryUrl();
    $brochures = $project->brochures();
@endphp

@section('content')
    @if($cover)
        <div class="uh-gallery-cover">
            <div class="uh-gallery-cover-media">
                <img src="{{ $cover->url(1920) }}" srcset="{{ $cover->srcset() }}" sizes="100vw"
                     alt="{{ $cover->alt(app()->getLocale()) }}" fetchpriority="high">
            </div>
        </div>
    @endif

    <article class="uh-container">
        <div class="uh-identity-wrap">
            <nav aria-label="{{ __('Breadcrumb') }}" class="text-sm text-muted">
                <a href="{{ route('home') }}" class="hover:text-ink">{{ __('Home') }}</a>
                <span aria-hidden="true"> / </span>
                <a href="{{ route('projects.index') }}" class="hover:text-ink">{{ __('Projects') }}</a>
                <span aria-hidden="true"> / </span>
                <span aria-current="page">{{ $project->name }}</span>
            </nav>

            <header class="uh-identity">
                <div>
                    <p class="uh-identity-kicker">{{ $stage }}@if($project->property_category) · {{ \App\Models\PropertyType::categoryLabels()[$project->property_category] ?? ucfirst($project->property_category) }}@endif @if($project->developer_name) · {{ $project->developer_name }}@endif</p>
                    <h1 class="uh-identity-title">{{ $project->name }}</h1>
                    @if($publicAddress)
                        <p class="uh-identity-address">{{ $publicAddress }}</p>
                    @endif
                    @if($mapUrl)
                        <a class="uh-link mt-3 inline-flex items-center gap-2" href="{{ $mapUrl }}" rel="noopener" target="_blank">
                            <x-icon name="pin" class="size-4" /> {{ $coordinates['approximate'] ? __('View approximate location') : __('View location') }}
                        </a>
                    @endif
                </div>
                <div class="uh-identity-side">
                    @if($project->completion_date)
                        <p class="uh-property-price">{{ $project->completion_date->format('Y') }}</p>
                        <p class="uh-project-meta">{{ __('Expected completion') }} · {{ $project->completion_date->format('F Y') }}</p>
                    @endif
                    @if($whatsappUrl)
                        <a class="uh-btn-whatsapp" href="{{ $whatsappUrl }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="project_page">
                            <x-icon name="whatsapp" class="size-4" /> {{ __('Ask about this project') }}
                        </a>
                    @endif
                </div>
            </header>
            <ul class="uh-pd-quick" aria-label="{{ __('Development availability') }}">
                <li><x-icon name="check-circle" class="size-4" /><span>{{ number_format($availability['available']) }} {{ __('available') }}</span></li>
                <li><x-icon name="clock" class="size-4" /><span>{{ number_format($availability['reserved']) }} {{ __('reserved') }}</span></li>
                <li><x-icon name="home" class="size-4" /><span>{{ number_format($availability['total']) }} {{ __('total units') }}</span></li>
            </ul>
        </div>

        <div class="uh-destination uh-detail-grid">
            <div class="min-w-0">
                @if(filled($project->description))
                    <section class="uh-panel" aria-labelledby="project-overview-title">
                        <h2 id="project-overview-title" class="uh-h2">{{ __('About this development') }}</h2>
                        @php($description = (string) $project->description)
                        <div class="uh-prose mt-5">{!! strip_tags($description) === $description ? nl2br(e($description)) : \Stevebauman\Purify\Facades\Purify::clean($description) !!}</div>
                    </section>
                @endif

                @if($project->highlights)
                    <section class="uh-panel mt-5" aria-labelledby="project-highlights-title">
                        <h2 id="project-highlights-title" class="uh-h2">{{ __('Project highlights') }}</h2>
                        <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach($project->highlights as $highlight)
                                <li class="flex items-start gap-3"><x-icon name="check-circle" class="mt-0.5 size-4 shrink-0 text-forest" /> <span>{{ $highlight }}</span></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if($amenities->isNotEmpty())
                    <section class="uh-panel mt-5" aria-labelledby="project-amenities-title">
                        <h2 id="project-amenities-title" class="uh-h2">{{ __('Facilities') }}</h2>
                        <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($amenities as $amenity)
                                <li class="flex items-center gap-3">
                                    @if($amenity->iconUrl())<img src="{{ $amenity->iconUrl() }}" class="size-5 object-contain" alt="" loading="lazy">@else<x-icon name="sparkle" class="size-4 text-muted" />@endif
                                    <span>{{ $amenity->label }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if($project->handover_info || $project->trust_label)
                    <section class="uh-panel mt-5" aria-labelledby="project-details-title">
                        <h2 id="project-details-title" class="uh-h2">{{ __('Project details') }}</h2>
                        @if($project->trust_label)<p class="mt-4 font-semibold">{{ $project->trust_label }}</p>@endif
                        @if($project->handover_info)
                            @php($handoverInfo = (string) $project->handover_info)
                            <div class="uh-prose mt-3 text-muted">{!! strip_tags($handoverInfo) === $handoverInfo ? nl2br(e($handoverInfo)) : \Stevebauman\Purify\Facades\Purify::clean($handoverInfo) !!}</div>
                        @endif
                    </section>
                @endif

                @if($brochures->isNotEmpty())
                    <section class="uh-panel mt-5" aria-labelledby="project-brochures-title">
                        <h2 id="project-brochures-title" class="uh-h2">{{ __('Project brochures') }}</h2>
                        <ul class="mt-4 grid gap-3">
                            @foreach($brochures as $brochure)
                                <li><a class="uh-link" href="{{ route('media.download', $brochure) }}">{{ $brochure->alt(app()->getLocale()) ?: __('Download brochure') }}</a></li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>

            <aside class="min-w-0">
                <div class="uh-pd-card uh-surface">
                    <h2 class="uh-h3">{{ __('Ask about this development') }}</h2>
                    <p class="mt-2 text-sm text-muted">{{ __('Our team can share availability and arrange a conversation.') }}</p>
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
                </div>
            </aside>
        </div>

        @if($videoEmbed)
            <section class="uh-section-tight" aria-labelledby="project-video-title">
                <h2 id="project-video-title" class="uh-h2">{{ __('Project video') }}</h2>
                <div class="mt-5 aspect-video overflow-hidden rounded-3xl bg-night">
                    <iframe class="size-full" src="{{ $videoEmbed }}" title="{{ $project->name }}" loading="lazy" allowfullscreen></iframe>
                </div>
            </section>
        @endif

        @if($publicProperties->isNotEmpty())
            <section class="uh-section-tight" aria-labelledby="project-listings-title">
                <h2 id="project-listings-title" class="uh-h2">{{ __('Available properties in this project') }}</h2>
                <div class="mt-6 uh-trending-grid" role="list">
                    @foreach($publicProperties as $property)
                        <div role="listitem">@include('public.partials.property-card', ['property' => $property, 'showContact' => true, 'showDetails' => true])</div>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection
