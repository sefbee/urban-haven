<section class="uh-section border-t border-line bg-cream">
    <div class="uh-container">
        <div class="max-w-2xl">
            <h2 class="uh-h2">{{ $about['title'] ?? __('Why Urban Haven?') }}</h2>
            <p class="uh-lede mt-3">
                {{ $about['body'] ?? __('One place to find Urban Haven properties and projects, with the details you need to decide.') }}
            </p>
        </div>

        <ul class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['icon' => 'building', 'title' => __('Our own inventory'), 'body' => __('Every property and project here is published by the Urban Haven team, not collected from other sellers.')],
                ['icon' => 'document', 'title' => __('Clear information'), 'body' => __('Price (or "price on request"), size, location and current availability are shown on each listing.')],
                ['icon' => 'search', 'title' => __('Search the way you think'), 'body' => __('Filter by purpose, type, location, budget and bedrooms, on a list or a map.')],
                ['icon' => 'phone', 'title' => __('One team from enquiry to visit'), 'body' => __('The same sales team answers your questions and arranges your site visit.')],
            ] as $point)
                <li class="rounded-2xl bg-paper px-5 py-5 ring-1 ring-line">
                    <span class="flex size-10 items-center justify-center rounded-lg bg-forest/8 text-forest">
                        <x-icon :name="$point['icon']" class="size-5" />
                    </span>
                    <h3 class="uh-h4 mt-4">{{ $point['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-[var(--color-muted)]">{{ $point['body'] }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
