@extends('layouts.admin')
@section('title', 'Page SEO')

@php
    $active = request('page');
    $active = is_string($active) && isset($pages[$active]) ? $active : (old('route') ?: array_key_first($pages));
@endphp

@section('content')
    <x-ui.page-header compact title="Page SEO"
                      description="Focus keyword, meta title, description and Search Console code for the website’s fixed pages. Properties, articles, pages, areas and categories have the same settings on their own edit screens.">
        <x-slot:eyebrow>Website pages</x-slot:eyebrow>
        <x-slot:actions>
            @if(auth()->user()->isOwnerAdmin())
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.settings.seo') }}">Site-wide defaults</a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="dd-pageseo" x-data="{ active: @js($active) }">
        <nav class="dd-pageseo-list" aria-label="Pages">
            @foreach($pages as $route => $page)
                @php
                    $set = filled($values[$route]['meta_title']) || filled($values[$route]['meta_description']);
                @endphp
                <button type="button" class="dd-pageseo-item" :class="active === @js($route) && 'is-active'" @click="active = @js($route)">
                    <span class="min-w-0">
                        <span class="block truncate font-medium">{{ $page['label'] }}</span>
                        <span class="block truncate text-xs text-[var(--color-muted)]" dir="ltr">{{ $page['path'] }}</span>
                    </span>
                    <span @class(['dd-seo-state', 'is-set' => $set])>{{ $set ? 'Custom' : 'Default' }}</span>
                </button>
            @endforeach
        </nav>

        <div class="min-w-0">
            @foreach($pages as $route => $page)
                <form method="POST" action="{{ route('admin.page-seo.update') }}" x-show="active === @js($route)" @if($route !== $active) x-cloak @endif
                      class="space-y-4" data-unsaved-guard x-data="uhForm" @submit="submit">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="route" value="{{ $route }}">
                    <x-ui.alert tone="info">{{ $page['hint'] }}</x-ui.alert>

                    @include('admin.partials.seo-panel', [
                        'heading' => $page['label'],
                        'seo' => $values[$route],
                        'prefix' => 'pages['.$route.']',
                        'idPrefix' => 'page-seo-'.str_replace('.', '-', $route),
                        'staticPath' => $page['path'],
                        'baseUrl' => url('/'),
                        'titleSource' => '__none__',
                        'fallbackTitle' => $page['title'] ?: $siteTitle,
                        'fallbackDescription' => $siteDescription,
                    ])

                    <div class="flex items-center gap-3">
                        <button type="submit" class="uh-btn-primary" :disabled="submitting">
                            <span class="uh-spinner" x-show="submitting" x-cloak></span>
                            <span>Save {{ Str::lower($page['label']) }} SEO</span>
                        </button>
                        <a class="uh-btn-ghost" href="{{ url($page['path']) }}" target="_blank" rel="noopener">
                            <x-icon name="external" class="size-4" />
                            View page
                        </a>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
@endsection
