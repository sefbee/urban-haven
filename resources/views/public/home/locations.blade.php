<section id="home-locations" class="uh-section uh-band-paper scroll-mt-20" aria-labelledby="locations-title">
    <div class="uh-container">
        <header class="uh-section-head" data-reveal>
            <h2 id="locations-title" class="uh-h2">{{ __('Explore Real Estate in Bangladesh') }}</h2>
            <p class="uh-lede">{{ __('') }}</p>
            <x-ui.pill-link :href="route('properties.index', ['view' => 'map'])">{{ __('See them on the map') }}</x-ui.pill-link>
        </header>

        <ul class="uh-places">
            @foreach($areas as $area)
                <li data-reveal style="--uh-i: {{ $loop->index % 4 }}">
                    <a href="{{ $area->hasLandingPage() ? route('locations.show', $area->slug) : route('properties.index', ['location_area_id' => $area->id]) }}"
                       class="uh-place">
                        <span class="uh-place-icon" aria-hidden="true">
                            <x-icon name="pin" class="size-4" />
                        </span>
                        <span class="uh-place-copy">
                            <span class="uh-place-name">{{ $area->name }}</span>
                            <span class="uh-place-meta">
                                @if($area->city){{ $area->city }} · @endif{{ trans_choice(':count listing|:count listings', $area->properties_count, ['count' => $area->properties_count]) }}
                            </span>
                        </span>
                        <x-icon name="arrow-right" class="uh-place-arrow" />
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
