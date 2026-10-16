@php
    $defaultTab = $latestSale->isNotEmpty() ? 'sale' : 'rent';
    $showTabs = $latestSale->isNotEmpty() && $latestRent->isNotEmpty();
@endphp

<section class="uh-section uh-band-paper uh-latest-section uh-trending-section" @if(filled($sectionContent['title'])) aria-labelledby="latest-title" @else aria-label="{{ __('Latest properties') }}" @endif>
    <div class="uh-container" x-data="uhHomeTabs('{{ $defaultTab }}')">
        @if(filled($sectionContent['title']) || filled($sectionContent['subtitle']) || filled($sectionContent['link_label']) || $showTabs)
        <header class="uh-section-head" data-reveal>
            @if(filled($sectionContent['title']))
                <h2 id="latest-title" class="uh-h2">{{ $sectionContent['title'] }}</h2>
            @endif
            <a class="uh-home-mobile-section-action" href="{{ route('properties.index', ['sort' => 'newest']) }}" x-bind:href="{{ Js::from(route('properties.index')) }} + '?sort=newest&listing_type=' + tab">{{ $sectionContent['link_label'] ?: __('View all') }}</a>
            @if(filled($sectionContent['subtitle']))
                <p class="uh-lede">{{ $sectionContent['subtitle'] }}</p>
            @endif
            @if($showTabs)
                <div class="uh-seg" role="group" aria-label="{{ __('Listing type') }}">
                    <button type="button" class="uh-seg-btn" :aria-pressed="(tab === 'sale').toString()"
                            :class="tab === 'sale' ? 'is-on' : ''" @click="tab = 'sale'">{{ __('For sale') }}</button>
                    <button type="button" class="uh-seg-btn" :aria-pressed="(tab === 'rent').toString()"
                            :class="tab === 'rent' ? 'is-on' : ''" @click="tab = 'rent'">{{ __('For rent') }}</button>
                </div>
            @endif
        </header>
        @endif

        @foreach(['sale' => $latestSale, 'rent' => $latestRent] as $purpose => $listings)
            @if($listings->isNotEmpty())
                <div class="uh-home-mobile-rail" x-data="uhRail()" x-show="tab === '{{ $purpose }}'" x-effect="tab; $nextTick(() => update())" x-cloak>
                    <button type="button" class="uh-housing-arrow uh-home-mobile-arrow is-prev" x-show="canPrev" x-cloak
                            @click="prev()" aria-label="{{ __('Previous latest properties') }}">
                        <x-icon name="chevron-left" class="size-4" />
                    </button>
                    <div class="uh-trending-grid uh-latest-grid uh-home-mobile-track" x-ref="scroller" tabindex="0"
                         role="list" aria-label="{{ __('Latest properties') }}">
                        @foreach($listings as $property)
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
                            @click="next()" aria-label="{{ __('Next latest properties') }}">
                        <x-icon name="chevron-right" class="size-4" />
                    </button>
                </div>
            @endif
        @endforeach

        @if(filled($sectionContent['link_label']))
            <div class="uh-section-foot uh-home-desktop-section-action">
                <x-ui.pill-link :href="route('properties.index', ['sort' => 'newest'])" x-bind:href="{{ Js::from(route('properties.index')) }} + '?sort=newest&listing_type=' + tab">
                    {{ $sectionContent['link_label'] }}
                </x-ui.pill-link>
            </div>
        @endif
    </div>
</section>
