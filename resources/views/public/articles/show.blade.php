@extends('layouts.public')

@php($cover = $post->featuredImage())

@section('content')
    @if($isPreview ?? false)
        <div class="uh-preview-banner" role="status">
            {{ __('Preview — this shows unpublished changes and is not visible to the public.') }}
        </div>
    @endif

    <article>
        <header class="uh-page-head">
            <div class="uh-container-narrow">
                <x-ui.breadcrumbs :items="[
                    ['label' => __('Home'), 'url' => route('home')],
                    ['label' => __('Articles'), 'url' => route('articles.index')],
                    ['label' => $post->title],
                ]" />
                <h1 class="uh-h1 uh-page-title">{{ $post->title }}</h1>
                <p class="uh-article-meta mt-6">
                    @if($post->category)
                        <span>{{ $post->category->name }}</span>
                    @endif
                    <span>{{ $post->author_label ?: \App\Models\Setting::get('company_name', 'Urban Haven') }}</span>
                    @if($post->publicationState?->published_at)
                        <time datetime="{{ $post->publicationState->published_at->toAtomString() }}">{{ $post->publicationState->published_at->timezone(config('urbanhaven.display_timezone'))->format('j M Y') }}</time>
                    @endif
                </p>
            </div>
        </header>

        @if($cover)
            <figure class="uh-container">
                <div class="uh-article-cover">
                    <img src="{{ $cover->url(1920) }}" srcset="{{ $cover->srcset() }}" sizes="(min-width: 1280px) 1200px, 100vw"
                         alt="{{ $cover->alt(app()->getLocale()) }}" fetchpriority="high" decoding="async">
                </div>
            </figure>
        @endif

        <div class="uh-container-narrow uh-section-tight">
            <div class="uh-prose uh-prose-lead">{!! \Stevebauman\Purify\Facades\Purify::clean((string) $post->body) !!}</div>

            <aside class="uh-next">
                <p class="uh-h3">{{ __('Looking for a property?') }}</p>
                <div class="uh-next-links">
                    <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Explore properties') }}</a>
                    <a class="uh-arrow-link" href="{{ route('cms.show', 'contact') }}">{{ __('Talk to our team') }} <x-icon name="arrow-right" class="size-3.5" /></a>
                </div>
            </aside>
        </div>
    </article>

    @if($related->isNotEmpty())
        <section class="uh-band-paper" aria-labelledby="related-title">
            <div class="uh-container uh-section-tight">
                <h2 id="related-title" class="uh-h2">{{ __('Related articles') }}</h2>
                <ul class="uh-related">
                    @foreach($related as $item)
                        <li>
                            <a href="{{ route('articles.show', $item->slug) }}">{{ $item->title }}</a>
                            @if($item->excerpt)
                                <p>{{ $item->excerpt }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
