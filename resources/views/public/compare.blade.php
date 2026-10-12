@extends('layouts.public')

@section('content')
    <div class="uh-container uh-section-tight uh-compare-workspace" x-data="uhSavedList('compare', @js(route('saved.cards')), 'compare')">
        <template x-if="loading && !html">
            <div role="status" class="py-12">
                <p class="sr-only">{{ __('Loading…') }}</p>
                <div class="uh-skeleton h-96 rounded-2xl" aria-hidden="true"></div>
            </div>
        </template>

        <template x-if="!loading && failed">
            <x-ui.alert tone="danger">{{ __('We could not load the comparison. Check your connection and refresh the page.') }}</x-ui.alert>
        </template>

        <template x-if="!loading && !failed && count < 1">
            <div>
                <header class="mb-8">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-bold text-[var(--uh-ink)] tracking-tight">{{ __('Compare properties') }}</h1>
                    <p class="mt-2 text-base text-[var(--uh-muted)]">{{ __('See the differences side by side and find the right home for you.') }}</p>
                </header>

                <div class="py-16 text-center max-w-lg mx-auto bg-[var(--uh-paper)] rounded-2xl border border-[var(--uh-line)] p-8 shadow-xs">
                    <div class="size-16 rounded-2xl bg-[var(--color-accent)]/10 text-[var(--color-accent)] flex items-center justify-center mx-auto mb-4 text-2xl shadow-xs">
                        <x-icon name="compare" class="size-8" />
                    </div>
                    <h2 class="text-xl sm:text-2xl font-serif font-bold text-[var(--uh-ink)]">{{ __('Pick properties to compare') }}</h2>
                    <p class="mt-2 text-sm text-[var(--uh-muted)] leading-relaxed">
                        {{ __('Tap Compare on any property card or page. You can compare up to 4 homes side by side to see key tradeoffs, pricing, and specs.') }}
                    </p>
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                        <x-ui.pill-link variant="primary" :href="route('properties.index')">{{ __('Explore properties') }}</x-ui.pill-link>
                        <x-ui.pill-link variant="light" :href="route('shortlist')">{{ __('Open your shortlist') }}</x-ui.pill-link>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="count >= 1" x-cloak x-effect="if (count < 1) html = ''">
            <div class="transition-opacity duration-200" :class="{ 'opacity-60 pointer-events-none': loading }" :aria-busy="loading.toString()" x-html="html"></div>
        </div>
    </div>
@endsection

