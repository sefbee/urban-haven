@php
    $cover = $project->featuredImage();
    $place = collect([$project->locationArea?->name, $project->city])->filter()->unique()->implode(', ');
    $stage = \App\Models\Project::STAGE_LABELS[$project->development_stage] ?? ucfirst($project->development_stage);
    $publicPropertiesCount = $project->properties_count ?? null;
@endphp

<article @class(['uh-project', 'is-feature' => $project->is_featured])>
    <a class="uh-project-frame" href="{{ route('projects.show', $project->slug) }}" tabindex="-1" aria-hidden="true">
        @if($cover)
            <img src="{{ $cover->url(768) }}" srcset="{{ $cover->srcset() }}"
                 sizes="(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw"
                 alt="{{ $cover->alt(app()->getLocale()) }}" loading="lazy" decoding="async">
        @endif
    </a>
    <div class="uh-project-body">
        <p class="uh-project-meta">{{ $stage }}@if($place) · {{ $place }}@endif</p>
        <h3 class="uh-project-name"><a href="{{ route('projects.show', $project->slug) }}">{{ $project->name }}</a></h3>
        @if(filled($project->description))
            <p class="uh-project-summary">{{ \Illuminate\Support\Str::limit(strip_tags($project->description), 180) }}</p>
        @endif
        @if($publicPropertiesCount !== null)
            <p class="uh-project-facts">{{ trans_choice(':count published listing|:count published listings', $publicPropertiesCount, ['count' => $publicPropertiesCount]) }}</p>
        @endif
        <a class="uh-project-cta uh-arrow-link" href="{{ route('projects.show', $project->slug) }}">
            <span>{{ __('Explore project') }}</span>
            <x-icon name="arrow-right" class="size-4" />
        </a>
    </div>
</article>
