@props(['title', 'description' => null, 'compact' => false])

<div {{ $attributes->class('uh-pagehead flex flex-wrap items-end justify-between gap-x-6 gap-y-3') }}>
    <div class="uh-pagehead-copy min-w-0">
        @if(! empty($eyebrow))
            <p class="uh-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 @class([$compact ? 'uh-h2' : 'uh-h1', 'mt-1' => ! empty($eyebrow)])>{{ $title }}</h1>
        @if($description)
            <p class="uh-pagehead-desc">{{ $description }}</p>
        @endif
    </div>
    @if(! empty($actions))
        <div class="uh-pagehead-actions">{{ $actions }}</div>
    @endif
</div>
