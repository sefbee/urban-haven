@php
    $defaultTab = $featuredSale->isNotEmpty() ? 'sale' : 'rent';
    $showTabs = $featuredSale->isNotEmpty() && $featuredRent->isNotEmpty();
@endphp

<section class="uh-section border-t border-line bg-cream">
    <div class="uh-container" x-data="uhHomeTabs('{{ $defaultTab }}')">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="max-w-2xl">
                <h2 class="uh-h2">{{ __('Featured Properties') }}</h2>
                <p class="uh-lede mt-3">{{ __('Explore selected properties available through Urban Haven.') }}</p>
            </div>
            @if($showTabs)
                <div class="flex flex-wrap gap-2" role="group" aria-label="{{ __('Listing type') }}">
                    <button type="button" class="uh-home-tab" :aria-pressed="(tab === 'sale').toString()"
                            :class="tab === 'sale' ? 'uh-home-tab-active' : ''" @click="tab = 'sale'">{{ __('Buy') }}</button>
                    <button type="button" class="uh-home-tab" :aria-pressed="(tab === 'rent').toString()"
                            :class="tab === 'rent' ? 'uh-home-tab-active' : ''" @click="tab = 'rent'">{{ __('Rent') }}</button>
                </div>
            @endif
        </div>

        @if($featuredSale->isNotEmpty())
            <div class="uh-home-scroll mt-8" x-bind:class="tab === 'sale' ? '' : 'hidden'">
                @foreach($featuredSale as $property)
                    @include('public.partials.property-card', ['property' => $property])
                @endforeach
            </div>
        @endif

        @if($featuredRent->isNotEmpty())
            <div class="uh-home-scroll mt-8" x-bind:class="tab === 'rent' ? '' : 'hidden'">
                @foreach($featuredRent as $property)
                    @include('public.partials.property-card', ['property' => $property])
                @endforeach
            </div>
        @endif
    </div>
</section>
