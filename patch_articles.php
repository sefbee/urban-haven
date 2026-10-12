<?php
$content = file_get_contents('resources/views/public/home/articles.blade.php');

$searchGrid = '<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 grid-flow-dense" data-reveal>';
$replaceGrid = '<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 grid-flow-dense lg:grid-rows-[repeat(4,180px)] sm:auto-rows-[180px]" data-reveal>';
$content = str_replace($searchGrid, $replaceGrid, $content);

$searchLoop = <<<'HTML'
                    @foreach($articles as $post)
                        @php
                            $cover = $post->featuredImage();
                            $span = match($loop->index % 6) {
                                0 => 'sm:col-span-2 sm:row-span-2',
                                1 => 'sm:col-span-2 sm:row-span-1',
                                2 => 'sm:col-span-1 sm:row-span-1',
                                3 => 'sm:col-span-1 sm:row-span-1',
                                4 => 'sm:col-span-2 sm:row-span-1',
                                5 => 'sm:col-span-2 sm:row-span-1',
                                default => 'sm:col-span-1 sm:row-span-1',
                            };
                        @endphp
                        <a href="{{ route('articles.show', $post->slug) }}" class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md {{ $span }}" style="max-height: {{ str_contains($span, 'row-span-2') ? '380px' : '220px' }};">
                            <div class="flex-1 w-full overflow-hidden bg-gray-100 min-h-[100px]">
                                @if($cover)
                                    <img src="{{ $cover->url(768) }}" srcset="{{ $cover->srcset() }}" alt="{{ $cover->alt() ?: $post->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <span class="uh-media-placeholder flex h-full w-full items-center justify-center p-4 text-center text-xs leading-tight">{{ $post->category?->name ?? __('Urban Haven') }}</span>
                                @endif
                            </div>
                            <div class="flex flex-col p-3 shrink-0 bg-white">
                                <p class="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500 mb-1">{{ $post->category?->name ?? __('Journal') }}</p>
                                <h3 class="text-sm font-bold leading-snug text-slate-900 transition group-hover:text-primary-600 line-clamp-1">{{ $post->title }}</h3>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500 line-clamp-2 hidden sm:block">{{ $post->excerpt ?? Str::limit(strip_tags($post->body), 80) }}</p>
                                <time class="mt-2 text-[0.65rem] text-slate-400" datetime="{{ $post->publicationState?->published_at?->toDateString() }}">{{ $post->publicationState?->published_at?->format('M d, Y') }}</time>
                            </div>
                        </a>
                    @endforeach
HTML;

$replaceLoop = <<<'HTML'
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
HTML;

$content = str_replace($searchLoop, $replaceLoop, $content);
file_put_contents('resources/views/public/home/articles.blade.php', $content);
