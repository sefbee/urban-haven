<section class="uh-section uh-band-paper" aria-labelledby="articles-title">
    <div class="uh-container">
        <header class="uh-section-head" data-reveal>
            <h2 id="articles-title" class="uh-h2">{{ __('Latest Articles & Blog') }}</h2>
            <p class="uh-lede">{{ __('Practical notes on buying, renting and investing in property from the Urban Haven team.') }}</p>
        </header>

        <div class="uh-article-grid">
            @foreach($articles as $post)
                @php($cover = $post->featuredImage())
                <article class="uh-article" data-reveal style="--uh-i: {{ $loop->index }}">
                    <div class="uh-article-frame">
                        @if($cover)
                            <img src="{{ $cover->url(768) }}" srcset="{{ $cover->srcset() }}"
                                 sizes="(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw"
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
                        <h3 class="uh-article-title">
                            <a href="{{ route('articles.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>
                        @if($post->excerpt)
                            <p class="uh-article-excerpt">{{ $post->excerpt }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="uh-section-foot">
            <x-ui.pill-link :href="route('articles.index')">{{ __('Read all articles') }}</x-ui.pill-link>
        </div>
    </div>
</section>
