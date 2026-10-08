@php
    $wide = $wide ?? true;
    $lede = $lede ?? null;
@endphp

<header class="uh-page-head">
    <div class="{{ $wide ? 'uh-container' : 'uh-container-narrow' }}">
        <h1 class="uh-h1 uh-page-title">{{ $title }}</h1>
        @if(filled($lede))
            <p class="uh-lede">{{ $lede }}</p>
        @endif
    </div>
</header>
