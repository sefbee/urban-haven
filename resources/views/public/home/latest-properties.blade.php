@php
    $defaultTab = $latestSale->isNotEmpty() ? 'sale' : 'rent';
    $showTabs = $latestSale->isNotEmpty() && $latestRent->isNotEmpty();
@endphp

<section class="uh-home-section">
    <div class="uh-container" x-data="uhHomeTabs('{{ $defaultTab }}')">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="uh-home-title">{{ __('Latest Properties') }}</h2>
                <p class="uh-home-lede">{{ __('Recently added properties from Urban Haven.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-6">
                @if($showTabs)
                    <div class="flex gap-6" role="group" aria-label="{{ __('Listing type') }}">
                        <button type="button" class="uh-home-tab" :aria-pressed="(tab === 'sale').toString()"
                                :class="tab === 'sale' ? 'uh-home-tab-active' : ''" @click="tab = 'sale'">{{ __('For sale') }}</button>
                        <button type="button" class="uh-home-tab" :aria-pressed="(tab === 'rent').toString()"
                                :class="tab === 'rent' ? 'uh-home-tab-active' : ''" @click="tab = 'rent'">{{ __('For rent') }}</button>
                    </div>
                @endif
                <a class="uh-home-link text-sm" href="{{ route('properties.index') }}"
                   :href="@js(route('properties.index')) + '?listing_type=' + tab">
                    {{ __('View All Properties') }}
                </a>
            </div>
        </div>

        @if($latestSale->isNotEmpty())
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" x-bind:class="tab === 'sale' ? '' : 'hidden'">
                @foreach($latestSale as $property)
                    @include('public.partials.home-tile', ['property' => $property])
                @endforeach
            </div>
        @endif

        @if($latestRent->isNotEmpty())
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" x-bind:class="tab === 'rent' ? '' : 'hidden'">
                @foreach($latestRent as $property)
                    @include('public.partials.home-tile', ['property' => $property])
                @endforeach
            </div>
        @endif
    </div>
</section>
