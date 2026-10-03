@php
    $defaultTab = $featuredSale->isNotEmpty() ? 'sale' : 'rent';
    $showTabs = $featuredSale->isNotEmpty() && $featuredRent->isNotEmpty();
@endphp

<section class="uh-home-section uh-home-featured bg-white" x-data="uhHomeTabs('{{ $defaultTab }}')">
    <div class="uh-container">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="uh-home-title">{{ __('Featured Properties') }}</h2>
                <p class="uh-home-lede">{{ __('Explore selected properties available through Urban Haven.') }}</p>
            </div>
            @if($showTabs)
                <div class="flex gap-6" role="group" aria-label="{{ __('Listing type') }}">
                    <button type="button" class="uh-home-tab" :aria-pressed="(tab === 'sale').toString()"
                            :class="tab === 'sale' ? 'uh-home-tab-active' : ''" @click="tab = 'sale'">{{ __('Buy') }}</button>
                    <button type="button" class="uh-home-tab" :aria-pressed="(tab === 'rent').toString()"
                            :class="tab === 'rent' ? 'uh-home-tab-active' : ''" @click="tab = 'rent'">{{ __('Rent') }}</button>
                </div>
            @endif
        </div>
    </div>

    @if($featuredSale->isNotEmpty())
        <div class="uh-featured-breakout" x-bind:class="tab === 'sale' ? '' : 'hidden'">
            @include('public.partials.featured-track', ['properties' => $featuredSale])
        </div>
    @endif

    @if($featuredRent->isNotEmpty())
        <div class="uh-featured-breakout" x-bind:class="tab === 'rent' ? '' : 'hidden'">
            @include('public.partials.featured-track', ['properties' => $featuredRent])
        </div>
    @endif

    <div class="uh-container mt-8">
        <a class="uh-home-link text-sm" href="{{ route('properties.index') }}"
           :href="@js(route('properties.index')) + '?listing_type=' + tab">
            {{ __('View All Properties') }}
        </a>
    </div>
</section>
