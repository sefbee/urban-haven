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

<article class="uh-home-project">
    <a href="{{ $url }}" class="uh-home-project-photo" tabindex="-1" aria-hidden="true">
        @if($image)
            <img src="{{ $image->url(1280) }}"
                 srcset="{{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w"
                 sizes="(min-width: 768px) 55vw, 100vw"
                 alt="" loading="lazy" decoding="async">
        @endif
    </a>
    <div>
        <p class="uh-home-kicker">{{ $stage }}</p>
        <h3 class="mt-2 text-xl font-semibold tracking-tight">
            <a class="uh-home-link" href="{{ $url }}">{{ $project->name }}</a>
        </h3>
        <p class="mt-3 text-[var(--color-muted)]">
            {{ $project->locationArea?->name }}@if($project->city), {{ $project->city }}@endif
        </p>
        @if($startingLabel)
            <p class="mt-4 text-lg font-semibold tracking-tight">{{ __('From :price', ['price' => $startingLabel]) }}</p>
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
            'detailsLabel' => __('Explore the project'),
            'detailsClass' => 'uh-home-link text-sm',
            'dividerClass' => 'border-black/8',
        ])
    </div>
</article>
