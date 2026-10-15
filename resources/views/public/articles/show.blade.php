@extends('layouts.public')

@php
    $cover = $post->featuredImage();
    $authorName = $post->author_label ?: \App\Models\Setting::get('company_name', 'Urban Haven');
    $publishedAt = $post->publicationState?->published_at;
    $readingMinutes = max(1, (int) ceil(str_word_count(strip_tags((string) $post->body)) / 220));
@endphp

@section('content')
    @if($isPreview ?? false)
        <div class="uh-preview-banner" role="status">
            {{ __('Preview — this shows unpublished changes and is not visible to the public.') }}
        </div>
    @endif

    <article class="uh-blog-detail">
        <header class="uh-blog-hero">
            @if($cover)
                <img class="uh-blog-hero-image" src="{{ $cover->url(1920) }}" srcset="{{ $cover->srcset() }}"
                     sizes="100vw" alt="{{ $cover->alt(app()->getLocale()) }}" fetchpriority="high" decoding="async">
            @endif
            <div class="uh-blog-hero-shade"></div>
            <div class="uh-blog-hero-inner uh-container">
                <a class="uh-blog-back" href="{{ route('articles.index') }}" aria-label="{{ __('Back to articles') }}">
                    <x-icon name="arrow-left" class="size-4" />
                </a>
                <div class="uh-blog-hero-copy">
                    @if($post->category)
                        <a class="uh-blog-category" href="{{ route('articles.index', ['category' => $post->category->slug]) }}">
                            <span aria-hidden="true"></span>{{ $post->category->name }}
                        </a>
                    @endif
                    <h1>{{ $post->title }}</h1>
                    @if($post->excerpt)
                        <p class="uh-blog-deck">{{ $post->excerpt }}</p>
                    @endif
                    <div class="uh-blog-byline">
                        <span class="uh-blog-author-mark" aria-hidden="true">{{ mb_substr($authorName, 0, 1) }}</span>
                        <span class="uh-blog-author"><strong>{{ __('By :author', ['author' => $authorName]) }}</strong><small>{{ __('Urban Haven Research Desk') }}</small></span>
                        <span class="uh-blog-byline-divider" aria-hidden="true"></span>
                        @if($publishedAt)
                            <time datetime="{{ $publishedAt->toAtomString() }}">{{ $publishedAt->timezone(config('urbanhaven.display_timezone'))->format('j M Y') }}</time>
                        @endif
                        <span class="uh-blog-read-time"><x-icon name="eye" class="size-4" />{{ trans_choice(':count min read|:count min read', $readingMinutes, ['count' => $readingMinutes]) }}</span>
                    </div>
                </div>
                <div class="uh-blog-share-wrap">
                    <x-share-menu :url="route('articles.show', $post->slug)" :title="$post->title" button-class="uh-blog-share" />
                </div>
            </div>
        </header>

        <div class="uh-blog-content uh-container">
            <div class="uh-blog-body">
                <div class="uh-prose uh-prose-lead">{!! \Stevebauman\Purify\Facades\Purify::clean((string) $post->body) !!}</div>
            </div>

            <aside class="uh-blog-sidebar" aria-label="{{ __('More from Urban Haven') }}">
                @if($related->isNotEmpty())
                    <section class="uh-blog-sidebar-card" aria-labelledby="related-title">
                        <div class="uh-blog-sidebar-heading">
                            <h2 id="related-title">{{ __('Related articles') }}</h2>
                            <a href="{{ route('articles.index') }}" aria-label="{{ __('Browse all articles') }}"><x-icon name="arrow-right" class="size-4" /></a>
                        </div>
                        <ul class="uh-blog-related">
                            @foreach($related as $item)
                                @php($relatedImage = $item->featuredImage())
                                <li>
                                    <a href="{{ route('articles.show', $item->slug) }}">
                                        @if($relatedImage)
                                            <img src="{{ $relatedImage->url(240) }}" alt="" loading="lazy" decoding="async">
                                        @else
                                            <span class="uh-blog-related-placeholder" aria-hidden="true"><x-icon name="document" class="size-5" /></span>
                                        @endif
                                        <span><strong>{{ $item->title }}</strong>@if($item->publicationState?->published_at)<small>{{ $item->publicationState->published_at->timezone(config('urbanhaven.display_timezone'))->format('j M Y') }}</small>@endif</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section class="uh-blog-promo">
                    <h2>{{ __('Looking for a property?') }}</h2>
                    <p>{{ __('Discover homes and areas across Dhaka with Urban Haven.') }}</p>
                    <a href="{{ route('properties.index') }}">{{ __('Browse properties') }} <x-icon name="arrow-right" class="size-4" /></a>
                </section>
            </aside>
        </div>
    </article>
@endsection
