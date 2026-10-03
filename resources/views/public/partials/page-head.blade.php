@php
    $wide = $wide ?? true;
    $crumbs = $crumbs ?? [];
    $lede = $lede ?? null;
@endphp

<div class="uh-page-head">
    <div class="{{ $wide ? 'uh-container' : 'uh-container-narrow' }}">
        <x-ui.breadcrumbs :items="$crumbs" />
        <h1 class="uh-home-title">{{ $title }}</h1>
        @if(filled($lede))
            <p class="uh-home-lede">{{ $lede }}</p>
        @endif
    </div>
</div>
