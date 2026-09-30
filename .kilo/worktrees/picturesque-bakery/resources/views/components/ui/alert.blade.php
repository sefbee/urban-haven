@props(['tone' => 'info', 'icon' => null, 'dismissible' => false])

@php
    $tones = [
        'success' => ['class' => 'uh-alert-success', 'icon' => 'check-circle'],
        'danger' => ['class' => 'uh-alert-danger', 'icon' => 'alert'],
        'warn' => ['class' => 'uh-alert-warn', 'icon' => 'alert'],
        'info' => ['class' => 'uh-alert-info', 'icon' => 'info'],
    ];
    $resolved = $tones[$tone] ?? $tones['info'];
@endphp

<div {{ $attributes->class(['uh-alert', $resolved['class']]) }}
     role="{{ $tone === 'danger' ? 'alert' : 'status' }}"
     @if($dismissible) x-data="{ shown: true }" x-show="shown" x-cloak @endif>
    <x-icon :name="$icon ?? $resolved['icon']" class="mt-0.5 size-4 shrink-0" />
    <div class="min-w-0 flex-1">{{ $slot }}</div>
    @if($dismissible)
        <button type="button" class="-my-1 -mr-1 shrink-0 rounded p-1 opacity-60 transition hover:opacity-100"
                @click="shown = false" aria-label="Dismiss">
            <x-icon name="close" class="size-4" />
        </button>
    @endif
</div>
