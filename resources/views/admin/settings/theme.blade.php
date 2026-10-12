@php
    $advancedGroupLabels = [
        'surfaces_text' => 'Page surfaces & text',
        'hero_slider' => 'Hero & slider',
        'mobile_nav' => 'Mobile menu & bottom nav',
    ];
@endphp

<form method="POST" action="{{ route('api.admin.settings.theme') }}"
      class="space-y-6" x-data="uhThemeEditor(@js($themeColors))" @submit.prevent="save" data-unsaved-guard>
    @csrf

    <p class="uh-alert uh-alert-danger" x-show="error" x-text="error" x-cloak role="alert"></p>
    <p class="uh-alert uh-alert-success" x-show="success" x-text="success" x-cloak role="status"></p>

    {{-- Header & 60-30-10 System Explanation --}}
    <section class="uh-panel" aria-labelledby="theme-main-heading">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--admin-accent-soft)] px-2.5 py-0.5 text-xs font-semibold text-[var(--admin-accent)]">
                    <x-icon name="sparkle" class="size-3.5" /> 6-Colour Palette Rule (60-30-10)
                </span>
                <h2 id="theme-main-heading" class="uh-h3 mt-2">Brand & Website Colour Palette</h2>
                <p class="mt-1 max-w-2xl text-sm text-[var(--color-muted)]">
                    Structure your website using designer Abi Connick’s 6-colour palette rule: <strong>2 Primary</strong> colours (60% dominant canvas & surfaces), <strong>2 Secondary</strong> colours (30% structure & typography), and <strong>2 Accent</strong> colours (10% action & badges).
                </p>
            </div>

            {{-- 60-30-10 Proportional Summary Bar --}}
            <div class="w-full xl:w-96 rounded-xl border border-[var(--color-line)] bg-white p-3.5 shadow-sm">
                <div class="flex items-center justify-between text-xs font-semibold text-[var(--color-muted)] mb-1.5">
                    <span>Palette Balance</span>
                    <span class="font-mono text-[0.7rem]">60% · 30% · 10%</span>
                </div>
                <div class="flex h-4 w-full overflow-hidden rounded-full ring-1 ring-black/10">
                    <div class="flex h-full w-[60%] transition-all duration-300" title="Primary (60%): Dominant Canvas & Surfaces">
                        <div class="h-full w-1/2" :style="`background:${theme.brand.primary_1}`"></div>
                        <div class="h-full w-1/2 border-l border-black/10" :style="`background:${theme.brand.primary_2}`"></div>
                    </div>
                    <div class="flex h-full w-[30%] transition-all duration-300" title="Secondary (30%): Structure & Typography">
                        <div class="h-full w-1/2" :style="`background:${theme.brand.secondary_1}`"></div>
                        <div class="h-full w-1/2 border-l border-white/20" :style="`background:${theme.brand.secondary_2}`"></div>
                    </div>
                    <div class="flex h-full w-[10%] transition-all duration-300" title="Accent (10%): Actions & Badges">
                        <div class="h-full w-1/2" :style="`background:${theme.brand.accent_1}`"></div>
                        <div class="h-full w-1/2 border-l border-black/10" :style="`background:${theme.brand.accent_2}`"></div>
                    </div>
                </div>
                <div class="mt-2 grid grid-cols-3 text-center text-[0.65rem] font-bold tracking-wider text-[var(--color-muted)] uppercase">
                    <span>60% Primary</span>
                    <span>30% Secondary</span>
                    <span>10% Accent</span>
                </div>
            </div>
        </div>

        {{-- Curated Designer Presets --}}
        <div class="mt-6 border-t border-[var(--color-line)] pt-5">
            <div class="flex items-center justify-between gap-2 mb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted)]">Curated Designer Palettes</h3>
                <span class="text-xs text-[var(--color-muted)]">Click any preset to preview</span>
            </div>
            <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-5">
                <template x-for="preset in presets" :key="preset.name">
                    <button type="button" @click="applyPreset(preset)"
                            class="group relative flex flex-col justify-between rounded-xl border border-[var(--color-line)] bg-white p-3 text-left transition hover:border-[var(--admin-accent)] hover:shadow-md active:scale-[0.98]">
                        <div>
                            <div class="flex h-5 w-full overflow-hidden rounded-md ring-1 ring-black/10 mb-2">
                                <span class="h-full w-1/6" :style="`background:${preset.brand.primary_1}`"></span>
                                <span class="h-full w-1/6 border-l border-black/5" :style="`background:${preset.brand.primary_2}`"></span>
                                <span class="h-full w-1/6" :style="`background:${preset.brand.secondary_1}`"></span>
                                <span class="h-full w-1/6" :style="`background:${preset.brand.secondary_2}`"></span>
                                <span class="h-full w-1/6" :style="`background:${preset.brand.accent_1}`"></span>
                                <span class="h-full w-1/6" :style="`background:${preset.brand.accent_2}`"></span>
                            </div>
                            <span class="block text-xs font-bold text-slate-800 group-hover:text-[var(--admin-accent)]" x-text="preset.name"></span>
                            <span class="mt-0.5 block text-[0.7rem] leading-snug text-[var(--color-muted)]" x-text="preset.desc"></span>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </section>

    {{-- The 6 Colors in 3 Proportional Groups --}}
    <div class="grid gap-6">

        {{-- Group 1: 60% Primary Colors (2 Colors) --}}
        <section class="uh-panel" aria-labelledby="group-primary-heading">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex size-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">60%</span>
                        <h3 id="group-primary-heading" class="uh-h4">Primary Colours · Dominant Canvas & Surfaces</h3>
                    </div>
                    <p class="mt-1 text-xs text-[var(--color-muted)]">Occupies 60% of the visual field. Sets the general atmosphere, background canvas, cards and large panels.</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                {{-- Primary 1: Dominant Canvas --}}
                <div class="rounded-xl border border-[var(--color-line)] bg-white p-4 transition hover:border-slate-300">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div>
                            <span class="text-[0.65rem] font-bold uppercase tracking-wider text-blue-600">Primary 1 · Dominant Base</span>
                            <label class="block text-sm font-semibold text-slate-800" for="theme-brand-primary_1">Canvas & Page Background</label>
                        </div>
                        <span class="size-8 rounded-lg border border-black/10 shadow-inner shrink-0" :style="`background:${theme.brand.primary_1}`" aria-hidden="true"></span>
                    </div>
                    <p class="text-xs text-[var(--color-muted)] mb-3">Overall website background, wide layout sections, and subtle neutral surfaces.</p>
                    <div class="flex min-w-0 items-center gap-2">
                        <input type="color" class="dd-color-swatch" :value="theme.brand.primary_1" @input="setBrand('primary_1', $event.target.value)" aria-label="Pick Primary 1 Canvas">
                        <input id="theme-brand-primary_1" class="uh-input min-w-0 font-mono uppercase" type="text" maxlength="7" pattern="#[0-9a-fA-F]{6}" placeholder="#F3F0EA" x-model="theme.brand.primary_1" @input="clearMessages()" aria-label="Primary 1 Canvas hex value">
                    </div>
                    <p class="uh-error" x-show="fieldError('theme.brand.primary_1')" x-text="fieldError('theme.brand.primary_1')" x-cloak></p>
                </div>

                {{-- Primary 2: Surface & Cards --}}
                <div class="rounded-xl border border-[var(--color-line)] bg-white p-4 transition hover:border-slate-300">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div>
                            <span class="text-[0.65rem] font-bold uppercase tracking-wider text-blue-600">Primary 2 · Surface Base</span>
                            <label class="block text-sm font-semibold text-slate-800" for="theme-brand-primary_2">Cards & Content Surfaces</label>
                        </div>
                        <span class="size-8 rounded-lg border border-black/10 shadow-inner shrink-0" :style="`background:${theme.brand.primary_2}`" aria-hidden="true"></span>
                    </div>
                    <p class="text-xs text-[var(--color-muted)] mb-3">Property cards, dropdown menus, filter drawers, modal dialogs, and content panels.</p>
                    <div class="flex min-w-0 items-center gap-2">
                        <input type="color" class="dd-color-swatch" :value="theme.brand.primary_2" @input="setBrand('primary_2', $event.target.value)" aria-label="Pick Primary 2 Surface">
                        <input id="theme-brand-primary_2" class="uh-input min-w-0 font-mono uppercase" type="text" maxlength="7" pattern="#[0-9a-fA-F]{6}" placeholder="#FFFFFF" x-model="theme.brand.primary_2" @input="clearMessages()" aria-label="Primary 2 Surface hex value">
                    </div>
                    <p class="uh-error" x-show="fieldError('theme.brand.primary_2')" x-text="fieldError('theme.brand.primary_2')" x-cloak></p>
                </div>
            </div>
        </section>

        {{-- Group 2: 30% Secondary Colors (2 Colors) --}}
        <section class="uh-panel" aria-labelledby="group-secondary-heading">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex size-6 items-center justify-center rounded-full bg-slate-800 text-xs font-bold text-white">30%</span>
                        <h3 id="group-secondary-heading" class="uh-h4">Secondary Colours · Structure & Typography</h3>
                    </div>
                    <p class="mt-1 text-xs text-[var(--color-muted)]">Occupies 30% of the visual field. Provides readability, contrast, structural foundation, and clear hierarchy.</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                {{-- Secondary 1: Dark Foundation & Primary Text --}}
                <div class="rounded-xl border border-[var(--color-line)] bg-white p-4 transition hover:border-slate-300">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div>
                            <span class="text-[0.65rem] font-bold uppercase tracking-wider text-slate-700">Secondary 1 · Deep Structure</span>
                            <label class="block text-sm font-semibold text-slate-800" for="theme-brand-secondary_1">Headings, Dark Sections & Body Text</label>
                        </div>
                        <span class="size-8 rounded-lg border border-black/10 shadow-inner shrink-0" :style="`background:${theme.brand.secondary_1}`" aria-hidden="true"></span>
                    </div>
                    <p class="text-xs text-[var(--color-muted)] mb-3">Primary headings, body text, hero dark overlay, navigation bar, and footer background.</p>
                    <div class="flex min-w-0 items-center gap-2">
                        <input type="color" class="dd-color-swatch" :value="theme.brand.secondary_1" @input="setBrand('secondary_1', $event.target.value)" aria-label="Pick Secondary 1 Dark">
                        <input id="theme-brand-secondary_1" class="uh-input min-w-0 font-mono uppercase" type="text" maxlength="7" pattern="#[0-9a-fA-F]{6}" placeholder="#0D1110" x-model="theme.brand.secondary_1" @input="clearMessages()" aria-label="Secondary 1 Dark hex value">
                    </div>
                    <p class="uh-error" x-show="fieldError('theme.brand.secondary_1')" x-text="fieldError('theme.brand.secondary_1')" x-cloak></p>
                </div>

                {{-- Secondary 2: Muted Supporting & Borders --}}
                <div class="rounded-xl border border-[var(--color-line)] bg-white p-4 transition hover:border-slate-300">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div>
                            <span class="text-[0.65rem] font-bold uppercase tracking-wider text-slate-700">Secondary 2 · Supporting Tone</span>
                            <label class="block text-sm font-semibold text-slate-800" for="theme-brand-secondary_2">Muted Subtext, Dividers & Lines</label>
                        </div>
                        <span class="size-8 rounded-lg border border-black/10 shadow-inner shrink-0" :style="`background:${theme.brand.secondary_2}`" aria-hidden="true"></span>
                    </div>
                    <p class="text-xs text-[var(--color-muted)] mb-3">Subtitles, property specifications (beds/baths), location tags, borders, and separators.</p>
                    <div class="flex min-w-0 items-center gap-2">
                        <input type="color" class="dd-color-swatch" :value="theme.brand.secondary_2" @input="setBrand('secondary_2', $event.target.value)" aria-label="Pick Secondary 2 Muted">
                        <input id="theme-brand-secondary_2" class="uh-input min-w-0 font-mono uppercase" type="text" maxlength="7" pattern="#[0-9a-fA-F]{6}" placeholder="#6E675F" x-model="theme.brand.secondary_2" @input="clearMessages()" aria-label="Secondary 2 Muted hex value">
                    </div>
                    <p class="uh-error" x-show="fieldError('theme.brand.secondary_2')" x-text="fieldError('theme.brand.secondary_2')" x-cloak></p>
                </div>
            </div>
        </section>

        {{-- Group 3: 10% Accent Colors (2 Colors) --}}
        <section class="uh-panel" aria-labelledby="group-accent-heading">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex size-6 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-800">10%</span>
                        <h3 id="group-accent-heading" class="uh-h4">Accent Colours · Action & Badges</h3>
                    </div>
                    <p class="mt-1 text-xs text-[var(--color-muted)]">Occupies 10% of the visual field. Draws the user’s eye to primary actions, interactive controls, and highlight badges.</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                {{-- Accent 1: Primary Action & CTA --}}
                <div class="rounded-xl border border-[var(--color-line)] bg-white p-4 transition hover:border-slate-300">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div>
                            <span class="text-[0.65rem] font-bold uppercase tracking-wider text-amber-700">Accent 1 · Primary CTA</span>
                            <label class="block text-sm font-semibold text-slate-800" for="theme-brand-accent_1">Buttons, Active Tabs & Links</label>
                        </div>
                        <span class="size-8 rounded-lg border border-black/10 shadow-inner shrink-0" :style="`background:${theme.brand.accent_1}`" aria-hidden="true"></span>
                    </div>
                    <p class="text-xs text-[var(--color-muted)] mb-3">Main call-to-action buttons, active navigation pills, search filters, and key link hovers.</p>
                    <div class="flex min-w-0 items-center gap-2">
                        <input type="color" class="dd-color-swatch" :value="theme.brand.accent_1" @input="setBrand('accent_1', $event.target.value)" aria-label="Pick Accent 1 Action">
                        <input id="theme-brand-accent_1" class="uh-input min-w-0 font-mono uppercase" type="text" maxlength="7" pattern="#[0-9a-fA-F]{6}" placeholder="#1A3328" x-model="theme.brand.accent_1" @input="clearMessages()" aria-label="Accent 1 Action hex value">
                    </div>
                    <p class="uh-error" x-show="fieldError('theme.brand.accent_1')" x-text="fieldError('theme.brand.accent_1')" x-cloak></p>
                </div>

                {{-- Accent 2: Secondary Pop & Badges --}}
                <div class="rounded-xl border border-[var(--color-line)] bg-white p-4 transition hover:border-slate-300">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div>
                            <span class="text-[0.65rem] font-bold uppercase tracking-wider text-amber-700">Accent 2 · Secondary Pop</span>
                            <label class="block text-sm font-semibold text-slate-800" for="theme-brand-accent_2">Featured Badges, Stars & Highlights</label>
                        </div>
                        <span class="size-8 rounded-lg border border-black/10 shadow-inner shrink-0" :style="`background:${theme.brand.accent_2}`" aria-hidden="true"></span>
                    </div>
                    <p class="text-xs text-[var(--color-muted)] mb-3">“Featured” badges, trending star icons, notification count pills, and secondary warm highlights.</p>
                    <div class="flex min-w-0 items-center gap-2">
                        <input type="color" class="dd-color-swatch" :value="theme.brand.accent_2" @input="setBrand('accent_2', $event.target.value)" aria-label="Pick Accent 2 Badge">
                        <input id="theme-brand-accent_2" class="uh-input min-w-0 font-mono uppercase" type="text" maxlength="7" pattern="#[0-9a-fA-F]{6}" placeholder="#C3A06A" x-model="theme.brand.accent_2" @input="clearMessages()" aria-label="Accent 2 Badge hex value">
                    </div>
                    <p class="uh-error" x-show="fieldError('theme.brand.accent_2')" x-text="fieldError('theme.brand.accent_2')" x-cloak></p>
                </div>
            </div>
        </section>
    </div>

    {{-- Interactive Live Preview --}}
    <section class="uh-panel" aria-labelledby="theme-live-preview-heading">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 id="theme-live-preview-heading" class="uh-h4">Interactive Live Preview</h3>
                <p class="mt-1 text-sm text-[var(--color-muted)]">Real-time simulation of public pages using the 6-colour system. Updates instantly as you adjust any color.</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold text-[var(--color-muted)]">
                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Live Rendering Active</span>
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-[var(--color-divider)] shadow-lg transition-colors" :style="previewVariables()">
            {{-- Public Header Mockup --}}
            <header class="flex items-center justify-between gap-4 px-6 py-4 border-b border-white/10" style="background:var(--color-secondary-1);color:var(--color-secondary-text)">
                <div class="flex items-center gap-3">
                    <span class="font-serif text-lg font-bold tracking-tight">Urban Haven</span>
                    <span class="hidden sm:inline-block rounded px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-wider" style="background:var(--color-accent-2);color:#111111">Luxury</span>
                </div>

                <nav class="hidden md:flex items-center gap-6 text-xs font-semibold">
                    <span class="opacity-90 hover:opacity-100 cursor-pointer">Properties</span>
                    <span class="opacity-90 hover:opacity-100 cursor-pointer">Projects</span>
                    <span class="opacity-90 hover:opacity-100 cursor-pointer">About</span>
                    <span class="rounded-full px-3 py-1 text-white shadow-sm" style="background:var(--color-accent-1)">Explore</span>
                </nav>

                <div class="flex items-center gap-3">
                    <div class="relative flex items-center justify-center size-8 rounded-full bg-white/10 text-white">
                        <x-icon name="bookmark" class="size-4" />
                        <span class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full text-[0.65rem] font-bold text-white shadow" style="background:var(--color-accent-2)">3</span>
                    </div>
                </div>
            </header>

            {{-- Page Canvas (Primary 1) --}}
            <div class="p-6 md:p-8" style="background:var(--color-page-background);color:var(--color-body-text)">
                
                {{-- Hero Section Preview --}}
                <div class="mb-8 rounded-2xl p-6 md:p-8 relative overflow-hidden" style="background:linear-gradient(135deg, color-mix(in srgb, var(--color-primary-1) 85%, var(--color-accent-2)) 0%, var(--color-primary-1) 100%);border:1px solid var(--color-divider)">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold tracking-wide uppercase" style="background:var(--color-card-surface);color:var(--color-accent-1);border:1px solid var(--color-divider)">
                        <x-icon name="sparkle" class="size-3" /> Exclusive Residences
                    </span>
                    <h1 class="mt-3 text-2xl md:text-3xl font-bold tracking-tight" style="color:var(--color-secondary-1)">
                        Considered Places to Live in Dhaka
                    </h1>
                    <p class="mt-2 max-w-xl text-sm md:text-base leading-relaxed" style="color:var(--color-muted-text)">
                        Discover signature penthouses, garden residences, and contemporary architectural masterpieces crafted for discerning lives.
                    </p>
                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button type="button" class="rounded-lg px-5 py-2.5 text-sm font-semibold text-white shadow-md transition" style="background:var(--color-accent-1)">
                            Search Properties
                        </button>
                        <button type="button" class="rounded-lg px-5 py-2.5 text-sm font-semibold transition" style="background:var(--color-card-surface);color:var(--color-body-text);border:1px solid var(--color-divider)">
                            Explore Projects
                        </button>
                    </div>
                </div>

                {{-- Property Card Grid Simulation --}}
                <div class="grid gap-6 sm:grid-cols-2">
                    {{-- Card 1: Featured Residence --}}
                    <div class="rounded-xl overflow-hidden shadow-sm transition hover:shadow-md" style="background:var(--color-card-surface);border:1px solid var(--color-divider)">
                        <div class="relative h-44 w-full bg-slate-200 overflow-hidden flex items-end p-3" style="background:linear-gradient(to top, rgba(0,0,0,0.65), transparent 60%), #8fa49a">
                            <span class="absolute top-3 left-3 rounded px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-wider text-black shadow-sm" style="background:var(--color-accent-2)">
                                Featured
                            </span>
                            <span class="absolute top-3 right-3 flex size-7 items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-sm">
                                <x-icon name="bookmark" class="size-3.5" />
                            </span>
                            <div class="text-white">
                                <span class="text-xs font-semibold opacity-90">Gulshan 2, Dhaka</span>
                                <p class="text-lg font-bold">BDT 4.85 Crore</p>
                            </div>
                        </div>
                        <div class="p-4">
                            <h4 class="font-bold text-base leading-snug" style="color:var(--color-secondary-1)">The Glass Pavilion Residence</h4>
                            <p class="mt-1 text-xs font-medium" style="color:var(--color-muted-text)">4 Beds · 4 Baths · 3,420 sqft</p>
                            <div class="mt-4 pt-3 flex items-center justify-between border-t border-[var(--color-divider)]">
                                <span class="text-xs font-semibold" style="color:var(--color-accent-1)">View Details →</span>
                                <span class="rounded px-2 py-0.5 text-[0.7rem] font-semibold" style="background:var(--color-page-background);color:var(--color-body-text)">Apartment</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card 2: Trending Project --}}
                    <div class="rounded-xl overflow-hidden shadow-sm transition hover:shadow-md" style="background:var(--color-card-surface);border:1px solid var(--color-divider)">
                        <div class="relative h-44 w-full bg-slate-200 overflow-hidden flex items-end p-3" style="background:linear-gradient(to top, rgba(0,0,0,0.65), transparent 60%), #4b5e65">
                            <span class="absolute top-3 left-3 rounded px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-wider text-white shadow-sm" style="background:var(--color-accent-1)">
                                For Rent
                            </span>
                            <span class="absolute top-3 right-3 flex size-7 items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-sm">
                                <x-icon name="bookmark" class="size-3.5" />
                            </span>
                            <div class="text-white">
                                <span class="text-xs font-semibold opacity-90">Banani Lakeview, Dhaka</span>
                                <p class="text-lg font-bold">BDT 1.75 Lac / mo</p>
                            </div>
                        </div>
                        <div class="p-4">
                            <h4 class="font-bold text-base leading-snug" style="color:var(--color-secondary-1)">Verdant Haven Terrace Suite</h4>
                            <p class="mt-1 text-xs font-medium" style="color:var(--color-muted-text)">3 Beds · 3 Baths · 2,650 sqft</p>
                            <div class="mt-4 pt-3 flex items-center justify-between border-t border-[var(--color-divider)]">
                                <span class="text-xs font-semibold" style="color:var(--color-accent-1)">View Details →</span>
                                <span class="rounded px-2 py-0.5 text-[0.7rem] font-semibold" style="background:var(--color-page-background);color:var(--color-body-text)">Condominium</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mobile Bottom Bar Preview --}}
                <div class="mt-6 flex items-center justify-around rounded-xl p-3 shadow-inner" style="background:var(--color-mobile-nav-background);border:1px solid var(--color-divider)">
                    <div class="flex flex-col items-center gap-1 text-[0.7rem] font-bold" style="color:var(--color-mobile-active-highlight)">
                        <x-icon name="dashboard" class="size-4" />
                        <span>Home</span>
                    </div>
                    <div class="flex flex-col items-center gap-1 text-[0.7rem] font-medium" style="color:var(--color-muted-text)">
                        <x-icon name="search" class="size-4" />
                        <span>Search</span>
                    </div>
                    <div class="relative flex flex-col items-center gap-1 text-[0.7rem] font-medium" style="color:var(--color-muted-text)">
                        <x-icon name="bookmark" class="size-4" />
                        <span>Saved</span>
                        <span class="absolute -top-1 -right-2 flex size-3.5 items-center justify-center rounded-full text-[0.55rem] font-bold text-white" style="background:var(--color-notification-badge)">2</span>
                    </div>
                    <div class="flex flex-col items-center gap-1 text-[0.7rem] font-medium" style="color:var(--color-muted-text)">
                        <x-icon name="phone" class="size-4" />
                        <span>Contact</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Advanced Overrides --}}
    @foreach(\App\Support\ThemeColors::ADVANCED_FIELDS as $group => $fields)
        <section class="overflow-hidden rounded-xl border border-[var(--color-line)] bg-white" aria-labelledby="theme-group-{{ $group }}">
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3.5">
                <button type="button" class="flex min-w-0 flex-1 items-center justify-between gap-3 text-left" @click="expanded.{{ $group }} = ! expanded.{{ $group }}" :aria-expanded="expanded.{{ $group }}.toString()" aria-controls="theme-fields-{{ $group }}">
                    <span class="min-w-0">
                        <span id="theme-group-{{ $group }}" class="block text-sm font-semibold">{{ $advancedGroupLabels[$group] }} <span class="font-normal text-[var(--color-muted)]">(advanced)</span></span>
                        <span class="mt-0.5 block text-xs text-[var(--color-muted)]">Optional fine-tuning. Blank fields automatically inherit from the 6 brand colours.</span>
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

    {{-- Sticky Save Dock --}}
    <div class="uh-admin-dock justify-end">
        <button type="submit" class="uh-btn-primary" :disabled="saving">
            <span class="uh-spinner" x-show="saving" x-cloak></span>
            <span x-text="saving ? 'Saving colours…' : 'Save colour palette'"></span>
        </button>
    </div>
</form>
