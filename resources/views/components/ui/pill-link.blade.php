@props(['href', 'variant' => 'dark'])

<a href="{{ $href }}" {{ $attributes->class(['uh-pill-link', 'is-'.$variant]) }}>
    <span>{{ $slot }}</span>
    <span class="uh-pill-link-icon" aria-hidden="true">
        <x-icon name="arrow-up-right" class="size-3.5" />
    </span>
</a>
