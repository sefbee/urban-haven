<section class="uh-section uh-band-paper uh-home-editorial" aria-labelledby="articles-title">
    <div class="uh-container">
        
        <!-- Section Header (Full Width) -->
        <header class="flex items-center justify-between mb-8" data-reveal>
            <h2 id="articles-title" class="text-2xl font-bold text-slate-900">{{ $sectionContent['title'] }}</h2>
            <a href="{{ route('articles.index') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-white hover:bg-slate-800 transition shadow-sm" aria-label="{{ __('View all articles') }}">
                <x-icon name="arrow-up-right" class="size-4" />
            </a>
        </header>

        <div class="flex flex-col gap-10">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 grid-flow-dense lg:grid-rows-[repeat(4,180px)] sm:auto-rows-[180px]" data-reveal>
                    @foreach($articles->take(9) as $post)
                        @php
                            $cover = $post->featuredImage();
                            $span = match($loop->index) {
                                0 => 'sm:col-span-2 sm:row-span-2',
                                1, 4, 7, 8 => 'sm:col-span-2 sm:row-span-1',
                                default => 'sm:col-span-1 sm:row-span-1',
                            };
                        @endphp
                        <a href="{{ route('articles.show', $post->slug) }}" class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md {{ $span }}">
                            <div class="flex-1 w-full overflow-hidden bg-gray-100 min-h-[80px]">
                                @if($cover)
                                    <img src="{{ $cover->url(768) }}" srcset="{{ $cover->srcset() }}" alt="{{ $cover->alt() ?: $post->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <span class="uh-media-placeholder flex h-full w-full items-center justify-center p-4 text-center text-xs leading-tight">{{ $post->category?->name ?? __('Urban Haven') }}</span>
                                @endif
                            </div>
                            <div class="flex flex-col p-3 shrink-0 bg-white">
                                <p class="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500 mb-1">{{ $post->category?->name ?? __('Journal') }}</p>
                                <h3 class="text-sm font-bold leading-snug text-slate-900 transition group-hover:text-primary-600 line-clamp-1">{{ $post->title }}</h3>
                                <p class="mt-1 text-[0.7rem] leading-relaxed text-slate-500 line-clamp-1 sm:line-clamp-2 hidden sm:block">{{ $post->excerpt ?? Str::limit(strip_tags($post->body), 80) }}</p>
                                <time class="mt-2 text-[0.65rem] text-slate-400" datetime="{{ $post->publicationState?->published_at?->toDateString() }}">{{ $post->publicationState?->published_at?->format('M d, Y') }}</time>
                            </div>
                        </a>
                    @endforeach
            </div>

            @if($propertyVideos->isNotEmpty())
                <div>
                    <header class="flex items-center justify-between mb-8" data-reveal>
                        <h2 id="property-tv-title" class="text-2xl font-bold text-slate-900">{{ $sectionContent['videos_title'] }}</h2>
                        <a href="{{ route('properties.index') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-white hover:bg-slate-800 transition shadow-sm" aria-label="{{ __('View properties') }}">
                            <x-icon name="arrow-up-right" class="size-4" />
                        </a>
                    </header>

                    <div class="flex flex-col gap-6">
                        @foreach($propertyVideos as $property)
                            <a class="flex items-center gap-4 group" href="{{ route('properties.show', $property->slug) }}" data-reveal style="--uh-i: {{ $loop->index }}">
                                <span class="block w-28 aspect-video flex-shrink-0 relative overflow-hidden rounded shadow-sm bg-gray-100">
                                    @if($image = $property->featuredImage())
                                        <img src="{{ $image->url(480) }}" alt="" loading="lazy" decoding="async" class="object-cover w-full h-full transition duration-300 group-hover:scale-105">
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
