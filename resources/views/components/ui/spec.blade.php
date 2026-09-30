@props(['label', 'icon' => null])

<div {{ $attributes->class('uh-spec') }}>
    <p class="uh-spec-label">
        @if($icon)
            <x-icon :name="$icon" class="mr-1 inline size-3.5 align-[-2px]" />
        @endif
        {{ $label }}
    </p>
    <p class="uh-spec-value">{{ $slot }}</p>
</div>
