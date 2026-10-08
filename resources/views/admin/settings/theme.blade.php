@php
    $advancedGroupLabels = [
        'surfaces_text' => 'Page surfaces & text',
        'hero_slider' => 'Hero & slider',
        'mobile_nav' => 'Mobile menu & bottom nav',
    ];
@endphp

<form method="POST" action="{{ route('api.admin.settings.theme') }}"
      class="space-y-5" x-data="uhThemeEditor(@js($themeColors))" @submit.prevent="save" data-unsaved-guard>
    @csrf

    <p class="uh-alert uh-alert-danger" x-show="error" x-text="error" x-cloak role="alert"></p>
    <p class="uh-alert uh-alert-success" x-show="success" x-text="success" x-cloak role="status"></p>

    <section class="uh-panel" aria-labelledby="theme-main-heading">
        <div class="mb-4">
            <h2 id="theme-main-heading" class="uh-h4">Main brand colors</h2>
            <p class="mt-1 text-sm text-[var(--color-muted)]">These three colors set the base palette across the website.</p>
        </div>

        <div class="mb-5 grid gap-2 rounded-xl border border-[var(--color-line)] bg-[var(--color-sand)]/50 p-3 sm:grid-cols-3" aria-label="Current brand colors">
            <div class="flex min-w-0 items-center gap-2 rounded-lg bg-white/70 px-3 py-2">
                <span class="size-7 shrink-0 rounded-md border border-black/10" :style="`background:${theme.brand.dominant}`" aria-hidden="true"></span>
                <span class="min-w-0"><span class="block text-[0.65rem] font-semibold uppercase tracking-wider text-[var(--color-muted)]">Primary · Dominant</span><span class="font-mono text-xs font-semibold uppercase" x-text="theme.brand.dominant"></span></span>
            </div>
            <div class="flex min-w-0 items-center gap-2 rounded-lg bg-white/70 px-3 py-2">
                <span class="size-7 shrink-0 rounded-md border border-black/10" :style="`background:${theme.brand.secondary}`" aria-hidden="true"></span>
                <span class="min-w-0"><span class="block text-[0.65rem] font-semibold uppercase tracking-wider text-[var(--color-muted)]">Dark · Secondary</span><span class="font-mono text-xs font-semibold uppercase" x-text="theme.brand.secondary"></span></span>
            </div>
            <div class="flex min-w-0 items-center gap-2 rounded-lg bg-white/70 px-3 py-2">
                <span class="size-7 shrink-0 rounded-md border border-black/10" :style="`background:${theme.brand.accent}`" aria-hidden="true"></span>
                <span class="min-w-0"><span class="block text-[0.65rem] font-semibold uppercase tracking-wider text-[var(--color-muted)]">Accent</span><span class="font-mono text-xs font-semibold uppercase" x-text="theme.brand.accent"></span></span>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach([
                'dominant' => ['label' => 'Dominant · 60%', 'description' => 'Page canvas and large surfaces.'],
                'secondary' => ['label' => 'Secondary · 30%', 'description' => 'Dark structure and body text.'],
                'accent' => ['label' => 'Accent · 10%', 'description' => 'Actions, active states and focus.'],
            ] as $key => $field)
                <div class="uh-field">
                    <label class="uh-label" for="theme-brand-{{ $key }}">{{ $field['label'] }}</label>
                    <p class="mb-1.5 text-xs text-[var(--color-muted)]">{{ $field['description'] }}</p>
                    <div class="flex min-w-0 items-center gap-2">
                        <input type="color" class="dd-color-swatch" :value="theme.brand.{{ $key }}" @input="setBrand('{{ $key }}', $event.target.value)" aria-label="Pick {{ strtolower($field['label']) }}">
                        <input id="theme-brand-{{ $key }}" class="uh-input min-w-0 font-mono uppercase" type="text" maxlength="7" pattern="#[0-9a-fA-F]{6}" placeholder="#FFFFFF" x-model="theme.brand.{{ $key }}" @input="clearMessages()" aria-label="{{ $field['label'] }} hex value">
                    </div>
                    <p class="uh-error" x-show="fieldError('theme.brand.{{ $key }}')" x-text="fieldError('theme.brand.{{ $key }}')" x-cloak></p>
                </div>
            @endforeach
        </div>
    </section>

    @foreach(\App\Support\ThemeColors::ADVANCED_FIELDS as $group => $fields)
        <section class="overflow-hidden rounded-xl border border-[var(--color-line)] bg-white" aria-labelledby="theme-group-{{ $group }}">
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3.5">
                <button type="button" class="flex min-w-0 flex-1 items-center justify-between gap-3 text-left" @click="expanded.{{ $group }} = ! expanded.{{ $group }}" :aria-expanded="expanded.{{ $group }}.toString()" aria-controls="theme-fields-{{ $group }}">
                    <span class="min-w-0">
                        <span id="theme-group-{{ $group }}" class="block text-sm font-semibold">{{ $advancedGroupLabels[$group] }} <span class="font-normal text-[var(--color-muted)]">(advanced)</span></span>
                        <span class="mt-0.5 block text-xs text-[var(--color-muted)]">Optional overrides. Blank fields inherit the main brand colors.</span>
                    </span>
                    <x-icon name="chevron-down" class="size-4 shrink-0 transition-transform" ::class="{ 'rotate-180': expanded.{{ $group }} }" />
                </button>
                <button type="button" class="uh-btn-outline uh-btn-sm shrink-0" @click="resetGroup('{{ $group }}')">Reset group</button>
            </div>

            <div id="theme-fields-{{ $group }}" class="border-t border-[var(--color-line)] px-4 py-4" x-show="expanded.{{ $group }}" x-transition x-cloak>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($fields as $key => $field)
                        <div class="uh-field">
                            <label class="uh-label" for="theme-{{ $group }}-{{ $key }}">{{ $field['label'] }}</label>
                            @if($field['type'] === 'opacity')
                                <div class="flex items-center gap-3">
                                    <input id="theme-{{ $group }}-{{ $key }}" type="range" min="0" max="1" step="0.05" class="uh-range flex-1"
                                           :value="theme.advanced.{{ $group }}.{{ $key }} === '' ? 0.3 : theme.advanced.{{ $group }}.{{ $key }}"
                                           @input="theme.advanced.{{ $group }}.{{ $key }} = $event.target.value; clearMessages()">
                                    <span class="w-10 text-right font-mono text-xs tabular-nums" x-text="Number(theme.advanced.{{ $group }}.{{ $key }} === '' ? 0.3 : theme.advanced.{{ $group }}.{{ $key }}).toFixed(2)"></span>
                                </div>
                            @else
                                <div class="flex min-w-0 items-center gap-2">
                                    <input type="color" class="dd-color-swatch" :value="pickerColor(theme.advanced.{{ $group }}.{{ $key }}, pickerFallback('{{ $group }}', '{{ $key }}'))"
                                           @input="setAdvanced('{{ $group }}', '{{ $key }}', $event.target.value)" aria-label="Pick {{ strtolower($field['label']) }}">
                                    <input id="theme-{{ $group }}-{{ $key }}" class="uh-input min-w-0 font-mono uppercase" type="text" maxlength="7" pattern="#[0-9a-fA-F]{6}" placeholder="Inherit" x-model="theme.advanced.{{ $group }}.{{ $key }}" @input="clearMessages()" aria-label="{{ $field['label'] }} hex override">
                                </div>
                            @endif
                            <p class="uh-error" x-show="fieldError('theme.advanced.{{ $group }}.{{ $key }}')" x-text="fieldError('theme.advanced.{{ $group }}.{{ $key }}')" x-cloak></p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach

    <section class="uh-panel" aria-labelledby="theme-live-preview-heading">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 id="theme-live-preview-heading" class="uh-h4">Live preview</h2>
                <p class="mt-1 text-sm text-[var(--color-muted)]">Changes appear here as you edit. Save to apply them to the website.</p>
            </div>
        </div>
        <div class="dd-theme-live-preview mt-4 overflow-hidden rounded-xl border border-[var(--color-divider)]" :style="previewVariables()">
            <div class="flex items-center justify-between gap-4 px-4 py-3" style="background:var(--color-secondary);color:var(--color-secondary-text)">
                <span class="font-semibold">Urban Haven</span>
                <span class="text-xs" style="color:var(--color-slider-subtitle)">Buy　 Rent　 About us</span>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2" style="background:var(--color-page-background);color:var(--color-body-text)">
                <div class="rounded-lg p-4" style="background:var(--color-card-surface);border:1px solid var(--color-divider)">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--color-muted-text)">Featured</p>
                    <div class="mt-2 h-12 rounded-lg" style="background:linear-gradient(to bottom, transparent, color-mix(in srgb, var(--color-hero-overlay) calc(var(--color-hero-overlay-opacity) * 100%), transparent)),var(--color-secondary)" aria-hidden="true"></div>
                    <h3 class="mt-3 text-lg font-semibold" style="color:var(--color-slider-title)">Considered places to live</h3>
                    <p class="mt-1 text-sm" style="color:var(--color-slider-subtitle)">A preview of the saved hero and slider palette.</p>
                    <div class="mt-3 flex items-center gap-2"><span class="size-2 rounded-full" style="background:var(--color-carousel-dot)"></span><span class="size-2 rounded-full opacity-50" style="background:var(--color-carousel-dot)"></span><button type="button" class="ml-auto grid size-7 place-items-center rounded-full text-white" style="background:var(--color-carousel-arrow)" aria-label="Carousel arrow">›</button></div>
                    <button type="button" class="mt-4 rounded-full px-4 py-2 text-sm font-semibold text-white" style="background:var(--color-accent)">Explore properties</button>
                </div>
                <div class="flex items-end justify-between rounded-lg p-4" style="background:var(--color-mobile-nav-background);border:1px solid var(--color-divider)">
                    <div class="flex gap-4 text-xs"><span>Home</span><span style="color:var(--color-mobile-active-highlight)">Saved</span><span>Profile</span></div>
                    <span class="rounded-full px-2 py-1 text-xs font-semibold text-white" style="background:var(--color-notification-badge)">2</span>
                </div>
            </div>
        </div>
    </section>

    <div class="uh-admin-dock justify-end">
        <button type="submit" class="uh-btn-primary" :disabled="saving">
            <span class="uh-spinner" x-show="saving" x-cloak></span>
            <span x-text="saving ? 'Saving colors…' : 'Save colors'"></span>
        </button>
    </div>
</form>
