@php
    $defaultTab = $latestSale->isNotEmpty() ? 'sale' : 'rent';
    $showTabs = $latestSale->isNotEmpty() && $latestRent->isNotEmpty();
@endphp

<section class="uh-section border-t border-line bg-paper">
    <div class="uh-container" x-data="uhHomeTabs('{{ $defaultTab }}')">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="max-w-2xl">
                <h2 class="uh-h2">{{ __('Latest Properties') }}</h2>
                <p class="uh-lede mt-3">{{ __('Recently added homes from Urban Haven inventory.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if($showTabs)
                    <div class="flex flex-wrap gap-2" role="tablist" aria-label="{{ __('Listing type') }}">
                        <button type="button" class="uh-home-tab" :aria-pressed="(tab === 'sale').toString()"
                                :class="tab === 'sale' ? 'uh-home-tab-active' : ''" @click="tab = 'sale'">{{ __('For sale') }}</button>
                        <button type="button" class="uh-home-tab" :aria-pressed="(tab === 'rent').toString()"
                                :class="tab === 'rent' ? 'uh-home-tab-active' : ''" @click="tab = 'rent'">{{ __('For rent') }}</button>
                    </div>
                @endif
                <a class="uh-link-quiet inline-flex items-center gap-1.5 text-sm" href="{{ route('properties.index') }}">
                    {{ __('View All Properties') }}
                    <x-icon name="arrow-right" class="size-4" />
                </a>
            </div>
        </div>

        @if($latestSale->isNotEmpty())
            <div class="uh-discover-grid mt-8" x-bind:class="tab === 'sale' ? '' : 'hidden'">
                @foreach($latestSale as $property)
                    @include('public.partials.property-card', ['property' => $property])
                @endforeach
            </div>
        @endif

        @if($latestRent->isNotEmpty())
            <div class="uh-discover-grid mt-8" x-bind:class="tab === 'rent' ? '' : 'hidden'">
                @foreach($latestRent as $property)
                    @include('public.partials.property-card', ['property' => $property])
                @endforeach
            </div>
        @endif
    </div>
</section>
