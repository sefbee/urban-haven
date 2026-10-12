@extends('layouts.public')

@php
    $articlesSections = \App\Support\ArticlesPageSections::visible();
@endphp

@section('content')
    <main class="uh-container uh-articles-page">
        <header class="uh-editorial-masthead">
            <div>
                <p class="uh-editorial-overline">{{ $articlesSections['masthead']['eyebrow'] ?? __('Urban Haven Journal') }}</p>
                <h1 class="uh-editorial-title">{{ $activeCategory?->name ?? ($articlesSections['masthead']['title'] ?? __('Explore News, Insights and Videos')) }}</h1>
                <p class="uh-editorial-intro">{{ $articlesSections['masthead']['intro'] ?? __('Ideas, places and perspectives shaping the way Bangladesh lives.') }}</p>
            </div>

            @if($categories->isNotEmpty())
                <nav class="uh-editorial-categories" aria-label="{{ __('Article categories') }}">
                    <a href="{{ route('articles.index') }}" @class(['is-active' => ! $activeCategory])>{{ __('All stories') }}</a>
                    @foreach($categories as $category)
                        <a href="{{ route('articles.index', ['category' => $category->slug]) }}"
                           @class(['is-active' => $activeCategory?->is($category)])>{{ $category->name }}</a>
                    @endforeach
                </nav>
            @endif
        </header>

        @if($posts->isNotEmpty())
            @if(! $activeCategory && $posts->onFirstPage() && $posts->count() >= 5)
                @php
                    $mainPost = $posts->first();
                    $topRightPosts = $posts->slice(1, 2);
                    $bottomRightPosts = $posts->slice(3, 4);
                    $showcaseCategories = $categories->take(6);
                @endphp

                <section class="uh-editorial-feature" aria-label="{{ __('Featured articles') }}">
                    <a href="{{ route('articles.show', $mainPost->slug) }}" class="uh-editorial-lead group">
                        @if($cover = $mainPost->featuredImage())
                            <img src="{{ $cover->url(1280) }}" alt="{{ $cover->alt() ?: $mainPost->title }}" fetchpriority="high">
                        @else
                            <span class="uh-editorial-image-placeholder"></span>
                        @endif
                        <span class="uh-editorial-scrim"></span>
                        <span class="uh-editorial-lead-copy">
                            <span class="uh-editorial-kicker">{{ $mainPost->category?->name ?? __('Featured story') }}</span>
                            <span class="uh-editorial-lead-title">{{ $mainPost->title }}</span>
                            @if($mainPost->excerpt)
                                <span class="uh-editorial-lead-excerpt">{{ $mainPost->excerpt }}</span>
                            @endif
                            <span class="uh-editorial-date">{{ $mainPost->publicationState?->published_at?->format('M d, Y') }}</span>
                        </span>
                    </a>

                    <div class="uh-editorial-side">
                        <div class="uh-editorial-feature-pair">
                            @foreach($topRightPosts as $post)
                                <a href="{{ route('articles.show', $post->slug) }}" class="uh-editorial-card group">
                                    <span class="uh-editorial-card-image">
                                        @if($cover = $post->featuredImage())
                                            <img src="{{ $cover->url(768) }}" alt="{{ $cover->alt() ?: $post->title }}" loading="lazy" decoding="async">
                                        @else
                                            <span class="uh-editorial-image-placeholder"></span>
                                        @endif
                                    </span>
                                    <span class="uh-editorial-card-copy">
                                        <span class="uh-editorial-kicker">{{ $post->category?->name }}</span>
                                        <span class="uh-editorial-card-title">{{ $post->title }}</span>
                                        <span class="uh-editorial-date">{{ $post->publicationState?->published_at?->format('M d, Y') }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>

                        <div class="uh-editorial-briefs">
                            @foreach($bottomRightPosts as $post)
                                <a href="{{ route('articles.show', $post->slug) }}" class="uh-editorial-brief group">
                                    <span class="uh-editorial-brief-image">
                                        @if($cover = $post->featuredImage())
                                            <img src="{{ $cover->url(400) }}" alt="{{ $cover->alt() ?: $post->title }}" loading="lazy" decoding="async">
                                        @else
                                            <span class="uh-editorial-image-placeholder"></span>
                                        @endif
                                    </span>
                                    <span class="uh-editorial-brief-copy">
                                        <span class="uh-editorial-card-title">{{ $post->title }}</span>
                                        <span class="uh-editorial-date">{{ $post->publicationState?->published_at?->format('M d, Y') }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>

                <a href="{{ route('properties.index') }}" class="uh-editorial-promo group">
                    <span class="uh-editorial-promo-image" aria-hidden="true">
                        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1400&q=85" alt="" loading="lazy">
                    </span>
                    <span class="uh-editorial-promo-copy">
                        <span class="uh-editorial-overline">{{ __('Find your next address') }}</span>
                        <span class="uh-editorial-promo-title">{{ __('A better way to find your place.') }}</span>
                        <span class="uh-editorial-promo-link">{{ __('Explore properties') }} <x-icon name="arrow-right" class="size-4" /></span>
                    </span>
                </a>

                @foreach($showcaseCategories as $index => $category)
                    @php
                        $categoryPosts = $showcasePosts->get($category->id, collect());
                    @endphp
                    @if($categoryPosts->isNotEmpty())
                        <section class="uh-editorial-section" aria-labelledby="editorial-category-{{ $category->id }}">
                            <header class="uh-editorial-section-head">
                                <div>
                                    <p class="uh-editorial-overline">{{ __('From the journal') }} · {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</p>
                                    <h2 id="editorial-category-{{ $category->id }}">{{ $category->name }} {{ __('News') }}</h2>
                                    <p>{{ __('Fresh reporting, local knowledge and thoughtful ideas.') }}</p>
                                </div>
                                <a href="{{ route('articles.index', ['category' => $category->slug]) }}" class="uh-editorial-view-all">
                                    {{ __('View all') }} <x-icon name="arrow-right" class="size-4" />
                                </a>
                            </header>

                            @php
                                $usesSplitLayout = $index % 4 === 1 || $index % 4 === 3;
                            @endphp
                            @if($usesSplitLayout)
                                @php
                                    $leadPost = $index % 4 === 1 ? $categoryPosts->first() : $categoryPosts->last();
                                    $supportingPosts = $index % 4 === 1
                                        ? $categoryPosts->slice(1, 3)
                                        : $categoryPosts->take(max(0, $categoryPosts->count() - 1));
                                @endphp
                                <div @class(['uh-editorial-split', 'is-reversed' => $index % 4 === 3])>
                                    <a href="{{ route('articles.show', $leadPost->slug) }}" class="uh-editorial-category-lead group">
                                        @if($cover = $leadPost->featuredImage())
                                            <img src="{{ $cover->url(1000) }}" alt="{{ $cover->alt() ?: $leadPost->title }}" loading="lazy" decoding="async">
                                        @else
                                            <span class="uh-editorial-image-placeholder"></span>
                                        @endif
                                        <span class="uh-editorial-scrim"></span>
                                        <span class="uh-editorial-category-lead-copy">
                                            <span class="uh-editorial-kicker">{{ $category->name }}</span>
                                            <span class="uh-editorial-category-lead-title">{{ $leadPost->title }}</span>
                                            <span class="uh-editorial-date">{{ $leadPost->publicationState?->published_at?->format('M d, Y') }}</span>
                                        </span>
                                    </a>
                                    <div class="uh-editorial-story-list">
                                        @foreach($supportingPosts as $post)
                                            <a href="{{ route('articles.show', $post->slug) }}" class="uh-editorial-brief group">
                                                <span class="uh-editorial-brief-image">
                                                    @if($cover = $post->featuredImage())
                                                        <img src="{{ $cover->url(480) }}" alt="{{ $cover->alt() ?: $post->title }}" loading="lazy" decoding="async">
                                                    @else
                                                        <span class="uh-editorial-image-placeholder"></span>
                                                    @endif
                                                </span>
                                                <span class="uh-editorial-brief-copy">
                                                    <span class="uh-editorial-kicker">{{ $post->category?->name }}</span>
                                                    <span class="uh-editorial-card-title">{{ $post->title }}</span>
                                                    <span class="uh-editorial-date">{{ $post->publicationState?->published_at?->format('M d, Y') }}</span>
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(! $usesSplitLayout)
                                <div class="uh-editorial-card-grid">
                                    @foreach($categoryPosts->take(4) as $post)
                                        <a href="{{ route('articles.show', $post->slug) }}" class="uh-editorial-card group">
                                            <span class="uh-editorial-card-image">
                                                @if($cover = $post->featuredImage())
                                                    <img src="{{ $cover->url(768) }}" alt="{{ $cover->alt() ?: $post->title }}" loading="lazy" decoding="async">
                                                @else
                                                    <span class="uh-editorial-image-placeholder"></span>
                                                @endif
                                            </span>
                                            <span class="uh-editorial-card-copy">
                                                <span class="uh-editorial-kicker">{{ $post->category?->name }}</span>
                                                <span class="uh-editorial-card-title">{{ $post->title }}</span>
                                                <span class="uh-editorial-date">{{ $post->publicationState?->published_at?->format('M d, Y') }}</span>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif
                @endforeach

                @if($posts->hasPages())
                    <div class="uh-pagination-wrap">{{ $posts->onEachSide(1)->links() }}</div>
                @endif
            @else
                <section class="uh-editorial-listing-grid" aria-label="{{ __('Articles') }}">
                    @foreach($posts as $post)
                        <a href="{{ route('articles.show', $post->slug) }}" class="uh-editorial-card group">
                            <span class="uh-editorial-card-image">
                                @if($cover = $post->featuredImage())
                                    <img src="{{ $cover->url(768) }}" alt="{{ $cover->alt() ?: $post->title }}" loading="lazy" decoding="async">
                                @else
                                    <span class="uh-editorial-image-placeholder"></span>
                                @endif
                            </span>
                            <span class="uh-editorial-card-copy">
                                <span class="uh-editorial-kicker">{{ $post->category?->name }}</span>
                                <span class="uh-editorial-card-title">{{ $post->title }}</span>
                                @if($post->excerpt)
                                    <span class="uh-editorial-card-excerpt">{{ $post->excerpt }}</span>
                                @endif
                                <span class="uh-editorial-date">{{ $post->publicationState?->published_at?->format('M d, Y') }}</span>
                            </span>
                        </a>
                    @endforeach
                </section>

                @if($posts->hasPages())
                    <div class="uh-pagination-wrap">{{ $posts->onEachSide(1)->links() }}</div>
                @endif
            @endif
        @else
            <x-ui.empty icon="document" :title="__('No articles published yet')"
                        :description="__('Guides from our team will appear here. In the meantime, browse our properties or ask us a question.')">
                <x-ui.pill-link variant="light" :href="route('properties.index')">{{ __('Explore properties') }}</x-ui.pill-link>
                <x-ui.pill-link variant="light" :href="route('faq')">{{ __('Read the FAQ') }}</x-ui.pill-link>
            </x-ui.empty>
        @endif
    </main>
@endsection
