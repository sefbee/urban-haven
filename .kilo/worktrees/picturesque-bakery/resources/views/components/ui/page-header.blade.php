@props(['title', 'description' => null, 'compact' => false])

<div {{ $attributes->class('flex flex-wrap items-end justify-between gap-4') }}>
    <div class="min-w-0">
        @if(! empty($eyebrow))
            <p class="uh-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 @class([$compact ? 'uh-h2' : 'uh-h1', 'mt-2' => ! empty($eyebrow)])>{{ $title }}</h1>
        @if($description)
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-muted)]">{{ $description }}</p>
        @endif
    </div>
    @if(! empty($actions))
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endif
</div>
