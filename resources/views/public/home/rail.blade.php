@php
    $eyebrow = $eyebrow ?? null;
    $title = $title ?? '';
    $actionUrl = $actionUrl ?? null;
    $actionLabel = $actionLabel ?? __('View all');
    $kind = $kind ?? 'property';
@endphp

<section class="uh-section border-t border-line bg-paper">
    <div class="uh-container" x-data="uhRail">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                @if(filled($eyebrow))
                    <p class="uh-eyebrow">{{ $eyebrow }}</p>
                @endif
                <h2 class="uh-h2 mt-2">{{ $title }}</h2>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="uh-icon-action" @click="prev()" :disabled="!canPrev"
                        aria-label="{{ __('Previous') }}">
                    <x-icon name="chevron-left" class="size-4" />
                </button>
                <button type="button" class="uh-icon-action" @click="next()" :disabled="!canNext"
                        aria-label="{{ __('Next') }}">
                    <x-icon name="chevron-right" class="size-4" />
                </button>
                @if($actionUrl)
                    <a class="uh-link-quiet inline-flex items-center gap-1.5 text-sm" href="{{ $actionUrl }}">
                        {{ $actionLabel }}
                        <x-icon name="arrow-right" class="size-4" />
                    </a>
                @endif
            </div>
        </div>

        <div class="uh-rail mt-8" x-ref="scroller">
            @if($kind === 'project')
                @foreach($items as $project)
                    <div class="uh-rail-card">
                        @include('public.partials.project-card', ['project' => $project])
                    </div>
                @endforeach
            @else
                @foreach($items as $property)
                    <div class="uh-rail-card">
                        @include('public.partials.property-card', ['property' => $property])
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>
