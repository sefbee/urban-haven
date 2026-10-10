@extends('layouts.public')

@section('content')
    @include('public.partials.page-head', [
        'title' => __('Your shortlist'),
        'lede' => __('Saved on this device. No account needed.'),
    ])

    <div class="uh-container uh-section-tight" x-data="uhSavedList('shortlist', @js(route('saved.cards')))">
        <template x-if="loading && !html">
            <div role="status">
                <p class="sr-only">{{ __('Loading your shortlist…') }}</p>
                <div class="uh-grid-cards is-large" aria-hidden="true">
                    @foreach(range(1, 2) as $placeholder)
                        <div class="uh-skeleton-card">
                            <div class="uh-skeleton"></div>
                            <div class="uh-skeleton h-4 w-2/3"></div>
                            <div class="uh-skeleton h-6 w-1/2"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </template>
        <template x-if="!loading && failed">
            <x-ui.alert tone="danger">
                {{ __('We could not load your shortlist. Check your connection and refresh the page.') }}
            </x-ui.alert>
        </template>
        <template x-if="!loading && !failed && count === 0">
            <x-ui.empty icon="heart" :title="__('Your collection starts here')"
                        :description="__('Tap the heart on any property and it waits for you here. Sold or removed properties drop off automatically.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Explore properties') }}</a>
            </x-ui.empty>
        </template>

        <div x-show="count > 0 && html" x-cloak>
            <div class="uh-collection-bar">
                <p class="uh-numeric" x-text="count === 1 ? @js(__('1 saved property')) : @js(__(':count saved properties')).replace(':count', count)"></p>
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                    <a class="uh-btn-text" href="{{ route('compare') }}">
                        <span x-text="($store.saved.compare.length >= 2 ? @js(__('See the differences')) : @js(__('Compare'))) + ' (' + $store.saved.compare.length + ')'">{{ __('Compare') }}</span>
                    </a>
                    <button type="button" class="uh-btn-text is-muted"
                            @click="if (window.confirm(window.uhCopyText('shortlist_clear_confirm'))) $store.saved.clear('shortlist')">{{ __('Clear all') }}</button>
                </div>
            </div>
            <p class="uh-collection-next" x-show="count >= 2 && $store.saved.compare.length < 2">
                {{ __('Narrowing it down? Tap Compare on two of these and we will show you exactly how they differ.') }}
            </p>
            <div class="transition-opacity" :class="{ 'opacity-60': loading }" :aria-busy="loading.toString()" x-html="html"></div>
        </div>

        <section class="uh-recent" aria-labelledby="recent-heading" x-data="uhSavedList('recent', @js(route('saved.cards')), 'strip')" x-show="count > 0" x-cloak>
            <h2 id="recent-heading" class="uh-h3">{{ __('Recently viewed') }}</h2>
            <div x-html="html"></div>
        </section>
    </div>
@endsection
