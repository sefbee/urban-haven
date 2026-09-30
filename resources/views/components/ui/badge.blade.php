@props(['tone' => 'neutral'])

@php
    $tones = [
        'neutral' => '',
        'success' => 'uh-badge-success',
        'warn' => 'uh-badge-warn',
        'danger' => 'uh-badge-danger',
        'info' => 'uh-badge-info',
        'gold' => 'uh-badge-gold',
        'dark' => 'uh-badge-dark',
        'outline' => 'uh-badge-outline',
    ];
@endphp

<span {{ $attributes->class(['uh-badge', $tones[$tone] ?? '']) }}>{{ $slot }}</span>
