@php
    $image = $project->featuredImage();
    $unitCount = $project->properties_count ?? null;
    $starting = $project->starting_price ?? null;
    $compactStart = $starting ? \App\Support\MoneyFormatter::compactBdt($starting) : null;
    $startingLabel = $compactStart
        ? 'BDT '.$compactStart
        : ($starting ? \App\Support\MoneyFormatter::formatBdt($starting) : null);
    $url = route('projects.show', $project->slug);
    $stage = match ($project->development_stage) {
        'completed' => __('Completed'),
        'ongoing' => __('Under Construction'),
        'upcoming' => __('Upcoming'),
        default => $project->development_stage,
    };
@endphp

<article class="uh-home-tile">
    <a href="{{ $url }}" class="uh-home-tile-photo" tabindex="-1" aria-hidden="true">
        @if($image)
            <img src="{{ $image->url(768) }}"
                 srcset="{{ $image->url(480) }} 480w, {{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w"
                 sizes="(min-width: 768px) 380px, 100vw"
                 alt="" loading="lazy" decoding="async">
        @endif
        <span class="absolute left-3 top-3">
            @php
                $stageBadge = match ($project->development_stage) {
                    'completed' => ['tone' => 'success', 'label' => __('Completed')],
                    'ongoing' => ['tone' => 'warn', 'label' => __('Under Construction')],
                    'upcoming' => ['tone' => 'info', 'label' => __('Upcoming')],
                    default => null,
                };
            @endphp
            @if($stageBadge)
                <x-ui.badge :tone="$stageBadge['tone']">{{ $stageBadge['label'] }}</x-ui.badge>
            @else
                <x-ui.status :status="$project->development_stage" />
            @endif
        </span>
    </a>

    <p class="uh-home-kicker mt-4">{{ $stage }}</p>
    <h3 class="mt-1 text-lg font-semibold tracking-tight">
        <a class="uh-home-link" href="{{ $url }}">{{ $project->name }}</a>
    </h3>
    <p class="mt-1 text-sm text-[var(--color-muted)]">
        {{ $project->locationArea?->name }}@if($project->city), {{ $project->city }}@endif
    </p>
    @if($startingLabel)
        <p class="mt-3 text-base font-semibold tracking-tight">{{ __('From :price', ['price' => $startingLabel]) }}</p>
    @endif

    @if($unitCount !== null)
        <p class="mt-2 text-sm text-[var(--color-muted)]">
            <span class="uh-numeric">{{ $unitCount }}</span>
            {{ $unitCount === 1 ? __('property listed') : __('properties listed') }}
        </p>
    @elseif($project->developer_name)
        <p class="mt-2 text-sm text-[var(--color-muted)]">{{ $project->developer_name }}</p>
    @endif

    @include('public.partials.card-actions', [
        'url' => $url,
        'title' => $project->name,
        'whatsapp' => $project->whatsappEnquiryUrl(),
        'trackProjectId' => $project->id,
        'trackLocation' => 'project_card',
        'detailsLabel' => __('View Project'),
        'detailsClass' => 'uh-home-link text-sm',
        'dividerClass' => 'border-black/8',
    ])
</article>
