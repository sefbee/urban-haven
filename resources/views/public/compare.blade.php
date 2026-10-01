@extends('layouts.public')

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container py-8 md:py-10">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Compare')],
            ]" />
            <h1 class="uh-h1 mt-3">{{ __('Compare properties') }}</h1>
            <p class="uh-lede mt-3 max-w-2xl">{{ __('Add up to :count properties with the compare button to see them side by side.', ['count' => $compareLimit]) }}</p>
        </div>
    </div>

    <div class="uh-container uh-section-tight" x-data="uhSavedList('compare', @js(route('saved.cards')), 'compare')">
        <p class="sr-only" aria-live="polite" x-text="$store.saved.notice"></p>

        <template x-if="loading">
            <p class="text-sm text-[var(--color-muted)]" role="status">{{ __('Loading…') }}</p>
        </template>
        <template x-if="!loading && failed">
            <x-ui.alert tone="danger">{{ __('We could not load the comparison. Check your connection and refresh the page.') }}</x-ui.alert>
        </template>
        <template x-if="!loading && !failed && count < 2">
            <x-ui.empty icon="compare" :title="__('Add at least two properties to compare')"
                        :description="__('Use the compare button on property cards or property pages.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Search properties') }}</a>
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('shortlist') }}">{{ __('Open your shortlist') }}</a>
            </x-ui.empty>
        </template>

        <div x-show="!loading && count >= 2" x-cloak>
            <p class="mb-3 text-sm text-[var(--color-muted)] sm:hidden">{{ __('Scroll the table sideways to see every property.') }}</p>
            <div x-html="html"></div>
        </div>
    </div>
@endsection
