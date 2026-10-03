@props(['name', 'title'])

<div
    {{ $attributes->merge(['class' => 'uh-admin-sheet', 'data-drawer' => $name]) }}
    x-bind:class="drawer === @js($name) ? 'is-open' : ''"
    x-cloak
    role="dialog"
    aria-modal="true"
    aria-labelledby="drawer-{{ $name }}-title"
>
    <div class="uh-admin-sheet-backdrop" @click="close()"></div>

    <aside class="uh-admin-sheet-panel">
        <header class="uh-admin-sheet-head">
            <div>
                <p class="uh-admin-sheet-kicker">Form</p>
                <h2 id="drawer-{{ $name }}-title" class="uh-admin-sheet-title">{{ $title }}</h2>
            </div>
            <button type="button" class="uh-admin-icon-btn" @click="close()" aria-label="Close">
                <x-icon name="close" class="size-5" />
            </button>
        </header>
        <div class="uh-admin-sheet-body">
            {{ $slot }}
        </div>
    </aside>
</div>
