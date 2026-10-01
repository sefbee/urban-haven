<section class="border-b border-line bg-paper" aria-label="{{ __('Why buyers work with Urban Haven') }}">
    <div class="uh-container uh-section-tight">
        <ul class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                [
                    'icon' => 'shield',
                    'title' => __('Verified Properties'),
                    'detail' => $stats['properties'] > 0
                        ? __(':count listings', ['count' => $stats['properties']])
                        : __('Reviewed by the Urban Haven desk'),
                ],
                [
                    'icon' => 'sparkle',
                    'title' => __('Carefully Selected Listings'),
                    'detail' => __('Curated Urban Haven inventory'),
                ],
                [
                    'icon' => 'pin',
                    'title' => __('Prime Locations'),
                    'detail' => $stats['locations'] > 0
                        ? __(':count areas', ['count' => $stats['locations']])
                        : __('Homes in places we know well'),
                ],
                [
                    'icon' => 'users',
                    'title' => __('Dedicated Support'),
                    'detail' => __('Direct help from our Dhaka sales desk'),
                ],
            ] as $badge)
                <li class="flex items-start gap-3 rounded-xl bg-cream px-4 py-4 ring-1 ring-line">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-forest/8 text-forest">
                        <x-icon :name="$badge['icon']" class="size-5" />
                    </span>
                    <span>
                        <span class="block text-sm font-semibold leading-snug">{{ $badge['title'] }}</span>
                        <span class="mt-1 block text-xs text-[var(--color-muted)]">{{ $badge['detail'] }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
