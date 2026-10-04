@php
    $defaultTab = $featuredSale->isNotEmpty() ? 'sale' : 'rent';
    $showTabs = $featuredSale->isNotEmpty() && $featuredRent->isNotEmpty();
@endphp

<section class="uh-section uh-showcase-section" x-data="uhHomeTabs('{{ $defaultTab }}')" aria-labelledby="featured-title">
    <div class="uh-container">
        <header class="uh-section-head" data-reveal>
            <h2 id="featured-title" class="uh-h2">{{ __('Featured Properties') }}</h2>
            <p class="uh-lede">{{ __('A closer look at the places our team is featuring right now.') }}</p>
            @if($showTabs)
                <div class="uh-seg" role="group" aria-label="{{ __('Listing type') }}">
                    <button type="button" class="uh-seg-btn" :aria-pressed="(tab === 'sale').toString()"
                            :class="tab === 'sale' ? 'is-on' : ''" @click="tab = 'sale'">{{ __('Buy') }}</button>
                    <button type="button" class="uh-seg-btn" :aria-pressed="(tab === 'rent').toString()"
                            :class="tab === 'rent' ? 'is-on' : ''" @click="tab = 'rent'">{{ __('Rent') }}</button>
                </div>
            @endif
        </header>
    </div>

    @foreach(['sale' => $featuredSale, 'rent' => $featuredRent] as $purpose => $listings)
        @if($listings->isNotEmpty())
            <div class="uh-showcase" x-data="uhShowcase({{ $listings->count() }})" x-bind:class="tab === '{{ $purpose }}' ? '' : 'hidden'"
                 role="region" aria-roledescription="{{ __('carousel') }}"
                 aria-label="{{ $purpose === 'rent' ? __('Featured properties for rent') : __('Featured properties for sale') }}"
                 tabindex="0" @keydown.left.prevent="previous()" @keydown.right.prevent="next()"
                 @pointerdown="dragStart($event)" @pointerup="dragEnd($event)" @pointercancel="dragging = false"
                 data-reveal>
                <div class="uh-showcase-stage" aria-live="polite">
                    @foreach($listings as $property)
                        @include('public.partials.showcase-slide', [
                            'property' => $property,
                            'index' => $loop->index,
                            'total' => $loop->count,
                        ])
                    @endforeach

                    @if($listings->count() > 1)
                        <button type="button" class="uh-showcase-arrow is-prev" @click.stop="previous()" aria-label="{{ __('Previous property') }}">
                            <x-icon name="chevron-left" class="size-4" />
                        </button>
                        <button type="button" class="uh-showcase-arrow is-next" @click.stop="next()" aria-label="{{ __('Next property') }}">
                            <x-icon name="chevron-right" class="size-4" />
                        </button>
                    @endif
                </div>
            </div>
        @endif
    @endforeach

    <div class="uh-container">
        <div class="uh-section-foot">
            <a class="uh-arrow-link" href="{{ route('properties.index') }}"
               :href="@js(route('properties.index')) + '?listing_type=' + tab">
                <span x-text="tab === 'rent' ? @js(__('See every property for rent')) : @js(__('See every property for sale'))">{{ __('See every property') }}</span>
                <x-icon name="arrow-right" class="size-3.5" />
            </a>
        </div>
    </div>
</section>
