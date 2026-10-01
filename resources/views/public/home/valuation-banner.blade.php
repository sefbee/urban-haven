<section class="bg-hero pb-10 md:pb-12" aria-label="{{ __('Valuation Tool') }}">
    <div class="uh-container">
        <a href="{{ route('tools') }}#valuation"
           class="flex flex-col overflow-hidden rounded-2xl border border-hero-muted bg-white text-ink shadow-[0_18px_40px_-24px_rgba(0,0,0,0.45)] sm:flex-row sm:items-center">
            <span class="flex flex-1 items-center gap-4 px-5 py-5 sm:px-7 sm:py-6">
                <span class="hidden size-16 shrink-0 items-center justify-center rounded-2xl bg-emerald/10 text-emerald sm:flex" aria-hidden="true">
                    <x-icon name="calculator" class="size-8" />
                </span>
                <span class="text-lg font-semibold tracking-tight sm:text-xl">
                    {{ __('Try our free online property valuation tool') }}
                </span>
            </span>
            <span class="flex items-center justify-between gap-4 border-t border-line px-5 py-4 sm:border-t-0 sm:px-7">
                <span class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-lg bg-emerald text-white">
                        <x-icon name="home" class="size-5" />
                    </span>
                    <span class="leading-none">
                        <span class="block text-sm font-bold">Urban Haven</span>
                        <span class="mt-1 block text-[0.625rem] uppercase tracking-[0.16em] text-[var(--color-muted)]">{{ __('Your trusted property advisor') }}</span>
                    </span>
                </span>
                <span class="inline-flex min-h-10 items-center rounded-lg bg-emerald px-4 text-xs font-bold tracking-wide text-white uppercase">{{ __('Calculate Now') }}</span>
            </span>
        </a>
    </div>
</section>
