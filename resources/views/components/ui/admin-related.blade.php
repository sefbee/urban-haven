@props(['label' => 'Related'])

<aside {{ $attributes->class('uh-admin-related') }}>
    <p class="uh-admin-related-label">{{ $label }}</p>
    <div class="uh-admin-related-links">{{ $slot }}</div>
</aside>
