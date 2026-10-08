@php
    $aboutContent = \App\Support\AboutPageContent::resolve($page->layout_content);
    $aboutImages = $page->galleryImages();
    $heroImage = $aboutImages->get(0);
    $storyImage = $aboutImages->get(1) ?? $heroImage;
@endphp

<div class="uh-container space-y-5 pb-12 pt-8 sm:space-y-7 sm:pb-16 sm:pt-12 lg:pb-20">
    <div class="flex items-center justify-between gap-4">
        <span class="hidden text-xs font-medium uppercase tracking-[0.2em] text-muted sm:block">{{ __('A better way home') }}</span>
    </div>

    <section class="relative isolate overflow-hidden rounded-[2rem] bg-night text-cream sm:rounded-[2.5rem]" aria-labelledby="about-title">
        <img src="{{ $heroImage?->url(1920) ?? 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=2200&q=85' }}"
             alt="{{ $heroImage?->alt(app()->getLocale()) ?: __('Warm, thoughtfully designed contemporary home') }}"
             class="absolute inset-0 -z-20 size-full object-cover opacity-55" fetchpriority="high">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-night via-night/75 to-night/10"></div>
        <div class="grid min-h-[34rem] items-end gap-12 p-7 sm:min-h-[42rem] sm:p-12 lg:grid-cols-[1.15fr_.85fr] lg:items-center lg:p-16">
            <div class="max-w-3xl py-8 lg:py-16">
                <p class="mb-5 text-xs font-semibold uppercase tracking-[0.22em] text-gold-soft">{{ $aboutContent['hero_eyebrow'] }}</p>
                <h1 id="about-title" class="max-w-3xl text-balance text-5xl leading-[0.98] font-medium tracking-[-0.055em] sm:text-7xl lg:text-[5.5rem]">{{ $aboutContent['hero_title'] }}</h1>
                <p class="mt-7 max-w-xl text-base leading-relaxed text-cream/80 sm:text-lg">{{ $aboutContent['hero_intro'] }}</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ $aboutContent['hero_primary_url'] }}" class="inline-flex min-h-12 items-center justify-center rounded-full bg-cream px-6 text-sm font-semibold text-night transition hover:bg-gold-soft">{{ $aboutContent['hero_primary_cta'] }}</a>
                    <a href="{{ $aboutContent['hero_secondary_url'] }}" class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/35 px-6 text-sm font-semibold text-white transition hover:bg-white/10">{{ $aboutContent['hero_secondary_cta'] }}</a>
                </div>
            </div>
            <div class="hidden max-w-sm justify-self-end rounded-3xl border border-white/20 bg-night/30 p-7 backdrop-blur-sm lg:block">
                <span class="text-sm text-cream/65">{{ $aboutContent['hero_quote_kicker'] }}</span>
                <p class="mt-4 text-2xl leading-snug font-medium tracking-tight">{{ $aboutContent['hero_quote'] }}</p>
                <span class="mt-8 block text-xs font-semibold uppercase tracking-[0.16em] text-gold-soft">{{ $aboutContent['hero_quote_label'] }}</span>
            </div>
        </div>
        <div class="absolute right-8 top-8 hidden size-24 items-center justify-center rounded-full border border-white/30 px-3 text-center text-[0.65rem] leading-relaxed font-semibold uppercase tracking-[0.16em] text-white/80 xl:flex">{{ $aboutContent['hero_badge'] }}</div>
    </section>

    <section class="grid overflow-hidden rounded-[2rem] bg-paper sm:rounded-[2.5rem] lg:grid-cols-[.78fr_1.22fr]" aria-labelledby="about-story-title">
        <div class="relative min-h-72 overflow-hidden sm:min-h-96 lg:min-h-[34rem]">
            <img src="{{ $storyImage?->url(1280) ?? 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=85' }}"
                 alt="{{ $storyImage?->alt(app()->getLocale()) ?: __('Sunlit modern apartment interior') }}" class="absolute inset-0 size-full object-cover" loading="lazy">
        </div>
        <div class="flex flex-col justify-center p-7 sm:p-12 lg:p-16">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-ink">{{ $aboutContent['story_eyebrow'] }}</p>
            <h2 id="about-story-title" class="mt-5 max-w-2xl text-balance text-3xl leading-tight font-medium tracking-[-0.04em] sm:text-5xl">{{ $aboutContent['story_title'] }}</h2>
            <div class="uh-prose mt-6 max-w-2xl">{!! \Stevebauman\Purify\Facades\Purify::clean((string) $page->body) !!}</div>
            <a href="{{ route('properties.index') }}" class="mt-8 inline-flex w-fit items-center gap-3 rounded-full bg-night px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-forest">
                {{ __('Start your search') }}
                <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>
    </section>

    <section class="rounded-[2rem] bg-[#e9e5dc] p-7 sm:rounded-[2.5rem] sm:p-12 lg:p-16" aria-labelledby="about-values-title">
        <div class="grid gap-8 lg:grid-cols-[.9fr_1.1fr] lg:items-end">
            <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-ink">{{ $aboutContent['values_eyebrow'] }}</p>
                <h2 id="about-values-title" class="mt-4 max-w-xl text-balance text-4xl leading-[1.05] font-medium tracking-[-0.05em] sm:text-6xl">{{ $aboutContent['values_title'] }}</h2>
            </div>
            <p class="max-w-xl text-base leading-relaxed text-muted sm:justify-self-end sm:text-lg">{{ $aboutContent['values_intro'] }}</p>
        </div>
        <div class="mt-10 grid gap-3 md:grid-cols-3">
            @foreach([
                ['01', $aboutContent['value_one_title'], $aboutContent['value_one_text']],
                ['02', $aboutContent['value_two_title'], $aboutContent['value_two_text']],
                ['03', $aboutContent['value_three_title'], $aboutContent['value_three_text']],
            ] as [$number, $title, $description])
                <article class="rounded-3xl bg-white/75 p-6 sm:p-8">
                    <span class="text-sm font-medium tabular-nums text-gold-ink">{{ $number }}</span>
                    <h3 class="mt-8 text-xl leading-tight font-semibold tracking-tight sm:text-2xl">{{ $title }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-muted sm:text-base">{{ $description }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-[2rem] bg-night p-7 text-cream sm:rounded-[2.5rem] sm:p-12 lg:p-16" aria-labelledby="about-cta-title">
        <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-soft">{{ $aboutContent['cta_eyebrow'] }}</p>
                <h2 id="about-cta-title" class="mt-5 text-balance text-5xl leading-[0.98] font-medium tracking-[-0.055em] sm:text-7xl">{{ $aboutContent['cta_title'] }}</h2>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-cream/70 sm:text-lg">{{ $aboutContent['cta_body'] }}</p>
            </div>
            <a href="{{ $aboutContent['cta_url'] }}" class="inline-flex min-h-14 items-center justify-center gap-3 rounded-full bg-cream px-7 text-sm font-semibold text-night transition hover:bg-gold-soft">
                {{ $aboutContent['cta_label'] }}
                <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>
    </section>
</div>
