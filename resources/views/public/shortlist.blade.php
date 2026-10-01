@extends('layouts.public')

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container py-8 md:py-10">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Shortlist')],
            ]" />
            <h1 class="uh-h1 mt-3">{{ __('Your shortlist') }}</h1>
            <p class="uh-lede mt-3 max-w-2xl">{{ __('Properties you save are kept in this browser on this device. No account is needed.') }}</p>
        </div>
    </div>

    <div class="uh-container uh-section-tight" x-data="uhSavedList('shortlist', @js(route('saved.cards')))">
        <p class="sr-only" aria-live="polite" x-text="$store.saved.notice"></p>

        <template x-if="loading">
            <p class="text-sm text-[var(--color-muted)]" role="status">{{ __('Loading your shortlist…') }}</p>
        </template>
        <template x-if="!loading && failed">
            <x-ui.alert tone="danger">
                {{ __('We could not load your shortlist. Check your connection and refresh the page.') }}
            </x-ui.alert>
        </template>
        <template x-if="!loading && !failed && count === 0">
            <x-ui.empty icon="heart" :title="__('Nothing saved yet')"
                        :description="__('Tap the heart on any property to save it here. Sold or removed properties drop off automatically.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Search properties') }}</a>
            </x-ui.empty>
        </template>

        <div x-show="!loading && count > 0" x-cloak>
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm"><strong x-text="count"></strong> {{ __('saved') }}</p>
                <div class="flex gap-2">
                    <a class="uh-btn-outline uh-btn-sm" href="{{ route('compare') }}">
                        <x-icon name="compare" class="size-4" />
                        {{ __('Compare') }} (<span x-text="$store.saved.compare.length"></span>)
                    </a>
                    <button type="button" class="uh-btn-ghost uh-btn-sm text-[var(--color-danger)]"
                            @click="if (window.confirm(@js(__('Remove every property from your shortlist?')))) $store.saved.clear('shortlist')">{{ __('Clear all') }}</button>
                </div>
            </div>
            <div x-html="html"></div>
        </div>

        <section class="mt-14" aria-labelledby="recent-heading" x-data="uhSavedList('recent', @js(route('saved.cards')), 'strip')" x-show="count > 0" x-cloak>
            <h2 id="recent-heading" class="uh-h3">{{ __('Recently viewed') }}</h2>
            <div class="mt-4" x-html="html"></div>
        </section>
    </div>
@endsection
