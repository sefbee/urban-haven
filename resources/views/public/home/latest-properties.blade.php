@php
    $defaultTab = $latestSale->isNotEmpty() ? 'sale' : 'rent';
    $showTabs = $latestSale->isNotEmpty() && $latestRent->isNotEmpty();
@endphp

<section class="uh-section uh-band-paper uh-latest-section uh-trending-section" aria-labelledby="latest-title">
    <div class="uh-container" x-data="uhHomeTabs('{{ $defaultTab }}')">
        <header class="uh-section-head" data-reveal>
            <h2 id="latest-title" class="uh-h2">{{ __('Latest Property Listings in Bangladesh') }}</h2>
            <p class="uh-lede">{{ __('New to Urban Haven this week.') }}</p>
            @if($showTabs)
                <div class="uh-seg" role="group" aria-label="{{ __('Listing type') }}">
                    <button type="button" class="uh-seg-btn" :aria-pressed="(tab === 'sale').toString()"
                            :class="tab === 'sale' ? 'is-on' : ''" @click="tab = 'sale'">{{ __('For sale') }}</button>
                    <button type="button" class="uh-seg-btn" :aria-pressed="(tab === 'rent').toString()"
                            :class="tab === 'rent' ? 'is-on' : ''" @click="tab = 'rent'">{{ __('For rent') }}</button>
                </div>
            @endif
        </header>

        @foreach(['sale' => $latestSale, 'rent' => $latestRent] as $purpose => $listings)
            @if($listings->isNotEmpty())
                <div class="uh-trending-grid uh-latest-grid" x-show="tab === '{{ $purpose }}'" x-cloak
                     role="list" aria-label="{{ __('Latest properties') }}">
                    @foreach($listings as $property)
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
            @endif
        @endforeach

        <div class="uh-section-foot">
            <x-ui.pill-link :href="route('properties.index', ['sort' => 'newest'])" x-bind:href="{{ Js::from(route('properties.index')) }} + '?sort=newest&listing_type=' + tab">
                {{ __('See everything new') }}
            </x-ui.pill-link>
        </div>
    </div>
</section>
