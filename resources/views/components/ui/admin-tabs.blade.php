@props(['label' => 'Views'])

<nav {{ $attributes->class('uh-admin-tabs') }} aria-label="{{ $label }}">
    {{ $slot }}
</nav>
