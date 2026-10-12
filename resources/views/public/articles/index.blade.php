@extends('layouts.public')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16 font-sans">
        
        {{-- Header & Tags --}}
        <div class="mb-8">
            <h1 class="text-3xl lg:text-[2.5rem] font-bold text-gray-900 tracking-tight mb-6">
                {{ $activeCategory?->name ?? __('Explore News, Insights and Videos') }}
            </h1>

            @if($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('articles.index') }}" 
                       class="px-4 py-2 text-[11px] font-bold uppercase tracking-wider transition-colors {{ ! $activeCategory ? 'bg-[#156e40] text-white' : 'bg-gray-100 text-gray-600 hover:bg-[#156e40] hover:text-white' }}">
                        {{ __('All') }}
                    </a>
                    @foreach($categories as $category)
                        <a href="{{ route('articles.index', ['category' => $category->slug]) }}"
                           class="px-4 py-2 text-[11px] font-bold uppercase tracking-wider transition-colors {{ $activeCategory?->is($category) ? 'bg-[#156e40] text-white' : 'bg-gray-100 text-gray-600 hover:bg-[#156e40] hover:text-white' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if($posts->isNotEmpty())
            @if(! $activeCategory && $posts->onFirstPage() && $posts->count() >= 5)
                {{-- Featured Section (Mockup Clone) --}}
                @php
                    $mainPost = $posts->first();
                    $topRightPosts = $posts->slice(1, 2);
                    $bottomRightPosts = $posts->slice(3, 4);
                @endphp
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-16">
                    {{-- Left: Main Featured --}}
                    @if($mainPost)
                        <a href="{{ route('articles.show', $mainPost->slug) }}" class="group relative block h-[400px] lg:h-[600px] rounded-sm overflow-hidden">
                            @if($mainCover = $mainPost->featuredImage())
                                <img src="{{ $mainCover->url(1280) }}" alt="{{ $mainPost->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 lg:p-8 text-white">
                                <h2 class="text-2xl lg:text-[2rem] leading-tight font-bold mb-2 group-hover:text-gray-200 transition-colors">
                                    {{ $mainPost->title }}
                                </h2>
                                <p class="text-sm lg:text-base text-gray-300 font-medium">
                                    {{ $mainPost->publicationState?->published_at?->format('M d, Y') }}
                                </p>
                            </div>
                        </a>
                    @endif

                    {{-- Right Side --}}
                    <div class="flex flex-col gap-6 lg:gap-8">
                        {{-- Top Right: 2 Medium Posts --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-8 h-auto lg:h-1/2">
                            @foreach($topRightPosts as $post)
                                <a href="{{ route('articles.show', $post->slug) }}" class="group flex flex-col block">
                                    <div class="relative w-full aspect-[4/3] rounded-sm overflow-hidden mb-3">
                                        @if($cover = $post->featuredImage())
                                            <img src="{{ $cover->url(768) }}" alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                        @else
                                            <div class="absolute inset-0 bg-gray-200"></div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col flex-grow justify-start">
                                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">{{ $post->category?->name }}</p>
                                        <h3 class="text-[17px] font-bold text-gray-900 leading-snug group-hover:text-[#156e40] transition-colors mb-2 line-clamp-3">
                                            {{ $post->title }}
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-auto">{{ $post->publicationState?->published_at?->format('M d, Y') }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        {{-- Bottom Right: 4 Small List Posts --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 lg:gap-x-8 gap-y-5 lg:gap-y-0 h-auto lg:h-1/2 content-between">
                            @foreach($bottomRightPosts as $post)
                                <a href="{{ route('articles.show', $post->slug) }}" class="group flex items-start gap-4">
                                    <div class="relative w-[100px] h-[75px] shrink-0 rounded-sm overflow-hidden">
                                        @if($cover = $post->featuredImage())
                                            <img src="{{ $cover->url(400) }}" alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                        @else
                                            <div class="absolute inset-0 bg-gray-200"></div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <h4 class="text-[13px] font-bold text-gray-900 leading-snug group-hover:text-[#156e40] transition-colors mb-1 line-clamp-2">
                                            {{ $post->title }}
                                        </h4>
                                        <p class="text-[11px] text-gray-500">{{ $post->publicationState?->published_at?->format('M d, Y') }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Banner Ad Placeholder --}}
                <div class="w-full bg-[#1b2529] rounded-sm overflow-hidden my-16 relative flex items-center h-[140px] lg:h-[180px]">
                    <div class="absolute right-0 top-0 bottom-0 w-1/2 opacity-60">
                        <img src="https://images.unsplash.com/photo-1549399542-7e3f8b79c341?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover" alt="Car banner">
                        <div class="absolute inset-0 bg-gradient-to-r from-[#1b2529] to-transparent"></div>
                    </div>
                    <div class="relative z-10 px-8 lg:px-16">
                        <p class="text-white text-sm font-semibold mb-1 flex items-center gap-2">
                            <span class="text-green-500"><x-icon name="car" class="size-4"/></span> Urban Haven Auto
                        </p>
                        <h3 class="text-white text-3xl lg:text-4xl font-bold tracking-tight mb-2">READY FOR AN UPGRADE?</h3>
                        <p class="text-gray-300 text-sm lg:text-base mb-4">Drive home your dream car.</p>
                        <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur text-white px-3 py-1.5 rounded text-xs">
                            <x-icon name="globe" class="size-3"/> www.urbanhaven.test
                        </span>
                    </div>
                </div>

                {{-- Category Sections based on mockup --}}
                @php
                    $showcaseCategories = $categories->take(3);
                @endphp
                
                @foreach($showcaseCategories as $index => $cat)
                    @php 
                        $catPosts = $cat->posts()->published()->latest('id')->take(4)->get(); 
                    @endphp
                    
                    @if($catPosts->isNotEmpty())
                        <div class="mb-16">
                            <div class="flex items-end justify-between mb-6 border-b border-gray-200 pb-3">
                                <div>
                                    <h2 class="text-[22px] font-bold text-gray-900">{{ $cat->name }} {{ __('News') }}</h2>
                                    <p class="text-[13px] text-gray-500 mt-1">{{ __('Here are the featured :category blogs for you', ['category' => strtolower($cat->name)]) }}</p>
                                </div>
                                <a href="{{ route('articles.index', ['category' => $cat->slug]) }}" class="text-[13px] font-semibold text-gray-500 hover:text-gray-900 flex items-center gap-1">
                                    {{ __('View all') }} <x-icon name="chevron-right" class="size-3" />
                                </a>
                            </div>

                            @if($index === 0)
                                {{-- Layout 1: 2 side-by-side --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    @foreach($catPosts->take(2) as $post)
                                        <a href="{{ route('articles.show', $post->slug) }}" class="group block">
                                            <div class="relative w-full aspect-[16/9] rounded-sm overflow-hidden mb-4">
                                                @if($cover = $post->featuredImage())
                                                    <img src="{{ $cover->url(1024) }}" alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                                @else
                                                    <div class="absolute inset-0 bg-gray-200"></div>
                                                @endif
                                            </div>
                                            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">—</p>
                                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-[#156e40] transition-colors mb-2">
                                                {{ $post->title }}
                                            </h3>
                                            <p class="text-[13px] text-gray-500">{{ $post->publicationState?->published_at?->format('M d, Y') }}</p>
                                        </a>
                                    @endforeach
                                </div>

                            @elseif($index === 1)
                                {{-- Layout 2: 1 Large Left, 3 Small Horizontal Right --}}
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    @if($largePost = $catPosts->first())
                                        <a href="{{ route('articles.show', $largePost->slug) }}" class="group relative block h-[350px] lg:h-[450px] rounded-sm overflow-hidden">
                                            @if($cover = $largePost->featuredImage())
                                                <img src="{{ $cover->url(1024) }}" alt="{{ $largePost->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                            @endif
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                                <p class="text-[11px] font-bold uppercase tracking-wider mb-2">—</p>
                                                <h3 class="text-2xl font-bold mb-2 group-hover:text-gray-200 transition-colors">
                                                    {{ $largePost->title }}
                                                </h3>
                                                <p class="text-[13px] text-gray-300 font-medium">
                                                    {{ $largePost->publicationState?->published_at?->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </a>
                                    @endif
                                    
                                    <div class="flex flex-col justify-between gap-4">
                                        @foreach($catPosts->slice(1, 3) as $post)
                                            <a href="{{ route('articles.show', $post->slug) }}" class="group flex items-center gap-5">
                                                <div class="relative w-[140px] aspect-[4/3] shrink-0 rounded-sm overflow-hidden">
                                                    @if($cover = $post->featuredImage())
                                                        <img src="{{ $cover->url(400) }}" alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                                    @else
                                                        <div class="absolute inset-0 bg-gray-200"></div>
                                                    @endif
                                                </div>
                                                <div class="flex flex-col">
                                                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">—</p>
                                                    <h4 class="text-[15px] font-bold text-gray-900 leading-snug group-hover:text-[#156e40] transition-colors mb-2 line-clamp-3">
                                                        {{ $post->title }}
                                                    </h4>
                                                    <p class="text-[12px] text-gray-500">{{ $post->publicationState?->published_at?->format('M d, Y') }}</p>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>

                            @else
                                {{-- Layout 3: 4 Small Vertical --}}
                                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                                    @foreach($catPosts as $post)
                                        <a href="{{ route('articles.show', $post->slug) }}" class="group block">
                                            <div class="relative w-full aspect-[4/3] rounded-sm overflow-hidden mb-3">
                                                @if($cover = $post->featuredImage())
                                                    <img src="{{ $cover->url(500) }}" alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                                @else
                                                    <div class="absolute inset-0 bg-gray-200"></div>
                                                @endif
                                            </div>
                                            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">—</p>
                                            <h3 class="text-[14px] font-bold text-gray-900 leading-snug group-hover:text-[#156e40] transition-colors mb-2 line-clamp-3">
                                                {{ $post->title }}
                                            </h3>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
                
                {{-- Fallback Pagination for the main posts collection to show there's more content --}}
                @if($posts->hasPages())
                    <div class="mt-8 flex justify-center">{{ $posts->onEachSide(1)->links() }}</div>
                @endif
                
            @else
                {{-- Standard Grid for Pagination / Category Filtered Views --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <a href="{{ route('articles.show', $post->slug) }}" class="group flex flex-col block">
                            <div class="relative w-full aspect-[4/3] rounded-sm overflow-hidden mb-4">
                                @if($cover = $post->featuredImage())
                                    <img src="{{ $cover->url(768) }}" alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                @else
                                    <div class="absolute inset-0 bg-gray-200"></div>
                                @endif
                            </div>
                            <div class="flex flex-col flex-grow">
                                @if($post->category)
                                    <p class="text-[11px] font-bold text-[#156e40] uppercase tracking-wider mb-2">{{ $post->category->name }}</p>
                                @endif
                                <h3 class="text-xl font-bold text-gray-900 leading-snug group-hover:text-[#156e40] transition-colors mb-3">
                                    {{ $post->title }}
                                </h3>
                                @if($post->excerpt)
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $post->excerpt }}</p>
                                @endif
                                <p class="text-[13px] text-gray-500 font-medium mt-auto">{{ $post->publicationState?->published_at?->format('M d, Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($posts->hasPages())
                    <div class="mt-16 flex justify-center">{{ $posts->onEachSide(1)->links() }}</div>
                @endif
            @endif
        @else
            <x-ui.empty icon="document" :title="__('No articles published yet')"
                        :description="__('Guides from our team will appear here. In the meantime, browse our properties or ask us a question.')">
                <a class="uh-btn-primary uh-btn-sm bg-[#156e40] text-white hover:bg-[#0e4f2d]" href="{{ route('properties.index') }}">{{ __('Explore properties') }}</a>
                <a class="uh-btn-secondary uh-btn-sm" href="{{ route('faq') }}">{{ __('Read the FAQ') }}</a>
            </x-ui.empty>
        @endif
    </div>
@endsection
