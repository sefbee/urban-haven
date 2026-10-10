@extends('layouts.public')

@section('content')
    @include('public.partials.page-head', [
        'title' => __('Compare properties'),
        'lede' => __('Put up to :count properties side by side. We highlight what is different, so the choice gets clearer.', ['count' => $compareLimit]),
    ])

    <div class="uh-container uh-section-tight uh-compare-workspace" x-data="uhSavedList('compare', @js(route('saved.cards')), 'compare')">
        <template x-if="loading && !html">
            <div role="status">
                <p class="sr-only">{{ __('Loading…') }}</p>
                <div class="uh-skeleton h-96" aria-hidden="true"></div>
            </div>
        </template>
        <template x-if="!loading && failed">
            <x-ui.alert tone="danger">{{ __('We could not load the comparison. Check your connection and refresh the page.') }}</x-ui.alert>
        </template>
        <template x-if="!loading && !failed && count < 2">
            <x-ui.empty icon="compare" :title="__('Pick two to compare')" :description="__('Tap Compare on any property card or page. Once you have two, the differences appear here.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Explore properties') }}</a>
                <a class="uh-btn-secondary uh-btn-sm" href="{{ route('shortlist') }}">{{ __('Open your shortlist') }}</a>
            </x-ui.empty>
        </template>

        <div x-show="count >= 2" x-cloak x-effect="if (count < 2) html = ''">
            <div class="transition-opacity" :class="{ 'opacity-60': loading }" :aria-busy="loading.toString()" x-html="html"></div>
        </div>
    </div>
@endsection
