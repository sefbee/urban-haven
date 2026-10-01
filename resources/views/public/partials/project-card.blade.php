@php
    $image = $project->featuredImage();
    $unitCount = $project->properties_count ?? null;
    $starting = $project->starting_price ?? null;
    $compactStart = $starting ? \App\Support\MoneyFormatter::compactBdt($starting) : null;
    $startingLabel = $compactStart
        ? 'BDT '.$compactStart
        : ($starting ? \App\Support\MoneyFormatter::formatBdt($starting) : null);
@endphp

<article class="uh-card uh-card-hover flex flex-col bg-ink text-cream ring-ink/10">
    <a href="{{ route('projects.show', $project->slug) }}" class="uh-media uh-media-zoom block aspect-16/10" tabindex="-1" aria-hidden="true">
        @if($image)
            <img src="{{ $image->url(768) }}"
                 srcset="{{ $image->url(480) }} 480w, {{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w"
                 sizes="(min-width: 768px) 380px, 100vw"
                 alt="" loading="lazy" decoding="async" class="opacity-90">
        @else
            <span class="uh-media-placeholder items-center justify-center">
                <x-icon name="building" class="size-8 opacity-40" />
            </span>
        @endif
        <span class="absolute left-3 top-3">
            @php
                $stageBadge = match ($project->development_stage) {
                    'completed' => ['tone' => 'success', 'label' => __('Ready to Move')],
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

    <div class="flex flex-1 flex-col p-5">
        <h3 class="uh-h3">
            <a class="line-clamp-2 transition hover:text-gold" href="{{ route('projects.show', $project->slug) }}">{{ $project->name }}</a>
        </h3>
        <p class="mt-2 flex items-center gap-1.5 text-xs text-cream/70">
            <x-icon name="pin" class="size-3.5 shrink-0 text-gold" />
            {{ $project->locationArea?->name }}@if($project->city), {{ $project->city }}@endif
        </p>
        @if($startingLabel)
            <p class="mt-3 text-sm font-semibold text-gold">{{ __('From :price', ['price' => $startingLabel]) }}</p>
        @endif

        <div class="mt-5 flex items-center justify-between gap-3 border-t border-white/10 pt-4 text-xs text-cream/70">
            @if($unitCount !== null)
                <span><span class="uh-numeric font-semibold text-cream">{{ $unitCount }}</span> {{ $unitCount === 1 ? __('home available') : __('homes available') }}</span>
            @else
                <span>{{ $project->developer_name }}</span>
            @endif
            <a class="inline-flex items-center gap-1.5 font-semibold text-gold" href="{{ route('projects.show', $project->slug) }}">
                {{ __('View Project') }}
                <x-icon name="arrow-right" class="size-3.5" />
            </a>
        </div>
    </div>
</article>
