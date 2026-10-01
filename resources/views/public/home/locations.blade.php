<section class="uh-section-tight border-t border-line bg-sand">
    <div class="uh-container">
        <p class="uh-eyebrow">{{ __('Locations') }}</p>
        <h2 class="uh-h2 mt-2">{{ __('Explore properties by popular locations') }}</h2>
        <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($areas as $area)
                <a href="{{ route('properties.index', ['location_area_id' => $area->id]) }}"
                   class="group flex items-center justify-between gap-3 rounded-xl bg-paper px-4 py-4 ring-1 ring-line transition hover:ring-emerald">
                    <span class="min-w-0">
                        <span class="block truncate text-[0.9375rem] font-semibold">{{ $area->name }}</span>
                        <span class="mt-0.5 block text-xs text-[var(--color-muted)]">{{ $area->city }}</span>
                    </span>
                    <span class="flex shrink-0 items-center gap-2 text-xs font-semibold text-[var(--color-muted)]">
                        <span class="uh-numeric">{{ $area->properties_count }}</span>
                        <x-icon name="arrow-right" class="size-4 text-emerald transition group-hover:translate-x-0.5" />
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
