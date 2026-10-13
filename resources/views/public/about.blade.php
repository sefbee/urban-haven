@php
    $aboutSections = $aboutSections ?? \App\Support\AboutPageSections::visible();
    $aboutImages = $page->galleryImages();
    $heroImage = \App\Models\CmsBlock::imagesFor('about_hero')->first() ?? $aboutImages->get(0);
    $storyImage = \App\Models\CmsBlock::imagesFor('about_story')->first() ?? $aboutImages->get(1) ?? $heroImage;
    $chooseImage = \App\Models\CmsBlock::imagesFor('about_choose')->first();
@endphp

<div class="font-sans antialiased bg-[var(--color-page-background)] pb-16">
    @foreach($aboutSections as $section => $data)
        @switch($section)
            @case('hero')
                {{-- Hero Section --}}
                <section class="grid lg:grid-cols-2 min-h-[500px] bg-[var(--color-dominant)] relative" aria-labelledby="about-title">
                    <div class="flex flex-col justify-center px-8 py-16 sm:px-16 lg:px-24">
                        @if(!empty($data['eyebrow']))
                            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.2em] text-[var(--color-accent)]">{{ $data['eyebrow'] }}</p>
                        @endif
                        <h1 id="about-title" class="uh-h1 mb-6 max-w-xl">
                            {!! nl2br(e($data['title'] ?? '')) !!}
                        </h1>
                        @if(!empty($data['intro']))
                            <p class="text-base sm:text-lg text-[var(--color-muted-text)] leading-relaxed max-w-lg">
                                {{ $data['intro'] }}
                            </p>
                        @endif

                        @if(!empty($data['quote']))
                            <blockquote class="mt-6 pl-4 border-l-2 border-[var(--color-accent)] max-w-lg">
                                @if(!empty($data['quote_kicker']))
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-accent)] mb-1">{{ $data['quote_kicker'] }}</p>
                                @endif
                                <p class="text-[15px] italic font-display text-[var(--color-body-text)]">
                                    "{{ $data['quote'] }}"
                                </p>
                                @if(!empty($data['quote_label']))
                                    <cite class="block text-xs text-[var(--color-muted-text)] mt-1.5 not-italic font-medium">— {{ $data['quote_label'] }}</cite>
                                @endif
                            </blockquote>
                        @endif

                        @if(!empty($data['primary_cta']) || !empty($data['secondary_cta']))
                            <div class="mt-8 flex flex-wrap items-center gap-4">
                                @if(!empty($data['primary_cta']))
                                    <a href="{{ $data['primary_url'] ?? route('properties.index') }}" class="inline-flex items-center justify-center bg-[var(--color-accent)] hover:bg-[var(--color-accent-hover)] text-white px-7 py-3 rounded text-sm font-semibold transition-colors">
                                        {{ $data['primary_cta'] }} <x-icon name="arrow-right" class="size-3.5 ml-2" />
                                    </a>
                                @endif
                                @if(!empty($data['secondary_cta']))
                                    <a href="{{ $data['secondary_url'] ?? route('page.contact') }}" class="inline-flex items-center justify-center border border-[var(--color-divider)] hover:border-[var(--color-body-text)] text-[var(--color-body-text)] px-7 py-3 rounded text-sm font-semibold transition-colors">
                                        {{ $data['secondary_cta'] }}
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="mt-8 flex items-center gap-3 text-sm font-semibold text-[var(--color-muted-text)]">
                                <a href="{{ route('home') }}" class="hover:text-[var(--color-body-text)] transition-colors">Home</a>
                                <span>/</span>
                                <span class="text-[var(--color-accent)]">About Us</span>
                            </div>
                        @endif
                    </div>
                    <div class="relative h-64 sm:h-96 lg:h-auto overflow-hidden">
                        <img src="{{ $heroImage?->url(1920) ?? 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=2200&q=85' }}"
                             alt="{{ $heroImage?->alt(app()->getLocale()) ?: __('Warm, thoughtfully designed contemporary home') }}"
                             class="absolute inset-0 w-full h-full object-cover" fetchpriority="high">
                        @if(!empty($data['badge']))
                            <div class="absolute bottom-6 right-6 bg-[var(--color-secondary)] text-[var(--color-secondary-text)] px-4 py-2 rounded text-xs font-semibold uppercase tracking-wider backdrop-blur-md shadow-lg">
                                {{ $data['badge'] }}
                            </div>
                        @endif
                    </div>
                </section>
                @break

            @case('story')
                {{-- Story Section --}}
                <section class="grid lg:grid-cols-2" aria-labelledby="about-story-title">
                    <div class="relative h-64 sm:h-96 lg:h-auto overflow-hidden order-last lg:order-first">
                        <img src="{{ $storyImage?->url(1280) ?? 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=85' }}"
                             alt="{{ $storyImage?->alt(app()->getLocale()) ?: __('Sunlit modern apartment interior') }}" 
                             class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                    </div>
                    <div class="flex flex-col justify-center px-8 py-16 sm:px-16 lg:px-24 bg-[var(--color-card-surface)]">
                        @if(!empty($data['eyebrow']))
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[var(--color-accent)] mb-4">{{ $data['eyebrow'] }}</p>
                        @endif
                        <h2 id="about-story-title" class="uh-h2 mb-6">
                            {{ $data['title'] ?? '' }}
                        </h2>
                        <div class="uh-prose text-[15px] leading-relaxed text-[var(--color-muted-text)] mb-8 max-w-xl">
                            {!! \Stevebauman\Purify\Facades\Purify::clean((string) $page->body) !!}
                        </div>
                        
                        @if(!empty($data['quote']))
                            <blockquote class="pl-6 border-l-2 border-[var(--color-accent)] max-w-xl mb-10">
                                @if(!empty($data['quote_kicker']))
                                    <p class="text-xs font-bold uppercase tracking-wider text-[var(--color-accent)] mb-1">{{ $data['quote_kicker'] }}</p>
                                @endif
                                <p class="text-lg italic font-display text-[var(--color-body-text)]">
                                    "{{ $data['quote'] }}"
                                </p>
                                @if(!empty($data['quote_label']))
                                    <cite class="block text-xs text-[var(--color-muted-text)] mt-2 not-italic font-medium">— {{ $data['quote_label'] }}</cite>
                                @endif
                            </blockquote>
                        @endif

                        @if(!empty($data['cta_label']))
                            <div>
                                <a href="{{ $data['cta_url'] ?? route('properties.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-[var(--color-accent)] hover:text-[var(--color-body-text)] transition-colors">
                                    {{ $data['cta_label'] }} <x-icon name="arrow-right" class="size-3.5" />
                                </a>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Stats Bar --}}
                @if(!empty($data['stat_one_value']) || !empty($data['stat_two_value']) || !empty($data['stat_three_value']) || !empty($data['stat_four_value']))
                    <section class="bg-[var(--color-secondary)] text-[var(--color-secondary-text)] py-12 lg:py-16">
                        <div class="uh-container">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-4 divide-y md:divide-y-0 md:divide-x divide-white/10 text-center">
                                @if(!empty($data['stat_one_value']))
                                    <div class="pt-4 md:pt-0">
                                        <p class="text-4xl lg:text-5xl font-display text-[var(--color-secondary-text)] mb-2">{{ $data['stat_one_value'] }}</p>
                                        <p class="text-[13px] text-[color-mix(in_srgb,var(--color-secondary-text)_65%,transparent)] font-medium">{!! nl2br(e($data['stat_one_label'] ?? '')) !!}</p>
                                    </div>
                                @endif
                                @if(!empty($data['stat_two_value']))
                                    <div class="pt-8 md:pt-0">
                                        <p class="text-4xl lg:text-5xl font-display text-[var(--color-secondary-text)] mb-2">{{ $data['stat_two_value'] }}</p>
                                        <p class="text-[13px] text-[color-mix(in_srgb,var(--color-secondary-text)_65%,transparent)] font-medium">{!! nl2br(e($data['stat_two_label'] ?? '')) !!}</p>
                                    </div>
                                @endif
                                @if(!empty($data['stat_three_value']))
                                    <div class="pt-8 md:pt-0">
                                        <p class="text-4xl lg:text-5xl font-display text-[var(--color-secondary-text)] mb-2">{{ $data['stat_three_value'] }}</p>
                                        <p class="text-[13px] text-[color-mix(in_srgb,var(--color-secondary-text)_65%,transparent)] font-medium">{!! nl2br(e($data['stat_three_label'] ?? '')) !!}</p>
                                    </div>
                                @endif
                                @if(!empty($data['stat_four_value']))
                                    <div class="pt-8 md:pt-0">
                                        <p class="text-4xl lg:text-5xl font-display text-[var(--color-secondary-text)] mb-2">{{ $data['stat_four_value'] }}</p>
                                        <p class="text-[13px] text-[color-mix(in_srgb,var(--color-secondary-text)_65%,transparent)] font-medium">{!! nl2br(e($data['stat_four_label'] ?? '')) !!}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                @endif
                @break

            @case('purpose')
                {{-- Purpose / Mission Vision --}}
                <section class="py-20 lg:py-28 bg-[var(--color-card-surface)]">
                    <div class="uh-container-narrow text-center mb-16">
                        @if(!empty($data['eyebrow']))
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[var(--color-accent)] mb-4">{{ $data['eyebrow'] }}</p>
                        @endif
                        <h2 class="uh-h2">{{ $data['title'] ?? '' }}</h2>
                    </div>
                    
                    <div class="uh-container">
                        <div class="grid md:grid-cols-2 gap-6 lg:gap-10">
                            {{-- Mission --}}
                            <div class="bg-[var(--color-card-surface)] border border-[var(--color-divider)] p-8 lg:p-10 flex gap-6 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] hover:border-[color-mix(in_srgb,var(--color-accent)_30%,transparent)] transition-colors">
                                <div class="shrink-0 w-16 h-16 rounded-full border-2 border-[var(--color-accent)] flex items-center justify-center text-[var(--color-accent)]">
                                    <x-icon name="compass" class="size-7" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-[var(--color-body-text)] mb-2 uppercase tracking-wide">{{ $data['mission_title'] ?? '' }}</h3>
                                    <p class="text-sm text-[var(--color-muted-text)] leading-relaxed">
                                        {{ $data['mission_text'] ?? '' }}
                                    </p>
                                </div>
                            </div>
                            
                            {{-- Vision --}}
                            <div class="bg-[var(--color-card-surface)] border border-[var(--color-divider)] p-8 lg:p-10 flex gap-6 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] hover:border-[color-mix(in_srgb,var(--color-accent)_30%,transparent)] transition-colors">
                                <div class="shrink-0 w-16 h-16 rounded-full border-2 border-[var(--color-accent)] flex items-center justify-center text-[var(--color-accent)]">
                                    <x-icon name="building" class="size-7" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-[var(--color-body-text)] mb-2 uppercase tracking-wide">{{ $data['vision_title'] ?? '' }}</h3>
                                    <p class="text-sm text-[var(--color-muted-text)] leading-relaxed">
                                        {{ $data['vision_text'] ?? '' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                @break

            @case('values')
                {{-- Values --}}
                <section class="py-20 bg-[var(--color-card-surface)]">
                    <div class="uh-container-narrow text-center mb-16">
                        @if(!empty($data['eyebrow']))
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[var(--color-accent)] mb-4">{{ $data['eyebrow'] }}</p>
                        @endif
                        <h2 class="uh-h2">{{ $data['title'] ?? '' }}</h2>
                        @if(!empty($data['intro']))
                            <p class="mt-4 text-[15px] text-[var(--color-muted-text)]">{{ $data['intro'] }}</p>
                        @endif
                    </div>
                    
                    <div class="uh-container">
                        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            @php
                                $values = [
                                    [$data['value_one_icon'] ?? 'shield', $data['value_one_title'] ?? '', $data['value_one_text'] ?? ''],
                                    [$data['value_two_icon'] ?? 'users', $data['value_two_title'] ?? '', $data['value_two_text'] ?? ''],
                                    [$data['value_three_icon'] ?? 'sparkle', $data['value_three_title'] ?? '', $data['value_three_text'] ?? ''],
                                    [$data['value_four_icon'] ?? 'refresh', $data['value_four_title'] ?? '', $data['value_four_text'] ?? ''],
                                ];
                            @endphp
                            @foreach($values as [$icon, $title, $description])
                                @if(!empty($title))
                                    <div class="border border-[var(--color-divider)] p-8 text-center hover:shadow-[0_8px_30px_-12px_rgba(0,0,0,0.1)] transition-all bg-[var(--color-card-surface)]">
                                        <div class="mx-auto w-14 h-14 bg-[var(--color-dominant)] rounded-full flex items-center justify-center text-[var(--color-accent)] mb-6 border border-[color-mix(in_srgb,var(--color-accent)_20%,transparent)]">
                                            <x-icon :name="$icon" class="size-5" />
                                        </div>
                                        <h3 class="text-base font-bold text-[var(--color-body-text)] mb-3">{{ $title }}</h3>
                                        <p class="text-[13px] text-[var(--color-muted-text)] leading-relaxed">{{ $description }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </section>
                @break

            @case('choose')
                {{-- Why Choose Us / CTA Split --}}
                <section class="grid lg:grid-cols-2 bg-[var(--color-secondary)] text-[var(--color-secondary-text)] overflow-hidden mt-10">
                    <div class="px-8 py-16 sm:px-16 lg:px-24 xl:pl-32 flex flex-col justify-center relative">
                        <div class="absolute right-0 bottom-0 opacity-10 pointer-events-none transform translate-x-1/4 translate-y-1/4">
                            <svg width="300" height="300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="20" y="20" width="2" height="80" fill="currentColor"/>
                                <rect x="40" y="40" width="2" height="60" fill="currentColor"/>
                                <rect x="60" y="60" width="2" height="40" fill="currentColor"/>
                                <rect x="80" y="10" width="2" height="90" fill="currentColor"/>
                            </svg>
                        </div>
                        
                        <div class="relative z-10">
                            @if(!empty($data['eyebrow']))
                                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[var(--color-accent)] mb-4">{{ $data['eyebrow'] }}</p>
                            @endif
                            <h2 class="text-3xl sm:text-[2.5rem] leading-[1.1] font-display mb-10 max-w-md">
                                {{ $data['title'] ?? '' }}
                            </h2>
                            @if(!empty($data['list']))
                                <ul class="space-y-4 mb-10">
                                    @foreach(array_filter(array_map('trim', explode("\n", $data['list']))) as $item)
                                        <li class="flex items-center gap-3 text-sm text-[color-mix(in_srgb,var(--color-secondary-text)_78%,transparent)]">
                                            <x-icon name="check-circle" class="size-4 text-[var(--color-accent)]" /> {{ $item }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            @if(!empty($data['cta_label']))
                                <a href="{{ $data['cta_url'] ?? route('properties.index') }}" class="inline-flex items-center justify-center bg-[var(--color-accent)] hover:bg-[var(--color-accent-hover)] text-[var(--color-secondary-text)] px-8 py-3.5 rounded text-sm font-semibold transition-colors">
                                    {{ $data['cta_label'] }} <x-icon name="arrow-right" class="size-3.5 ml-2" />
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="relative h-72 sm:h-96 lg:h-auto">
                        <img src="{{ $chooseImage?->url(1200) ?? 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1200&q=85' }}" 
                             alt="{{ $chooseImage?->alt(app()->getLocale()) ?: __('Real estate consultation') }}" class="absolute inset-0 w-full h-full object-cover">
                    </div>
                </section>
                @break

            @case('approach')
                {{-- Process Steps --}}
                <section class="py-20 lg:py-28 bg-[var(--color-dominant)]">
                    <div class="uh-container-narrow text-center mb-16">
                        @if(!empty($data['eyebrow']))
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[var(--color-accent)] mb-4">{{ $data['eyebrow'] }}</p>
                        @endif
                        <h2 class="uh-h2">{{ $data['title'] ?? '' }}</h2>
                    </div>
                    
                    <div class="uh-container">
                        <div class="relative">
                            <div class="hidden md:block absolute top-6 left-[12%] right-[12%] h-[1px] bg-[color-mix(in_srgb,var(--color-accent)_30%,transparent)]"></div>
                            
                            <div class="grid md:grid-cols-4 gap-10 md:gap-6 relative">
                                @php
                                    $steps = [
                                        ['01', $data['approach_one_title'] ?? '', $data['approach_one_text'] ?? ''],
                                        ['02', $data['approach_two_title'] ?? '', $data['approach_two_text'] ?? ''],
                                        ['03', $data['approach_three_title'] ?? '', $data['approach_three_text'] ?? ''],
                                        ['04', $data['approach_four_title'] ?? '', $data['approach_four_text'] ?? ''],
                                    ];
                                @endphp
                                @foreach($steps as [$num, $title, $desc])
                                    @if(!empty($title))
                                        <div class="text-center relative">
                                            <div class="w-12 h-12 rounded-full bg-[var(--color-card-surface)] border border-[var(--color-accent)] text-[var(--color-accent)] flex items-center justify-center font-display text-lg mx-auto mb-6 relative z-10 shadow-[0_0_0_8px_var(--color-dominant)]">
                                                {{ $num }}
                                            </div>
                                            <h3 class="text-base font-bold text-[var(--color-body-text)] mb-2">{{ $title }}</h3>
                                            <p class="text-[13px] text-[var(--color-muted-text)] leading-relaxed max-w-[200px] mx-auto">{{ $desc }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
                @break

            @case('cta')
                {{-- Final CTA --}}
                <section class="bg-[var(--color-secondary)] py-20 text-center relative overflow-hidden border-t border-white/5">
                    <div class="absolute inset-0 pointer-events-none opacity-5">
                        <svg width="100%" height="100%" viewBox="0 0 1000 200" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                            <rect x="100" y="50" width="2" height="150" fill="white"/>
                            <rect x="250" y="100" width="2" height="100" fill="white"/>
                            <rect x="400" y="20" width="2" height="180" fill="white"/>
                            <rect x="550" y="80" width="2" height="120" fill="white"/>
                            <rect x="700" y="30" width="2" height="170" fill="white"/>
                            <rect x="850" y="110" width="2" height="90" fill="white"/>
                        </svg>
                    </div>
                    <div class="uh-container-narrow relative z-10">
                        @if(!empty($data['eyebrow']))
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[var(--color-accent)] mb-3">{{ $data['eyebrow'] }}</p>
                        @endif
                        <h2 class="uh-h2 mb-4">{{ $data['title'] ?? '' }}</h2>
                        <p class="text-[15px] text-[color-mix(in_srgb,var(--color-secondary-text)_65%,transparent)] mb-8">{{ $data['body'] ?? '' }}</p>
                        <div class="flex flex-wrap justify-center gap-4">
                            @if(!empty($data['label']))
                                <a href="{{ $data['url'] ?? route('properties.index') }}" class="inline-flex items-center justify-center bg-[var(--color-accent)] hover:bg-[var(--color-accent-hover)] text-[var(--color-secondary-text)] px-8 py-3.5 rounded text-[13px] font-semibold transition-colors">
                                    {{ $data['label'] }} <x-icon name="arrow-right" class="size-3.5 ml-2" />
                                </a>
                            @endif
                            @if(!empty($data['secondary_label']))
                                <a href="{{ $data['secondary_url'] ?? route('page.contact') }}" class="inline-flex items-center justify-center border border-white/20 hover:bg-[var(--color-card-surface)]/5 text-[var(--color-secondary-text)] px-8 py-3.5 rounded text-[13px] font-semibold transition-colors">
                                    {{ $data['secondary_label'] }}
                                </a>
                            @endif
                        </div>
                    </div>
                </section>
                @break
        @endswitch
    @endforeach
</div>
