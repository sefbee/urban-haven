@extends('layouts.admin')
@section('title', 'Home page')

@php
    $icons = ['hero' => 'image', 'trending' => 'sparkle', 'featured' => 'heart', 'locations' => 'pin', 'types' => 'tag', 'latest' => 'clock', 'why' => 'shield', 'articles' => 'document', 'faq' => 'info'];
    $visibleCount = $blocks->filter(fn ($block) => $block->is_visible)->count();
@endphp

@section('content')
    <x-ui.page-header compact title="Home page">
        <x-slot:eyebrow>Website pages</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-outline uh-btn-sm" href="{{ route('home') }}" target="_blank" rel="noopener">
                <x-icon name="external" class="size-4" />
                Preview home page
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="dd-summary-bar mt-1">
        <span><strong>{{ $blocks->count() }}</strong> sections</span>
        <span><strong>{{ $visibleCount }}</strong> shown</span>
        <span><strong>{{ $blocks->count() - $visibleCount }}</strong> hidden</span>
        @php $drafts = $blocks->filter(fn ($block) => $block->hasDraft())->count(); @endphp
        @if($drafts)
            <span class="text-[var(--dd-red)]"><strong>{{ $drafts }}</strong> waiting to be published</span>
        @endif
    </div>

    <ol class="dd-hs-list mt-5" aria-label="Homepage sections in display order">
        @foreach($blocks as $section => $block)
            @php $definition = $definitions[$section]; @endphp
            @php $content = \App\Support\HomeSections::resolve($section, $block->content); @endphp
            <li @class(['dd-hs-card', 'is-hidden' => ! $block->is_visible])>
                <span class="dd-hs-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="dd-hs-icon" aria-hidden="true"><x-icon :name="$icons[$section] ?? 'grid'" class="size-5" /></span>

                <div class="dd-hs-body">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="dd-hs-title">
                            <a href="{{ route('admin.home-sections.edit', $section) }}" class="uh-link-quiet">{{ $definition['label'] }}</a>
                        </h2>
                        <x-ui.badge :tone="$block->is_visible ? 'success' : 'neutral'">{{ $block->is_visible ? 'Shown' : 'Hidden' }}</x-ui.badge>
                        @if($block->hasDraft())
                            <x-ui.badge tone="warn">Draft pending</x-ui.badge>
                        @endif
                        @if($section === 'hero')
                            <x-ui.badge tone="info">{{ $block->images()->count() }} {{ \Illuminate\Support\Str::plural('photo', $block->images()->count()) }}</x-ui.badge>
                        @endif
                    </div>
                    <p class="dd-hs-summary">{{ $definition['summary'] }}</p>
                    <p class="dd-hs-preview">“{{ \Illuminate\Support\Str::limit($content['title'] ?? '', 80) }}”</p>
                </div>

                <div class="dd-hs-actions">
                    <a href="{{ route('admin.home-sections.edit', $section) }}" class="uh-btn-primary uh-btn-sm">Edit</a>
                    @if($canPublish)
                        <form method="POST" action="{{ route('admin.home-sections.visibility', $section) }}">
                            @csrf
                            <button type="submit" class="uh-btn-outline uh-btn-sm" aria-label="{{ ($block->is_visible ? 'Hide ' : 'Show ').$definition['label'] }}">
                                {{ $block->is_visible ? 'Hide' : 'Show' }}
                            </button>
                        </form>
                        <div class="dd-hs-move" role="group" aria-label="Move {{ $definition['label'] }}">
                            <form method="POST" action="{{ route('admin.home-sections.move', $section) }}">
                                @csrf
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="uh-admin-icon-btn" @disabled($loop->first) aria-label="Move {{ $definition['label'] }} up" title="Move up">
                                    <x-icon name="chevron-down" class="size-4 rotate-180" />
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.home-sections.move', $section) }}">
                                @csrf
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="uh-admin-icon-btn" @disabled($loop->last) aria-label="Move {{ $definition['label'] }} down" title="Move down">
                                    <x-icon name="chevron-down" class="size-4" />
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>

    <p class="mt-5 text-xs text-[var(--color-muted)]">
        Sections that pull in listings, areas, articles or FAQs stay hidden on the live site while they have nothing to show.
    </p>
@endsection
