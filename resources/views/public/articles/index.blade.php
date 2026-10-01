@extends('layouts.public')

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container py-8 md:py-10">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Articles'), 'url' => $activeCategory ? route('articles.index') : null],
                $activeCategory ? ['label' => $activeCategory->name] : null,
            ]" />
            <h1 class="uh-h1 mt-3">{{ $activeCategory?->name ?? __('Guides and articles') }}</h1>
            <p class="uh-lede mt-3 max-w-2xl">{{ __('Practical notes on buying, renting and investing in property from the Urban Haven team.') }}</p>

            @if($categories->isNotEmpty())
                <nav class="mt-6 flex flex-wrap gap-2" aria-label="{{ __('Article categories') }}">
                    <a href="{{ route('articles.index') }}" @class(['uh-chip', 'uh-chip-active' => ! $activeCategory]) @if(! $activeCategory) aria-current="page" @endif>{{ __('All') }}</a>
                    @foreach($categories as $category)
                        <a href="{{ route('articles.index', ['category' => $category->slug]) }}"
                           @class(['uh-chip', 'uh-chip-active' => $activeCategory?->is($category)])
                           @if($activeCategory?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>

    <div class="uh-container uh-section-tight">
        @if($posts->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($posts as $post)
                    @php($cover = $post->featuredImage())
                    <article class="uh-card uh-card-hover flex flex-col">
                        @if($cover)
                            <img src="{{ $cover->url(768) }}" srcset="{{ $cover->srcset() }}" sizes="(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw"
                                 alt="{{ $cover->alt(app()->getLocale()) }}" loading="lazy" decoding="async" class="aspect-16/9 w-full object-cover">
                        @endif
                        <div class="flex flex-1 flex-col p-5">
                            @if($post->category)
                                <p class="uh-eyebrow">{{ $post->category->name }}</p>
                            @endif
                            <h2 class="uh-h3 mt-2"><a class="transition hover:text-forest" href="{{ route('articles.show', $post->slug) }}">{{ $post->title }}</a></h2>
                            @if($post->excerpt)
                                <p class="mt-2 line-clamp-3 text-sm text-[var(--color-muted)]">{{ $post->excerpt }}</p>
                            @endif
                            @if($post->publicationState?->published_at)
                                <p class="mt-auto pt-4 text-xs text-[var(--color-muted)]">{{ $post->publicationState->published_at->timezone(config('urbanhaven.display_timezone'))->format('j M Y') }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if($posts->hasPages())
                <div class="mt-9">{{ $posts->onEachSide(1)->links() }}</div>
            @endif
        @else
            <x-ui.empty icon="document" :title="__('No articles published yet')"
                        :description="__('Guides from our team will appear here. In the meantime, browse our properties or ask us a question.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Search properties') }}</a>
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('faq') }}">{{ __('Read the FAQ') }}</a>
            </x-ui.empty>
        @endif
    </div>
@endsection
