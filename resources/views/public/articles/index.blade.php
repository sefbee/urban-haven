@extends('layouts.public')

@php
    $articlesSections = \App\Support\ArticlesPageSections::visible();
    $masthead = $articlesSections['masthead'] ?? null;
    $categorySection = $articlesSections['categories'] ?? null;
    $latestSection = $articlesSections['latest'] ?? null;
@endphp

@section('content')
    <main class="uh-container uh-articles-page">
        @if($masthead)
            <header class="uh-journal-hero">
                <div class="uh-journal-hero-copy">
                    @if(filled($masthead['eyebrow'] ?? null))<p class="uh-editorial-overline">{{ $masthead['eyebrow'] }}</p>@endif
                    <h1 class="uh-editorial-title">{{ $activeCategory?->name ?? ($masthead['title'] ?? '') }}</h1>
                    @if(filled($masthead['intro'] ?? null))<p class="uh-editorial-intro">{{ $masthead['intro'] }}</p>@endif
                    <div class="uh-journal-hero-actions">
                        <a class="uh-journal-primary" href="{{ $masthead['cta_url'] ?? '#articles-grid' }}">{{ $masthead['cta_label'] ?? __('Explore Articles') }} <x-icon name="arrow-right" class="size-4" /></a>
                        @if(filled($masthead['video_label'] ?? null) && filled($masthead['video_url'] ?? null))
                            <a class="uh-journal-video" href="{{ $masthead['video_url'] }}"><span aria-hidden="true">▶</span>{{ $masthead['video_label'] }}</a>
                        @endif
                    </div>
                </div>
                @if($heroImage)<span class="uh-journal-hero-photo" aria-hidden="true"><img src="{{ $heroImage->url(1920) }}" alt="" fetchpriority="high" decoding="async" width="1920" height="1080"></span>@endif
            </header>
        @endif

        <section class="uh-journal-toolbar" aria-label="{{ __('Browse articles') }}">
            <nav class="uh-editorial-categories" aria-label="{{ __('Article categories') }}">
                <a href="{{ route('articles.index', request()->query('q') ? ['q' => request()->query('q')] : []) }}" @class(['is-active' => ! $activeCategory])>{{ __('All Articles') }}</a>
                @foreach($categories as $category)
                    <a href="{{ route('articles.index', array_filter(['category' => $category->slug, 'q' => $search ?: null])) }}" @class(['is-active' => $activeCategory?->is($category)])>{{ __($category->name) }}</a>
                @endforeach
            </nav>
        </section>

        @if(! $activeCategory && $search === '' && ($featuredPost || $latestArticles->isNotEmpty()))
            <section @class(['uh-journal-feature', 'has-featured' => $featuredPost]) aria-label="{{ __('Featured and latest articles') }}">
                @if($featuredPost)
                    <a href="{{ route('articles.show', $featuredPost->slug) }}" class="uh-journal-featured group">
                        @if($cover = $featuredPost->featuredImage())
                            <img src="{{ $cover->url(1400) }}" alt="{{ $cover->alt() ?: $featuredPost->title }}" fetchpriority="high" width="1400" height="920">
                        @else
                            <span class="uh-editorial-image-placeholder"></span>
                        @endif
                        <span class="uh-editorial-scrim"></span>
                        <span class="uh-journal-featured-copy">
                            <span class="uh-journal-badge">{{ __('Featured') }}</span>
                            @if($featuredPost->category)<span class="uh-editorial-kicker">{{ $featuredPost->category->name }}</span>@endif
                            <span class="uh-journal-featured-title">{{ $featuredPost->title }}</span>
                            @if($featuredPost->excerpt)<span class="uh-journal-excerpt">{{ $featuredPost->excerpt }}</span>@endif
                            <span class="uh-journal-meta">{{ $featuredPost->publicationState?->published_at?->format('M d, Y') }} <span aria-hidden="true">·</span> {{ max(1, (int) ceil(str_word_count(strip_tags($featuredPost->body ?? '')) / 200)) }} {{ __('min read') }}</span>
                            <span class="uh-journal-arrow" aria-hidden="true"><x-icon name="arrow-right" class="size-5" /></span>
                        </span>
                    </a>
                @endif

                @if($latestArticles->isNotEmpty())
                    <div class="uh-journal-latest">
                        <header class="uh-journal-section-heading">
                            <h2>{{ $latestSection['title'] ?? __('Latest Articles') }}</h2>
                            <a href="#articles-grid">{{ __('View all') }} <x-icon name="arrow-right" class="size-4" /></a>
                        </header>
                        <div class="uh-journal-latest-list">
                            @foreach($latestArticles as $post)
                                <a href="{{ route('articles.show', $post->slug) }}" class="uh-journal-latest-item group">
                                    <span class="uh-journal-thumb">
                                        @if($cover = $post->featuredImage())<img src="{{ $cover->url(420) }}" alt="{{ $cover->alt() ?: $post->title }}" loading="lazy" decoding="async" width="420" height="300">@endif
                                    </span>
                                    <span class="uh-journal-latest-copy">
                                        @if($post->category)<span class="uh-editorial-kicker">{{ $post->category->name }}</span>@endif
                                        <span class="uh-journal-latest-title">{{ $post->title }}</span>
                                        <span class="uh-journal-meta">{{ $post->publicationState?->published_at?->format('M d, Y') }} <span aria-hidden="true">·</span> {{ max(1, (int) ceil(str_word_count(strip_tags($post->body ?? '')) / 200)) }} {{ __('min read') }}</span>
                                    </span>
                                    <span class="uh-journal-item-arrow" aria-hidden="true"><x-icon name="arrow-right" class="size-4" /></span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        @endif

        @if($categorySection && ! $activeCategory && $search === '' && $categories->isNotEmpty())
            <section class="uh-journal-categories" aria-labelledby="journal-categories-title">
                <header class="uh-journal-section-heading">
                    <div><p class="uh-editorial-overline">{{ __('Our categories') }}</p><h2 id="journal-categories-title">{{ $categorySection['title'] ?? __('Explore by category') }}</h2></div>
                </header>
                <div class="uh-journal-category-grid">
                    @foreach($categories->take((int) ($categorySection['limit'] ?? 6)) as $category)
                        @php($categoryCover = $category->posts->first()?->featuredImage())
                        <a href="{{ route('articles.index', ['category' => $category->slug]) }}" class="uh-journal-category-card group">
                            <span class="uh-journal-category-image">
                                @if($categoryCover)<img src="{{ $categoryCover->url(720) }}" alt="{{ $categoryCover->alt() ?: __($category->name) }}" loading="lazy" decoding="async" width="720" height="440">@endif
                            </span>
                            <span class="uh-journal-category-copy"><span>{{ __($category->name) }}</span><small>{{ trans_choice(':count article|:count articles', $category->published_posts_count, ['count' => $category->published_posts_count]) }}</small><span class="uh-journal-item-arrow" aria-hidden="true"><x-icon name="arrow-right" class="size-4" /></span></span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if(isset($articlesSections['promo']))
            @php($promoImage = \App\Models\CmsBlock::imagesFor('articles_promo')->first())
            <a href="{{ $articlesSections['promo']['cta_url'] ?? route('properties.index') }}" @class(['uh-editorial-promo group', 'is-text-only' => ! $promoImage])>
                @if($promoImage)<span class="uh-editorial-promo-image" aria-hidden="true"><img src="{{ $promoImage->url(1400) }}" alt="" loading="lazy" width="1400" height="800"></span>@endif
                <span class="uh-editorial-promo-copy">
                    <span class="uh-editorial-overline">{{ $articlesSections['promo']['eyebrow'] ?? '' }}</span>
                    <span class="uh-editorial-promo-title">{{ $articlesSections['promo']['title'] ?? '' }}</span>
                    <span class="uh-editorial-promo-link">{{ $articlesSections['promo']['cta_label'] ?? '' }} <x-icon name="arrow-right" class="size-4" /></span>
                </span>
            </a>
        @endif

        <section id="articles-grid" class="uh-journal-results" aria-labelledby="journal-results-title">
            <header class="uh-journal-section-heading">
                <div><p class="uh-editorial-overline">{{ $activeCategory?->name ?? ($search !== '' ? __('Search results') : __('The journal')) }}</p><h2 id="journal-results-title">{{ $activeCategory?->name ?? ($search !== '' ? __('Articles matching “:term”', ['term' => $search]) : __('All Articles')) }}</h2></div>
                <span class="uh-journal-result-count">{{ trans_choice(':count story|:count stories', $posts->total(), ['count' => $posts->total()]) }}</span>
            </header>

            @if($posts->isNotEmpty())
                <div class="uh-journal-result-grid">
                    @foreach($posts as $post)
                        <a href="{{ route('articles.show', $post->slug) }}" class="uh-journal-result-card group">
                            <span class="uh-journal-result-image">@if($cover = $post->featuredImage())<img src="{{ $cover->url(800) }}" alt="{{ $cover->alt() ?: $post->title }}" loading="lazy" decoding="async" width="800" height="540">@endif</span>
                            <span class="uh-journal-result-copy">
                                @if($post->category)<span class="uh-editorial-kicker">{{ $post->category->name }}</span>@endif
                                <span class="uh-journal-result-title">{{ $post->title }}</span>
                                @if($post->excerpt)<span class="uh-journal-result-excerpt">{{ $post->excerpt }}</span>@endif
                                <span class="uh-journal-meta">{{ $post->publicationState?->published_at?->format('M d, Y') }} <span aria-hidden="true">·</span> {{ max(1, (int) ceil(str_word_count(strip_tags($post->body ?? '')) / 200)) }} {{ __('min read') }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
                <div class="uh-journal-pagination">{{ $posts->links() }}</div>
            @else
                <x-ui.empty icon="document" :title="$search !== '' ? __('No matching articles') : __('No articles published in this category yet')" :description="__('Try another search or browse all of our published articles.')">
                    <x-ui.pill-link variant="light" :href="route('articles.index')">{{ __('Browse all articles') }}</x-ui.pill-link>
                </x-ui.empty>
            @endif
        </section>
    </main>
@endsection
