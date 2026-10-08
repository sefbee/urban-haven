<div x-data="uhPassword">
    <x-ui.input name="password" :label="$label" type="password" x-ref="password" x-bind:type="shown ? 'text' : 'password'"
                autocomplete="new-password" dir="ltr" :required="$required" :optional="! $required" :hint="$hint" />
    <div class="mt-2 flex flex-wrap items-center gap-2">
        <button type="button" class="uh-btn-ghost uh-btn-sm" @click="generate()">
            <x-icon name="key" class="size-4" />
            Generate a strong password
        </button>
        <button type="button" class="uh-btn-ghost uh-btn-sm" @click="shown = ! shown" x-text="shown ? 'Hide' : 'Show'">Show</button>
        <span class="text-xs text-[var(--color-success)]" x-show="copied" x-cloak>Copied. Share it with them privately.</span>
    </div>
</div>
