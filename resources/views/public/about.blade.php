<div class="uh-container space-y-5 pb-12 pt-8 sm:space-y-7 sm:pb-16 sm:pt-12 lg:pb-20">
    <div class="flex items-center justify-between gap-4">
        <span class="hidden text-xs font-medium uppercase tracking-[0.2em] text-muted sm:block">{{ __('A better way home') }}</span>
    </div>

    <section class="relative isolate overflow-hidden rounded-[2rem] bg-night text-cream sm:rounded-[2.5rem]" aria-labelledby="about-title">
        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=2200&q=85"
             alt="{{ __('Warm, thoughtfully designed contemporary home') }}"
             class="absolute inset-0 -z-20 size-full object-cover opacity-55" fetchpriority="high">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-night via-night/75 to-night/10"></div>
        <div class="grid min-h-[34rem] items-end gap-12 p-7 sm:min-h-[42rem] sm:p-12 lg:grid-cols-[1.15fr_.85fr] lg:items-center lg:p-16">
            <div class="max-w-3xl py-8 lg:py-16">
                <p class="mb-5 text-xs font-semibold uppercase tracking-[0.22em] text-gold-soft">{{ __('A home is more than an address') }}</p>
                <h1 id="about-title" class="max-w-3xl text-balance text-5xl leading-[0.98] font-medium tracking-[-0.055em] sm:text-7xl lg:text-[5.5rem]">{{ __('Find a place for the life you want to live.') }}</h1>
                <p class="mt-7 max-w-xl text-base leading-relaxed text-cream/80 sm:text-lg">{{ __('Urban Haven brings property discovery and real human guidance together, so your next move can feel clear from the very first search.') }}</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('properties.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-full bg-cream px-6 text-sm font-semibold text-night transition hover:bg-gold-soft">{{ __('Explore properties') }}</a>
                    <a href="{{ route('cms.show', 'contact') }}" class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/35 px-6 text-sm font-semibold text-white transition hover:bg-white/10">{{ __('Talk to our team') }}</a>
                </div>
            </div>
            <div class="hidden max-w-sm justify-self-end rounded-3xl border border-white/20 bg-night/30 p-7 backdrop-blur-sm lg:block">
                <span class="text-sm text-cream/65">{{ __('Our point of view') }}</span>
                <p class="mt-4 text-2xl leading-snug font-medium tracking-tight">{{ __('Good decisions start with honest details and people who listen.') }}</p>
                <span class="mt-8 block text-xs font-semibold uppercase tracking-[0.16em] text-gold-soft">{{ __('The Urban Haven approach') }}</span>
            </div>
        </div>
        <div class="absolute right-8 top-8 hidden size-24 items-center justify-center rounded-full border border-white/30 px-3 text-center text-[0.65rem] leading-relaxed font-semibold uppercase tracking-[0.16em] text-white/80 xl:flex">{{ __('Made for your next chapter') }}</div>
    </section>

    <section class="grid overflow-hidden rounded-[2rem] bg-paper sm:rounded-[2.5rem] lg:grid-cols-[.78fr_1.22fr]" aria-labelledby="about-story-title">
        <div class="relative min-h-72 overflow-hidden sm:min-h-96 lg:min-h-[34rem]">
            <img src="https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=85"
                 alt="{{ __('Sunlit modern apartment interior') }}" class="absolute inset-0 size-full object-cover" loading="lazy">
        </div>
        <div class="flex flex-col justify-center p-7 sm:p-12 lg:p-16">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-ink">{{ __('Why we are here') }}</p>
            <h2 id="about-story-title" class="mt-5 max-w-2xl text-balance text-3xl leading-tight font-medium tracking-[-0.04em] sm:text-5xl">{{ __('A more thoughtful journey to your next home.') }}</h2>
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
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-ink">{{ __('What matters to us') }}</p>
                <h2 id="about-values-title" class="mt-4 max-w-xl text-balance text-4xl leading-[1.05] font-medium tracking-[-0.05em] sm:text-6xl">{{ __('The right home starts with the right experience.') }}</h2>
            </div>
            <p class="max-w-xl text-base leading-relaxed text-muted sm:justify-self-end sm:text-lg">{{ __('Finding a home can feel like a big decision. We make the search easier to navigate with useful information, considered choices and a team ready to help.') }}</p>
        </div>
        <div class="mt-10 grid gap-3 md:grid-cols-3">
            @foreach([
                ['01', __('Clarity at every step'), __('Straightforward property information helps you compare options and focus on what fits your life.')],
                ['02', __('People who listen'), __('Our team takes time to understand your priorities and answer the questions that matter to you.')],
                ['03', __('A search that feels personal'), __('From the first shortlist to arranging a visit, we help make your next move feel more manageable.')],
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
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-soft">{{ __('Your next chapter') }}</p>
                <h2 id="about-cta-title" class="mt-5 text-balance text-5xl leading-[0.98] font-medium tracking-[-0.055em] sm:text-7xl">{{ __('Let’s find a place that feels like yours.') }}</h2>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-cream/70 sm:text-lg">{{ __('Browse homes at your own pace, or tell us what you are looking for and we’ll help you take the next step.') }}</p>
            </div>
            <a href="{{ route('cms.show', 'contact') }}" class="inline-flex min-h-14 items-center justify-center gap-3 rounded-full bg-cream px-7 text-sm font-semibold text-night transition hover:bg-gold-soft">
                {{ __('Get in touch') }}
                <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>
    </section>
</div>
