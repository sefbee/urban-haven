@props(['title', 'description' => null, 'compact' => false, 'icon' => null])

@php
    if ($icon === null && auth()->check() && request()->routeIs('admin.*')) {
        $icon = \App\Support\AdminNavigation::current(\App\Support\AdminNavigation::for(auth()->user()))['item']['icon'] ?? null;
    }
@endphp

<div {{ $attributes->class('uh-pagehead flex flex-wrap items-end justify-between gap-x-6 gap-y-3') }}>
    <div class="uh-pagehead-main">
        @if($icon)
            <span class="uh-pagehead-icon" aria-hidden="true"><x-icon :name="$icon" class="size-5" /></span>
        @endif
        <div class="uh-pagehead-copy min-w-0">
            @if(! empty($eyebrow))
                <p class="uh-eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1 @class([$compact ? 'uh-h2' : 'uh-h1', 'mt-1' => ! empty($eyebrow)])>{{ $title }}</h1>
            @if($description)
                <p class="uh-pagehead-desc">{{ $description }}</p>
            @endif
        </div>
    </div>
    @if(! empty($actions))
        <div class="uh-pagehead-actions">{{ $actions }}</div>
    @endif
</div>
