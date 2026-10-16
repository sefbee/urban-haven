<section class="uh-section uh-band-paper uh-trending-section" @if(filled($sectionContent['title'])) aria-labelledby="trending-title" @else aria-label="{{ __('Trending properties') }}" @endif>
    <div class="uh-container">
        @if(filled($sectionContent['title']) || filled($sectionContent['link_label']))
            <header class="uh-trending-head" data-reveal>
                <a class="uh-home-mobile-section-action" href="{{ route('properties.index') }}">{{ $sectionContent['link_label'] ?: __('Explore') }}</a>
                @if(filled($sectionContent['title']))
                    <h2 id="trending-title" class="uh-h2">{{ $sectionContent['title'] }}</h2>
                @endif
                @if(filled($sectionContent['link_label']))
                    <x-ui.pill-link class="uh-home-desktop-section-action" variant="light" :href="route('properties.index')">{{ $sectionContent['link_label'] }}</x-ui.pill-link>
                @endif
            </header>
        @endif

        <div class="uh-home-mobile-rail" x-data="uhRail()">
            <button type="button" class="uh-housing-arrow uh-home-mobile-arrow is-prev" x-show="canPrev" x-cloak
                    @click="prev()" aria-label="{{ __('Previous trending properties') }}">
                <x-icon name="chevron-left" class="size-4" />
            </button>
            <div class="uh-trending-grid uh-home-mobile-track" x-ref="scroller" tabindex="0" role="list" aria-label="{{ __('Trending properties') }}">
                @foreach($trending as $property)
                    <div class="uh-home-mobile-item" role="listitem">
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
            <button type="button" class="uh-housing-arrow uh-home-mobile-arrow is-next" x-show="canNext" x-cloak
                    @click="next()" aria-label="{{ __('Next trending properties') }}">
                <x-icon name="chevron-right" class="size-4" />
            </button>
        </div>
    </div>
</section>
