<section class="uh-section uh-band-paper uh-home-editorial" aria-labelledby="articles-title">
    <div class="uh-container">
        
        <!-- Section Header (Full Width) -->
        <header class="flex items-center justify-between mb-8" data-reveal>
            <h2 id="articles-title" class="text-2xl font-bold text-slate-900">{{ $sectionContent['title'] }}</h2>
            <a href="{{ route('articles.index') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-white hover:bg-slate-800 transition shadow-sm" aria-label="{{ __('View all articles') }}">
                <x-icon name="arrow-up-right" class="size-4" />
            </a>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <!-- Left Side: Articles -->
            <div class="lg:col-span-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6" data-reveal>
                    @foreach($articles as $post)
                        @php($cover = $post->featuredImage())
                        <a href="{{ route('articles.show', $post->slug) }}" class="flex items-center gap-4 group">
                            <div class="h-[5.5rem] w-[8.5rem] flex-shrink-0 overflow-hidden rounded-md shadow-sm bg-gray-100">
                                @if($cover)
                                    <img src="{{ $cover->url(480) }}" srcset="{{ $cover->srcset() }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <span class="uh-media-placeholder h-full w-full flex items-center justify-center text-xs text-center p-1 leading-tight">{{ $post->category?->name ?? __('Urban Haven') }}</span>
                                @endif
                            </div>
                            <div class="flex flex-col justify-center flex-1 py-1">
                                <h3 class="text-[15px] font-bold text-slate-900 leading-snug group-hover:text-primary-600 transition line-clamp-2">{{ $post->title }}</h3>
                                <p class="mt-1 text-[13px] text-slate-500 line-clamp-2">{{ $post->excerpt ?? Str::limit(strip_tags($post->body), 100) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Right Side: Property TV -->
            @if($propertyVideos->isNotEmpty())
                <div class="lg:col-span-4">
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
