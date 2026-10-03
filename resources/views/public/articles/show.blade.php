@extends('layouts.public')

@php($cover = $post->featuredImage())

@section('content')
    @if($isPreview ?? false)
        <div class="bg-warn/15 py-2 text-center text-xs font-semibold text-ink" role="status">
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
                @if($post->category)
                    <p class="uh-home-kicker mt-4">{{ $post->category->name }}</p>
                @endif
                <h1 class="uh-home-title mt-2">{{ $post->title }}</h1>
                <p class="mt-3 text-sm text-[var(--color-muted)]">
                    {{ $post->author_label ?: \App\Models\Setting::get('company_name', 'Urban Haven') }}
                    @if($post->publicationState?->published_at)
                        <span aria-hidden="true"> · </span>
                        <time datetime="{{ $post->publicationState->published_at->toAtomString() }}">{{ $post->publicationState->published_at->timezone(config('urbanhaven.display_timezone'))->format('j M Y') }}</time>
                    @endif
                </p>
            </div>
        </header>

        <div class="uh-container-narrow uh-section-tight">
            @if($cover)
                <img src="{{ $cover->url(1280) }}" srcset="{{ $cover->srcset() }}" sizes="(min-width: 768px) 720px, 100vw"
                     alt="{{ $cover->alt(app()->getLocale()) }}" fetchpriority="high" decoding="async" class="mb-8 aspect-16/9 w-full rounded-xl object-cover">
            @endif
            <div class="uh-prose">{!! \Stevebauman\Purify\Facades\Purify::clean((string) $post->body) !!}</div>

            <div class="mt-12 rounded-xl bg-sand px-5 py-6">
                <p class="uh-h4">{{ __('Looking for a property?') }}</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Search properties') }}</a>
                    <a class="uh-btn-outline uh-btn-sm" href="{{ route('cms.show', 'contact') }}">{{ __('Talk to our team') }}</a>
                </div>
            </div>
        </div>
    </article>

    @if($related->isNotEmpty())
        <section class="border-t border-line bg-paper">
            <div class="uh-container uh-section-tight">
                <h2 class="uh-h2">{{ __('Related articles') }}</h2>
                <ul class="mt-6 grid gap-4 sm:grid-cols-3">
                    @foreach($related as $item)
                        <li class="uh-panel">
                            <a class="font-semibold transition hover:text-forest" href="{{ route('articles.show', $item->slug) }}">{{ $item->title }}</a>
                            @if($item->excerpt)
                                <p class="mt-2 line-clamp-2 text-sm text-[var(--color-muted)]">{{ $item->excerpt }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
