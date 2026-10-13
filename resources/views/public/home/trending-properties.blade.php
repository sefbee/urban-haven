<section class="uh-section uh-band-paper uh-trending-section" @if(filled($sectionContent['title'])) aria-labelledby="trending-title" @else aria-label="{{ __('Trending properties') }}" @endif>
    <div class="uh-container">
        @if(filled($sectionContent['title']) || filled($sectionContent['link_label']))
            <header class="uh-trending-head" data-reveal>
                @if(filled($sectionContent['title']))
                    <h2 id="trending-title" class="uh-h2">{{ $sectionContent['title'] }}</h2>
                @endif
                @if(filled($sectionContent['link_label']))
                    <x-ui.pill-link variant="light" :href="route('properties.index')">
                        {{ $sectionContent['link_label'] }}
                    </x-ui.pill-link>
                @endif
            </header>
        @endif

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
