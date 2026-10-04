@extends('layouts.public')

@section('content')
    <header class="uh-page-head">
        <div class="uh-container">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Articles'), 'url' => $activeCategory ? route('articles.index') : null],
                $activeCategory ? ['label' => $activeCategory->name] : null,
            ]" />
            <h1 class="uh-h1 uh-page-title">{{ $activeCategory?->name ?? __('Guides and articles') }}</h1>
            <p class="uh-lede">{{ __('Practical notes on buying, renting and investing in property from the Urban Haven team.') }}</p>

            @if($categories->isNotEmpty())
                <nav class="uh-text-toggle uh-stage-filter" aria-label="{{ __('Article categories') }}">
                    <a href="{{ route('articles.index') }}" @class(['is-on' => ! $activeCategory]) @if(! $activeCategory) aria-current="page" @endif>{{ __('All') }}</a>
                    @foreach($categories as $category)
                        <a href="{{ route('articles.index', ['category' => $category->slug]) }}"
                           @class(['is-on' => $activeCategory?->is($category)])
                           @if($activeCategory?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
    </header>

    <div class="uh-container uh-section-tight pt-0">
        @if($posts->isNotEmpty())
            <div class="uh-article-grid">
                @foreach($posts as $post)
                    @php($cover = $post->featuredImage())
                    @php($lead = $loop->first && $posts->onFirstPage() && $posts->count() > 2)
                    <article @class(['uh-article', 'is-lead' => $lead]) data-reveal style="--uh-i: {{ $loop->index % 3 }}">
                        <div class="uh-article-frame">
                            @if($cover)
                                <img src="{{ $cover->url($lead ? 1280 : 768) }}" srcset="{{ $cover->srcset() }}"
                                     sizes="{{ $lead ? '(min-width: 1024px) 720px, 100vw' : '(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw' }}"
                                     alt="{{ $cover->alt(app()->getLocale()) }}" loading="lazy" decoding="async">
                            @endif
                        </div>
                        <div class="uh-article-body">
                            <p class="uh-article-meta">
                                @if($post->category)
                                    <span>{{ $post->category->name }}</span>
                                @endif
                                @if($post->publicationState?->published_at)
                                    <time datetime="{{ $post->publicationState->published_at->toAtomString() }}">{{ $post->publicationState->published_at->timezone(config('urbanhaven.display_timezone'))->format('j M Y') }}</time>
                                @endif
                            </p>
                            <h2 class="uh-article-title">
                                <a href="{{ route('articles.show', $post->slug) }}">{{ $post->title }}</a>
                            </h2>
                            @if($post->excerpt)
                                <p class="uh-article-excerpt">{{ $post->excerpt }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if($posts->hasPages())
                <div class="uh-pagination-wrap">{{ $posts->onEachSide(1)->links() }}</div>
            @endif
        @else
            <x-ui.empty icon="document" :title="__('No articles published yet')"
                        :description="__('Guides from our team will appear here. In the meantime, browse our properties or ask us a question.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Explore properties') }}</a>
                <a class="uh-btn-secondary uh-btn-sm" href="{{ route('faq') }}">{{ __('Read the FAQ') }}</a>
            </x-ui.empty>
        @endif
    </div>
@endsection
