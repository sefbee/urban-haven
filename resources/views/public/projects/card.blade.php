@php
    $cover = $project->featuredImage();
    $place = collect([$project->locationArea?->name, $project->city])->filter()->unique()->implode(', ');
    $stage = \App\Models\Project::STAGE_LABELS[$project->development_stage] ?? ucfirst($project->development_stage);
    $description = filled($project->description) ? \Illuminate\Support\Str::limit(strip_tags($project->description), 180) : null;
    $isHomeProjectCard = $isHomeProjectCard ?? false;
@endphp

<article @class(['uh-project', 'uh-home-project-card' => $isHomeProjectCard, 'is-feature' => $project->is_featured])>
    <a class="uh-project-frame" href="{{ route('projects.show', $project->slug) }}" tabindex="-1" aria-hidden="true">
        @if($cover)
            <img src="{{ $cover->url(768) }}" srcset="{{ $cover->srcset() }}"
                 sizes="{{ $isHomeProjectCard ? '(min-width: 1280px) 25vw, (min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw' : '(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw' }}"
                 alt="{{ $cover->alt(app()->getLocale()) }}" loading="lazy" decoding="async">
        @endif
        @if($isHomeProjectCard)
            <span @class(['uh-project-status', 'is-completed' => $project->development_stage === 'completed', 'is-ongoing' => $project->development_stage === 'ongoing', 'is-upcoming' => $project->development_stage === 'upcoming'])>
                {{ $stage }}
            </span>
            @if($place)
                <span class="uh-project-location"><x-icon name="pin" class="size-4" />{{ $place }}</span>
            @endif
        @endif
    </a>
    <div class="uh-project-body">
        @if($isHomeProjectCard)
            <p class="sr-only">{{ $stage }}@if($place) · {{ $place }}@endif</p>
        @endif
        @unless($isHomeProjectCard)
            <p class="uh-project-meta">{{ $stage }}@if($place) · {{ $place }}@endif</p>
        @endunless
        <h3 class="uh-project-name"><a href="{{ route('projects.show', $project->slug) }}">{{ $project->name }}</a></h3>
        @if($isHomeProjectCard && $description)
            <p class="uh-project-summary">{{ $description }}</p>
        @endif
        <x-ui.pill-link class="uh-project-cta" :mobile-label="$isHomeProjectCard" :href="route('projects.show', $project->slug)">{{ __('Explore project') }}</x-ui.pill-link>
    </div>
</article>
