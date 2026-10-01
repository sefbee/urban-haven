<section class="uh-section-tight border-t border-line bg-paper">
    <div class="uh-container">
        <h2 class="uh-h2">{{ __('Explore Properties by Location') }}</h2>
        <p class="uh-lede mt-3 max-w-2xl">{{ __('Neighbourhoods where Urban Haven currently has properties listed.') }}</p>

        <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($areas as $area)
                <a href="{{ $area->hasLandingPage() ? route('locations.show', $area->slug) : route('properties.index', ['location_area_id' => $area->id]) }}"
                   class="group flex items-center justify-between gap-3 rounded-xl bg-cream px-4 py-4 ring-1 ring-line transition hover:ring-forest">
                    <span class="min-w-0">
                        <span class="block truncate text-[0.9375rem] font-semibold">{{ $area->name }}</span>
                        <span class="mt-0.5 block text-xs text-[var(--color-muted)]">{{ $area->city }}</span>
                    </span>
                    <span class="flex shrink-0 items-center gap-2 text-xs font-semibold text-[var(--color-muted)]">
                        <span class="uh-numeric">{{ trans_choice(':count property|:count properties', $area->properties_count, ['count' => $area->properties_count]) }}</span>
                        <x-icon name="arrow-right" class="size-4 text-forest transition group-hover:translate-x-0.5" />
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
