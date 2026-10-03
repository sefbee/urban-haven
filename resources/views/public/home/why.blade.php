<section class="uh-home-section">
    <div class="uh-container">
        <div class="max-w-2xl">
            <h2 class="uh-home-title">{{ $about['title'] ?? __('Why Urban Haven?') }}</h2>
            <p class="uh-home-lede">
                {{ $about['body'] ?? __('One place to find Urban Haven properties and projects, with the details you need to decide.') }}
            </p>
        </div>

        <ul class="mt-14 grid gap-10 sm:grid-cols-2">
            @foreach([
                ['title' => __('Our own inventory'), 'body' => __('Every property and project here is published by the Urban Haven team, not collected from other sellers.')],
                ['title' => __('Clear information'), 'body' => __('Price (or "price on request"), size, location and current availability are shown on each listing.')],
                ['title' => __('Search the way you think'), 'body' => __('Filter by purpose, type, location, budget and bedrooms, on a list or a map.')],
                ['title' => __('One team from enquiry to visit'), 'body' => __('The same sales team answers your questions and arranges your site visit.')],
            ] as $point)
                <li>
                    <h3 class="text-xl font-semibold tracking-tight">{{ $point['title'] }}</h3>
                    <p class="mt-2 max-w-md text-[var(--color-muted)] leading-relaxed">{{ $point['body'] }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
