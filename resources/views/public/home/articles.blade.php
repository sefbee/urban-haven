<section class="uh-section uh-home-editorial bg-slate-50" aria-labelledby="articles-title">
    <div class="uh-container">
        @php
            $articlesTitle = $sectionContent['title'] ?: __('Latest Articles & Blog');
            $articlesSubtitle = $sectionContent['subtitle'] ?: __('Practical notes on buying, renting, and investing in property from the Urban Haven research team.');
            $articlesLinkLabel = $sectionContent['link_label'] ?: __('Read all articles');
        @endphp
        <header class="uh-home-articles-head mb-7 flex flex-col items-start justify-between gap-5 md:flex-row md:items-end" data-reveal>
            <div class="max-w-4xl">
                <p class="mb-1.5 text-xs font-bold uppercase tracking-[0.25em] text-blue-900">{{ __('Our Insights') }}</p>
                <h2 id="articles-title" class="text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">{{ $articlesTitle }}</h2>
                <p class="mt-2 text-base leading-relaxed text-slate-600 sm:text-lg">{{ $articlesSubtitle }}</p>
            </div>
            <a class="uh-home-mobile-section-action" href="{{ route('articles.index') }}">{{ $articlesLinkLabel }}</a>
            <x-ui.pill-link class="uh-home-desktop-section-action shrink-0" :href="route('articles.index')">{{ $articlesLinkLabel }}</x-ui.pill-link>
        </header>

        <div class="flex flex-col gap-10">
            <div class="uh-home-mobile-rail" x-data="uhRail()">
                <button type="button" class="uh-housing-arrow uh-home-mobile-arrow is-prev" aria-label="{{ __('Previous articles') }}" x-show="canPrev" x-cloak @click="prev()">
                    <x-icon name="chevron-left" class="size-5" />
                </button>
                <button type="button" class="uh-housing-arrow uh-home-mobile-arrow is-next" aria-label="{{ __('Next articles') }}" x-show="canNext" x-cloak @click="next()">
                    <x-icon name="chevron-right" class="size-5" />
                </button>
                <div class="grid grid-cols-1 items-stretch gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 uh-home-mobile-track" data-reveal x-ref="scroller" tabindex="0" aria-label="{{ __('Latest articles') }}">
                @foreach($articles->take(10) as $post)
                    @php
                        $cover = $post->featuredImage();
                    @endphp
                    <a href="{{ route('articles.show', $post->slug) }}" class="uh-home-mobile-item group flex h-full min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                        <div class="aspect-[16/9] w-full shrink-0 overflow-hidden bg-slate-200">
                            @if($cover)
                                <img src="{{ $cover->url(480) }}" srcset="{{ $cover->srcset() }}" alt="{{ $cover->alt() ?: $post->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <span class="uh-media-placeholder flex h-full w-full items-center justify-center p-3 text-center text-sm leading-tight">{{ $post->category?->name ?? __('Urban Haven') }}</span>
                            @endif
                        </div>
                        <div class="flex min-h-40 grow flex-col p-4 sm:p-5">
                            <p class="mb-2 w-fit max-w-full truncate rounded-full bg-blue-50 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide text-blue-800">{{ $post->category?->name ?? __('Journal') }}</p>
                            <h3 class="line-clamp-2 text-base font-bold leading-snug text-slate-950 transition group-hover:text-blue-800">{{ $post->title }}</h3>
                            <span class="mt-auto flex items-center justify-between gap-2 pt-4">
                                <time class="text-xs text-slate-500" datetime="{{ $post->publicationState?->published_at?->toDateString() }}">{{ $post->publicationState?->published_at?->format('M d, Y') }}</time>
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full border border-slate-300 text-slate-800 transition group-hover:border-slate-900 group-hover:bg-slate-900 group-hover:text-white" aria-hidden="true">
                                    <x-icon name="arrow-up-right" class="size-4" />
                                </span>
                            </span>
                        </div>
                    </a>
                @endforeach
                </div>
            </div>

            @if($propertyVideos->isNotEmpty())
                <div>
                    @if(filled($sectionContent['videos_title']))
                        <header class="uh-home-video-head mb-8 flex items-center justify-between" data-reveal>
                            <h2 id="property-tv-title" class="text-2xl font-bold text-slate-900">{{ $sectionContent['videos_title'] }}</h2>
                            <a class="uh-home-mobile-section-action" href="{{ route('properties.index') }}">{{ __('View properties') }}</a>
                            <a href="{{ route('properties.index') }}" class="uh-home-desktop-section-action flex size-10 items-center justify-center rounded-full bg-slate-900 text-white shadow-sm transition hover:bg-slate-800" aria-label="{{ __('View properties') }}">
                                <x-icon name="arrow-up-right" class="size-4" />
                            </a>
                        </header>
                    @endif

                    <div class="flex flex-col gap-6">
                        @foreach($propertyVideos as $property)
                            <a class="flex items-center gap-4 group" href="{{ route('properties.show', $property->slug) }}" data-reveal style="--uh-i: {{ $loop->index }}">
                                <span class="block w-28 aspect-video flex-shrink-0 relative overflow-hidden rounded shadow-sm bg-gray-100">
                                    @if($image = $property->featuredImage())
                                        <img src="{{ $image->url(480) }}" alt="{{ $property->title ?? $sectionContent['title'] ?? 'Property image' }}" loading="lazy" decoding="async" class="object-cover w-full h-full transition duration-300 group-hover:scale-105">
                                    @else
                                        <span class="uh-media-placeholder w-full h-full flex items-center justify-center text-[0.6rem]">{{ __('Video') }}</span>
                                    @endif
                                    <span class="absolute inset-0 flex items-center justify-center">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-white text-white">
                                            <x-icon name="play" class="size-3 ml-0.5" />
                                        </span>
                                    </span>
                                </span>
                                <span class="flex flex-col flex-1 py-1">
                                    <span class="text-[0.65rem] font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Property video') }}</span>
                                    <span class="text-sm font-bold text-slate-900 leading-snug group-hover:text-primary-600 transition line-clamp-2">{{ $property->title }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</section>
