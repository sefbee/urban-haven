<?php

$file = 'resources/views/public/about.blade.php';
$content = file_get_contents($file);

$stats = <<<'REPLACE'
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
REPLACE;

$content = preg_replace('~<div class="pt-4 md:pt-0">.*?</div>\s*</div>\s*</div>\s*</section>~s', $stats."\n            </div>\n        </div>\n    </section>", $content);

$purpose = <<<'REPLACE'
        <div class="max-w-5xl mx-auto px-6 lg:px-8 text-center mb-16">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#d6b562] mb-4">{{ $aboutContent['purpose_eyebrow'] }}</p>
            <h2 class="text-3xl sm:text-4xl font-display text-gray-900">{{ $aboutContent['purpose_title'] }}</h2>
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
REPLACE;

$content = preg_replace('~<div class="max-w-5xl mx-auto px-6 lg:px-8 text-center mb-16">.*?</div>\s*</div>\s*</section>~s', $purpose."\n    </section>", $content, 1); // Only first match!

$values = <<<'REPLACE'
                @php
                    $values = [
                        ['shield', $aboutContent['value_one_title'], $aboutContent['value_one_text']],
                        ['users', $aboutContent['value_two_title'], $aboutContent['value_two_text']],
                        ['sparkle', $aboutContent['value_three_title'], $aboutContent['value_three_text']],
                        ['refresh', $aboutContent['value_four_title'], $aboutContent['value_four_text']],
                    ];
                @endphp
REPLACE;

$content = preg_replace('~@php\s+\$values = \[.*?\];\s+@endphp~s', $values, $content);

$choose = <<<'REPLACE'
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
REPLACE;

$content = preg_replace('~<div class="relative z-10">.*?</a>\s*</div>~s', $choose, $content);

$approach = <<<'REPLACE'
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
REPLACE;

$content = preg_replace('~<div class="max-w-4xl mx-auto px-6 lg:px-8 text-center mb-16">.*?@endphp~s', $approach, $content);

$team = <<<'REPLACE'
        <div class="max-w-5xl mx-auto px-6 lg:px-8 text-center mb-16">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#d6b562] mb-4">{{ $aboutContent['team_eyebrow'] }}</p>
            <h2 class="text-3xl sm:text-4xl font-display text-gray-900">{{ $aboutContent['team_title'] }}</h2>
        </div>
REPLACE;
$content = preg_replace('~<div class="max-w-5xl mx-auto px-6 lg:px-8 text-center mb-16">.*?</h2>\s*</div>~s', $team, $content, 1);

// Add the final CTA block to about page view! In original design I didn't see where CTA was, wait.
// Ah, the mockup bottom says "Let's Build Your Property Journey Together." "Book a Consultation / Contact Us"

$cta = <<<'REPLACE'
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
REPLACE;

$content = preg_replace('~</div>\s*$~s', $cta, $content);

file_put_contents($file, $content);
