<section class="uh-section border-t border-line bg-cream">
    <div class="uh-container">
        <div class="max-w-2xl">
            <h2 class="uh-h2">{{ __('Why Urban Haven?') }}</h2>
            <p class="uh-lede mt-3">
                {{ __('One trusted destination for discovering Urban Haven properties and projects.') }}
            </p>
        </div>

        <ul class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['icon' => 'shield', 'title' => __('Verified, curated properties'), 'body' => __('Every listing is published by our desk after the details have been checked.')],
                ['icon' => 'document', 'title' => __('Transparent information'), 'body' => __('Prices, sizes, and availability are shown as we know them — no marketplace filler.')],
                ['icon' => 'search', 'title' => __('Convenient discovery'), 'body' => __('Search by purpose, type, location, and budget without leaving Urban Haven.')],
                ['icon' => 'phone', 'title' => __('Direct inquiry and support'), 'body' => __('The same Dhaka team answers questions, arranges viewings, and handles paperwork.')],
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
