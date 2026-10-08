<section class="uh-section uh-band-paper uh-home-editorial" aria-labelledby="articles-title">
    <div class="uh-container">
        <div class="uh-home-editorial-grid">
            <div class="uh-home-news">
                <header class="uh-home-editorial-head" data-reveal>
                    <h2 id="articles-title" class="uh-h2">{{ __('News & Blog Updates') }}</h2>
                    <x-ui.pill-link variant="dark" :href="route('articles.index')">
                        {{ __('View all') }}
                    </x-ui.pill-link>
                </header>

                <div class="uh-article-grid">
                    @foreach($articles as $post)
                        @php($cover = $post->featuredImage())
                        <article class="uh-article uh-article-compact" data-reveal style="--uh-i: {{ $loop->index }}">
                            <a class="uh-article-frame" href="{{ route('articles.show', $post->slug) }}" tabindex="-1" aria-hidden="true">
                                @if($cover)
                                    <img src="{{ $cover->url(480) }}" srcset="{{ $cover->srcset() }}"
                                         sizes="(min-width: 1024px) 6rem, 5rem"
                                         alt="" loading="lazy" decoding="async">
                                @else
                                    <span class="uh-media-placeholder">{{ $post->category?->name ?? __('Urban Haven') }}</span>
                                @endif
                            </a>
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
                                    <a href="{{ route('articles.show', $post->slug) }}"><span>{{ $post->title }}</span></a>
                                </h3>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            @if($propertyVideos->isNotEmpty())
                <aside class="uh-home-property-tv" aria-labelledby="property-tv-title">
                    <header class="uh-home-editorial-head" data-reveal>
                        <h2 id="property-tv-title" class="uh-h2">{{ __('Property TV') }}</h2>
                        <a class="uh-icon-action uh-home-tv-more" href="{{ route('properties.index') }}" aria-label="{{ __('View properties') }}">
                            <x-icon name="chevron-right" class="size-4" />
                        </a>
                    </header>

                    <div class="uh-home-tv-list">
                        @foreach($propertyVideos as $property)
                            <a class="uh-home-tv-item" href="{{ route('properties.show', $property->slug) }}" data-reveal style="--uh-i: {{ $loop->index }}">
                                <span class="uh-home-tv-thumb">
                                    @if($image = $property->featuredImage())
                                        <img src="{{ $image->url(480) }}" alt="" loading="lazy" decoding="async">
                                    @else
                                        <span class="uh-media-placeholder">{{ __('Property video') }}</span>
                                    @endif
                                    <span class="uh-home-tv-play"><x-icon name="play" class="size-4" /></span>
                                </span>
                                <span class="uh-home-tv-copy">
                                    <span class="uh-home-tv-label">{{ __('Property video') }}</span>
                                    <span class="uh-home-tv-title">{{ $property->title }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </aside>
            @endif
        </div>

    </div>
</section>
