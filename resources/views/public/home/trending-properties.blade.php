<section class="uh-section uh-band-paper uh-trending-section" aria-labelledby="trending-title">
    <div class="uh-container">
        <header class="uh-trending-head" data-reveal>
            <h2 id="trending-title" class="uh-h2">{{ __('Trending Properties in Bangladesh') }}</h2>
            <x-ui.pill-link variant="light" :href="route('properties.index')">
                {{ __('View all') }}
            </x-ui.pill-link>
        </header>

        <div class="uh-trending-grid" role="list" aria-label="{{ __('Trending properties') }}">
            @foreach($trending as $property)
                <div role="listitem">
                    @include('public.partials.property-card', [
                        'property' => $property,
                        'revealIndex' => $loop->index % 3,
                        'showShortlist' => false,
                        'showDetails' => true,
                        'showContact' => true,
                        'showFavoriteAction' => true,
                        'priceInBody' => true,
                        'detailsLabel' => __('View Details'),
                        'detailsClass' => 'uh-trending-details',
                        'detailsIconOnly' => true,
                    ])
                </div>
            @endforeach
        </div>
    </div>
</section>
