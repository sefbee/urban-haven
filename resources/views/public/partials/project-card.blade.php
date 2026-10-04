@php
    $image = $project->featuredImage();
    $unitCount = $project->properties_count ?? null;
    $starting = $project->starting_price ?? null;
    $compactStart = $starting ? \App\Support\MoneyFormatter::compactBdt($starting) : null;
    $startingLabel = $compactStart
        ? 'BDT '.$compactStart
        : ($starting ? \App\Support\MoneyFormatter::formatBdt($starting) : null);
    $url = route('projects.show', $project->slug);
    $feature = $feature ?? false;
    $stage = match ($project->development_stage) {
        'completed' => __('Completed'),
        'ongoing' => __('Under Construction'),
        'upcoming' => __('Upcoming'),
        default => $project->development_stage,
    };
    $place = collect([$project->locationArea?->name, $project->city])->filter()->implode(', ');
    $summary = \Illuminate\Support\Str::limit(trim(strip_tags((string) $project->description)), $feature ? 220 : 120);
    $facts = array_values(array_filter([
        $startingLabel ? __('From :price', ['price' => $startingLabel]) : null,
        $unitCount ? trans_choice(':count home listed|:count homes listed', $unitCount, ['count' => $unitCount]) : null,
    ]));
@endphp

<article @class(['uh-project', 'is-feature' => $feature])>
    <div class="uh-project-frame">
        @if($image)
            <img src="{{ $image->url($feature ? 1920 : 768) }}"
                 srcset="{{ $image->url(480) }} 480w, {{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w, {{ $image->url(1920) }} 1920w"
                 sizes="{{ $feature ? '(min-width: 1280px) 1200px, 100vw' : '(min-width: 1024px) 380px, (min-width: 768px) 50vw, 100vw' }}"
                 alt="" loading="lazy" decoding="async">
        @else
            <span class="uh-media-placeholder">{{ $project->name }}</span>
        @endif
    </div>

    <div class="uh-project-body">
        <p class="uh-project-meta">{{ $stage }}@if($place) · {{ $place }}@endif</p>
        <h3 class="uh-project-name">
            <a href="{{ $url }}">{{ $project->name }}</a>
        </h3>
        @if($summary !== '')
            <p class="uh-project-summary">{{ $summary }}</p>
        @endif
        @if($facts)
            <p class="uh-project-facts uh-numeric">{{ implode(' · ', $facts) }}</p>
        @endif
        <span class="uh-arrow-link uh-project-cta" aria-hidden="true">
            {{ __('Explore the project') }}
            <x-icon name="arrow-right" class="size-3.5" />
        </span>
    </div>
</article>
