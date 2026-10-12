@php
    $aboutContent = \App\Support\AboutPageContent::resolve($page->layout_content);
    $aboutImages = $page->galleryImages();
    $heroImage = $aboutImages->get(0);
    $storyImage = $aboutImages->get(1) ?? $heroImage;
@endphp

<div class="font-sans antialiased bg-white pb-16">
    {{-- Hero Section --}}
    <section class="grid lg:grid-cols-2 min-h-[500px] bg-[#fbfbf9]" aria-labelledby="about-title">
        <div class="flex flex-col justify-center px-8 py-16 sm:px-16 lg:px-24">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.2em] text-[#d6b562]">{{ $aboutContent['hero_eyebrow'] }}</p>
            <h1 id="about-title" class="text-[2.75rem] sm:text-[3.5rem] lg:text-[4rem] leading-[1.05] font-display text-gray-900 mb-6 max-w-xl">
                {!! nl2br(e($aboutContent['hero_title'])) !!}
            </h1>
            <p class="text-base sm:text-lg text-gray-600 leading-relaxed max-w-lg">
                {{ $aboutContent['hero_intro'] }}
            </p>
            <div class="mt-8 flex items-center gap-3 text-sm font-semibold text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-gray-900 transition-colors">Home</a>
                <span>/</span>
                <span class="text-[#d6b562]">About Us</span>
            </div>
        </div>
        <div class="relative h-64 sm:h-96 lg:h-auto overflow-hidden">
            <img src="{{ $heroImage?->url(1920) ?? 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=2200&q=85' }}"
                 alt="{{ $heroImage?->alt(app()->getLocale()) ?: __('Warm, thoughtfully designed contemporary home') }}"
                 class="absolute inset-0 w-full h-full object-cover" fetchpriority="high">
        </div>
    </section>

    {{-- Story Section --}}
    <section class="grid lg:grid-cols-2" aria-labelledby="about-story-title">
        <div class="relative h-64 sm:h-96 lg:h-auto overflow-hidden order-last lg:order-first">
            <img src="{{ $storyImage?->url(1280) ?? 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=85' }}"
                 alt="{{ $storyImage?->alt(app()->getLocale()) ?: __('Sunlit modern apartment interior') }}" 
                 class="absolute inset-0 w-full h-full object-cover" loading="lazy">
        </div>
        <div class="flex flex-col justify-center px-8 py-16 sm:px-16 lg:px-24 bg-white">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#d6b562] mb-4">{{ $aboutContent['story_eyebrow'] }}</p>
            <h2 id="about-story-title" class="text-3xl sm:text-[2.5rem] leading-[1.1] font-display text-gray-900 mb-6">
                {{ $aboutContent['story_title'] }}
            </h2>
            <div class="uh-prose text-[15px] leading-relaxed text-gray-600 mb-8 max-w-xl">
                {!! \Stevebauman\Purify\Facades\Purify::clean((string) $page->body) !!}
            </div>
            
            <blockquote class="pl-6 border-l-2 border-[#d6b562] max-w-xl mb-10">
                <p class="text-lg italic font-display text-gray-800">
                    "{{ $aboutContent['hero_quote'] }}"
                </p>
            </blockquote>

            <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#d6b562] hover:text-gray-900 transition-colors">
                Discover Our Properties <x-icon name="arrow-right" class="size-3.5" />
            </a>
        </div>
    </section>

    {{-- Stats Bar --}}
    <section class="bg-[#1b1c1c] text-white py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-4 divide-y md:divide-y-0 md:divide-x divide-white/10 text-center">
                                <div class="pt-4 md:pt-0">
                    <p class="text-4xl lg:text-5xl font-display text-[#d6b562] mb-2">{{ $aboutContent['stat_one_value'] }}</p>
                    <p class="text-[13px] text-gray-400 font-medium">{!! nl2br(e($aboutContent['stat_one_label'])) !!}</p>
                </div>
                <div class="pt-8 md:pt-0">
                    <p class="text-4xl lg:text-5xl font-display text-[#d6b562] mb-2">{{ $aboutContent['stat_two_value'] }}</p>
                    <p class="text-[13px] text-gray-400 font-medium">{!! nl2br(e($aboutContent['stat_two_label'])) !!}</p>
                </div>
                <div class="pt-8 md:pt-0">
                    <p class="text-4xl lg:text-5xl font-display text-[#d6b562] mb-2">{{ $aboutContent['stat_three_value'] }}</p>
                    <p class="text-[13px] text-gray-400 font-medium">{!! nl2br(e($aboutContent['stat_three_label'])) !!}</p>
                </div>
                <div class="pt-8 md:pt-0">
                    <p class="text-4xl lg:text-5xl font-display text-[#d6b562] mb-2">{{ $aboutContent['stat_four_value'] }}</p>
                    <p class="text-[13px] text-gray-400 font-medium">{!! nl2br(e($aboutContent['stat_four_label'])) !!}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Purpose / Mission Vision --}}
    <section class="py-20 lg:py-28 bg-[#fdfdfc]">
                        <div class="max-w-5xl mx-auto px-6 lg:px-8 text-center mb-16">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#d6b562] mb-4">{{ $aboutContent['team_eyebrow'] }}</p>
            <h2 class="text-3xl sm:text-4xl font-display text-gray-900">{{ $aboutContent['team_title'] }}</h2>
        </div>
        
        <div class="max-w-6xl mx-auto px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-6 lg:gap-10">
                {{-- Mission --}}
                <div class="bg-white border border-gray-100 p-8 lg:p-10 flex gap-6 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] hover:border-[#d6b562]/30 transition-colors">
                    <div class="shrink-0 w-16 h-16 rounded-full border-2 border-[#d6b562] flex items-center justify-center text-[#d6b562]">
                        <x-icon name="compass" class="size-7" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2 uppercase tracking-wide">{{ $aboutContent['mission_title'] }}</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            {{ $aboutContent['mission_text'] }}
                        </p>
                    </div>
                </div>
                
                {{-- Vision --}}
                <div class="bg-white border border-gray-100 p-8 lg:p-10 flex gap-6 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] hover:border-[#d6b562]/30 transition-colors">
                    <div class="shrink-0 w-16 h-16 rounded-full border-2 border-[#d6b562] flex items-center justify-center text-[#d6b562]">
                        <x-icon name="building" class="size-7" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2 uppercase tracking-wide">{{ $aboutContent['vision_title'] }}</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            {{ $aboutContent['vision_text'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-6 lg:px-8 text-center mb-16">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#d6b562] mb-4">What Guides Us</p>
            <h2 class="text-3xl sm:text-4xl font-display text-gray-900">{{ $aboutContent['values_title'] }}</h2>
        </div>
        
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                @php
                    $values = [
                        ['shield', $aboutContent['value_one_title'], $aboutContent['value_one_text']],
                        ['users', $aboutContent['value_two_title'], $aboutContent['value_two_text']],
                        ['sparkle', $aboutContent['value_three_title'], $aboutContent['value_three_text']],
                        ['refresh', $aboutContent['value_four_title'], $aboutContent['value_four_text']],
                    ];
                @endphp
                @foreach($values as [$icon, $title, $description])
                    <div class="border border-gray-100 p-8 text-center hover:shadow-[0_8px_30px_-12px_rgba(0,0,0,0.1)] transition-all bg-white">
                        <div class="mx-auto w-14 h-14 bg-[#fbf9f5] rounded-full flex items-center justify-center text-[#d6b562] mb-6 border border-[#d6b562]/20">
                            <x-icon :name="$icon" class="size-5" />
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mb-3">{{ $title }}</h3>
                        <p class="text-[13px] text-gray-500 leading-relaxed">{{ $description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Why Choose Us / CTA Split --}}
    <section class="grid lg:grid-cols-2 bg-[#1b1c1c] text-white overflow-hidden mt-10">
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
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#d6b562] mb-4">{{ $aboutContent['choose_eyebrow'] }}</p>
                <h2 class="text-3xl sm:text-[2.5rem] leading-[1.1] font-display mb-10 max-w-md">
                    {{ $aboutContent['choose_title'] }}
                </h2>
                <ul class="space-y-4 mb-10">
                    @foreach(array_filter(array_map('trim', explode("\n", $aboutContent['choose_list']))) as $item)
                        <li class="flex items-center gap-3 text-sm text-gray-300">
                            <x-icon name="check-circle" class="size-4 text-[#d6b562]" /> {{ $item }}
                        </li>
                    @endforeach
                </ul>
                <a href="{{ $aboutContent['choose_cta_url'] }}" class="inline-flex items-center justify-center bg-[#d6b562] hover:bg-[#c4a452] text-white px-8 py-3.5 rounded text-sm font-semibold transition-colors">
                    {{ $aboutContent['choose_cta_label'] }} <x-icon name="arrow-right" class="size-3.5 ml-2" />
                </a>
            </div>
        </div>
        <div class="relative h-72 sm:h-96 lg:h-auto">
            <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1200&q=85" 
                 alt="Real estate consultation" class="absolute inset-0 w-full h-full object-cover">
        </div>
    </section>

    {{-- Process Steps --}}
    <section class="py-20 lg:py-28 bg-[#fbfbf9]">
                <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center mb-16">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#d6b562] mb-4">{{ $aboutContent['approach_eyebrow'] }}</p>
            <h2 class="text-3xl sm:text-4xl font-display text-gray-900">{{ $aboutContent['approach_title'] }}</h2>
        </div>
        
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="relative">
                <div class="hidden md:block absolute top-6 left-[12%] right-[12%] h-[1px] bg-[#d6b562]/30"></div>
                
                <div class="grid md:grid-cols-4 gap-10 md:gap-6 relative">
                    @php
                        $steps = [
                            ['01', $aboutContent['approach_one_title'], $aboutContent['approach_one_text']],
                            ['02', $aboutContent['approach_two_title'], $aboutContent['approach_two_text']],
                            ['03', $aboutContent['approach_three_title'], $aboutContent['approach_three_text']],
                            ['04', $aboutContent['approach_four_title'], $aboutContent['approach_four_text']],
                        ];
                    @endphp
                    @foreach($steps as [$num, $title, $desc])
                        <div class="text-center relative">
                            <div class="w-12 h-12 rounded-full bg-white border border-[#d6b562] text-[#d6b562] flex items-center justify-center font-display text-lg mx-auto mb-6 relative z-10 shadow-[0_0_0_8px_#fbfbf9]">
                                {{ $num }}
                            </div>
                            <h3 class="text-base font-bold text-gray-900 mb-2">{{ $title }}</h3>
                            <p class="text-[13px] text-gray-500 leading-relaxed max-w-[200px] mx-auto">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Team --}}
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-6 lg:px-8 text-center mb-16">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#d6b562] mb-4">{{ $aboutContent['team_eyebrow'] ?? 'Our People' }}</p>
            <h2 class="text-3xl sm:text-4xl font-display text-gray-900">{{ $aboutContent['team_title'] ?? 'Meet the Team Behind Urban Haven' }}</h2>
        </div>
        
        <div class="max-w-6xl mx-auto px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                @foreach([
                    ['MD. CLIENT NAME', 'Managing Director', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=400&q=80'],
                    ['TEAM MEMBER', 'Director', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80'],
                    ['TEAM MEMBER', 'Head of Sales', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80'],
                ] as [$name, $role, $img])
                    <div class="group cursor-pointer">
                        <div class="relative w-full aspect-square bg-[#e8e4db] rounded-sm overflow-hidden mb-5">
                            <img src="{{ $img }}" alt="{{ $name }}" class="absolute inset-0 w-full h-full object-cover grayscale mix-blend-multiply group-hover:grayscale-0 group-hover:mix-blend-normal transition-all duration-500">
                        </div>
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">{{ $name }}</h3>
                                <p class="text-xs text-gray-500 mt-1">{{ $role }}</p>
                            </div>
                            <a href="#" class="text-[#d6b562] hover:text-gray-900 transition-colors">
                                <x-icon name="linkedin" class="size-4" />
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="bg-[#141515] py-20 text-center relative overflow-hidden border-t border-white/5">
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
        <div class="max-w-3xl mx-auto px-6 relative z-10">
            <h2 class="text-3xl sm:text-4xl lg:text-[2.5rem] font-display text-white mb-4">{{ $aboutContent['cta_title'] }}</h2>
            <p class="text-[15px] text-gray-400 mb-8">{{ $aboutContent['cta_body'] }}</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ $aboutContent['cta_url'] }}" class="inline-flex items-center justify-center bg-[#d6b562] hover:bg-[#c4a452] text-white px-8 py-3.5 rounded text-[13px] font-semibold transition-colors">
                    {{ $aboutContent['cta_label'] }} <x-icon name="arrow-right" class="size-3.5 ml-2" />
                </a>
                <a href="/contact" class="inline-flex items-center justify-center border border-white/20 hover:bg-white/5 text-white px-8 py-3.5 rounded text-[13px] font-semibold transition-colors">
                    Contact Us
                </a>
            </div>
        </div>
    </section>
</div>