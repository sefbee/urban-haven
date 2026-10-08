@php
    $browseRoute ??= 'properties.index';
    $listing = (string) ($filters['listing_type'] ?? '');
    $kept = request()->except(['listing_type', 'page']);
    $selectedAreaIds = array_values(array_filter(array_map('intval', (array) ($filters['location_area_ids'] ?? []))));
    $priceBand = (string) ($filters['price_band'] ?? '');
    $selectedPriceBand = collect(config('urbanhaven.price_bands.'.($listing === 'rent' ? 'rent' : 'sale').'.options', []))->get($priceBand, []);
    $initialMinPrice = $filters['min_price'] ?? ($selectedPriceBand['min'] ?? '');
    $initialMaxPrice = $filters['max_price'] ?? ($selectedPriceBand['max'] ?? '');
    $priceStops = [
        'sale' => [0, 1_000_000, 2_000_000, 3_000_000, 4_000_000, 5_000_000, 6_000_000, 7_500_000, 10_000_000, 12_500_000, 15_000_000, 20_000_000, 25_000_000, 30_000_000, 40_000_000, 50_000_000, 75_000_000, 100_000_000, 150_000_000, 200_000_000],
        'rent' => [0, 10_000, 15_000, 20_000, 25_000, 30_000, 40_000, 50_000, 60_000, 75_000, 100_000, 125_000, 150_000, 200_000, 250_000, 300_000, 500_000],
    ];
    $priceLabels = [
        'anyPrice' => __('Price range'),
        'from' => __('From :price'),
        'upTo' => __('Up to :price'),
        'crore' => __('crore'),
        'lakh' => __('lakh'),
    ];
    $typeOptions = $types->map(fn ($propertyType): array => [
        'id' => (string) $propertyType->id,
        'label' => $propertyType->label,
        'category' => $propertyType->category,
    ])->values();
    $typeLabels = [
        'any' => __('Property Type'),
        'typesMore' => __(':first +:count'),
        'categories' => \App\Models\PropertyType::categoryLabels(),
    ];
@endphp

<div class="uh-filter-bar">
    <div class="uh-container">
        <form id="property-filters" x-ref="form" method="GET" action="{{ route($browseRoute) }}"
              class="uh-filter-grid"
              @submit.prevent="search($event.target)"
              @change.debounce.250ms="search($event.target.form || $el)">
            <input x-ref="status" type="hidden" name="listing_type" value="{{ $listing }}">
            @if(filled($filters['q'] ?? null))
                <input type="hidden" name="q" value="{{ $filters['q'] }}">
            @endif
            <input type="hidden" name="lat" value="{{ $filters['lat'] ?? '' }}">
            <input type="hidden" name="lng" value="{{ $filters['lng'] ?? '' }}">
            <input type="hidden" name="radius_km" value="{{ $filters['radius_km'] ?? '3' }}">

            @if(count($purposes) > 1)
                <div class="uh-status-picker" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" class="uh-select uh-status-trigger" @click="open = !open" :aria-expanded="open.toString()">
                        <span>{{ __('Property Status') }}</span><x-icon name="chevron-down" class="size-4" />
                    </button>
                    <div class="uh-status-popover" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                        <p class="uh-search-group-title">{{ __('Property Status') }}</p>
                        <div class="uh-status-options" role="group" aria-label="{{ __('Property Status') }}">
                            <button type="button" @click="document.querySelector('#property-filters [name=listing_type]').value = 'sale'; window.dispatchEvent(new CustomEvent('uh-listing-purpose', { detail: 'sale' })); open = false; $nextTick(() => search(document.getElementById('property-filters')))" @class(['is-active' => $listing === 'sale'])>{{ __('For sale') }}</button>
                            <button type="button" @click="document.querySelector('#property-filters [name=listing_type]').value = 'rent'; window.dispatchEvent(new CustomEvent('uh-listing-purpose', { detail: 'rent' })); open = false; $nextTick(() => search(document.getElementById('property-filters')))" @class(['is-active' => $listing === 'rent'])>{{ __('For rent') }}</button>
                        </div>
                        <button type="button" class="uh-status-all" @click="document.querySelector('#property-filters [name=listing_type]').value = ''; window.dispatchEvent(new CustomEvent('uh-listing-purpose', { detail: 'sale' })); open = false; $nextTick(() => search(document.getElementById('property-filters')))">{{ __('Any status') }}</button>
                    </div>
                </div>
            @endif

            <div class="uh-filter-wide uh-filter-field is-select2 min-w-0">
                <x-icon name="search" class="size-4 shrink-0 text-[var(--uh-faint)]" />
                <label class="sr-only" for="location-query">{{ __('Location') }}</label>
                <select id="location-query" name="location_area_ids[]" multiple
                        data-uh-select data-uh-select-ajax="{{ route('search.locations') }}"
                        data-placeholder="{{ __('Search an area') }}"
                        data-uh-select-empty="{{ __('No matching areas') }}"
                        data-uh-select-searching="{{ __('Searching…') }}">
                    @foreach($areas->whereIn('id', $selectedAreaIds) as $selectedArea)
                        <option value="{{ $selectedArea->id }}" selected>{{ $selectedArea->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="uh-icon-action shrink-0" x-data="uhNearMe()" @click="locate()"
                        :aria-busy="locating.toString()" :disabled="locating"
                        aria-label="{{ __('Properties Near Me') }}" title="{{ __('Properties Near Me') }}">
                    <x-icon name="locate" class="size-4" x-show="!locating" />
                    <span class="uh-spinner" x-show="locating" x-cloak></span>
                </button>
            </div>

            <div class="uh-search-cell is-budget" x-data="uhHeroSearch({ initial: @js($listing ?: 'sale'), initialMin: @js($initialMinPrice), initialMax: @js($initialMaxPrice), labels: @js($priceLabels), stops: @js($priceStops) })"
                 @click.outside="close()" @keydown.escape="close(true)" @uh-listing-purpose.window="setPurpose($event.detail)">
                <span id="find-price-label" class="sr-only">{{ __('Price range') }}</span>
                <button type="button" class="uh-search-control uh-search-toggle" :aria-labelledby="hasMin || hasMax ? 'find-price-label find-price-value' : 'find-price-label'"
                        aria-labelledby="find-price-label" aria-controls="find-price-panel"
                        :aria-expanded="(open === 'price').toString()" @click="toggle('price')">
                    <span id="find-price-value" class="uh-search-value" :class="{ 'is-set': hasMin || hasMax }" x-text="priceSummary">{{ __('Price range') }}</span>
                    <x-icon name="chevron-down" class="uh-search-chevron" />
                </button>
                <input type="hidden" name="min_price" :value="min" :disabled="! hasMin">
                <input type="hidden" name="max_price" :value="max" :disabled="! hasMax">

                <div id="find-price-panel" class="uh-search-panel is-price" role="group" aria-labelledby="find-price-label"
                     x-show="open === 'price'" x-cloak x-transition.opacity.duration.150ms @keydown.enter.prevent="close(true)">
                    <div class="uh-search-group">
                        <p class="uh-search-group-title">{{ __('Price range') }}</p>
                        <div class="uh-price-range" :style="`--from: ${rangeFrom}%; --to: ${rangeTo}%`">
                            <span class="uh-price-range-track" aria-hidden="true"><span class="uh-price-range-fill"></span></span>
                            <input type="range" min="0" :max="lastStop" step="1" x-model.number="minIndex" @input="slideMin()"
                                   aria-label="{{ __('Minimum price') }}" :aria-valuetext="hasMin ? money(min) : @js(__('No minimum'))">
                            <input type="range" min="0" :max="lastStop" step="1" x-model.number="maxIndex" @input="slideMax()"
                                   aria-label="{{ __('Maximum price') }}" :aria-valuetext="hasMax ? money(max) : @js(__('No maximum'))">
                        </div>
                        <div class="uh-price-range-inputs">
                            <label class="uh-price-range-field">
                                <span>{{ __('Minimum') }}</span>
                                <span class="uh-price-range-box"><b aria-hidden="true">৳</b>
                                    <input type="number" min="0" step="1000" inputmode="numeric" placeholder="{{ __('No min') }}" x-model="min" @input="typedMin()">
                                </span>
                            </label>
                            <span class="uh-price-range-dash" aria-hidden="true">–</span>
                            <label class="uh-price-range-field">
                                <span>{{ __('Maximum') }}</span>
                                <span class="uh-price-range-box"><b aria-hidden="true">৳</b>
                                    <input type="number" min="0" step="1000" inputmode="numeric" placeholder="{{ __('No max') }}" x-model="max" @input="typedMax()">
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="uh-type-field"
                 x-data="uhTypePicker({ types: @js($typeOptions), category: @js($filters['category'] ?? ''), picked: @js($filters['property_type_ids'] ?? []), labels: @js($typeLabels) })"
                 @keydown.escape="close(true)" @click.outside="close()">
                <button type="button" class="uh-select uh-type-trigger" x-ref="trigger" @click="toggle()"
                        aria-controls="filter-type-panel" :aria-expanded="open.toString()" aria-expanded="false"
                        :class="{ 'is-set': summary !== @js($typeLabels['any']) }">
                    <span class="sr-only">{{ __('Property Type') }}:</span>
                    <span class="uh-type-summary" x-text="summary">{{ $typeLabels['any'] }}</span>
                </button>
                <input type="hidden" name="category" :value="category" :disabled="category === ''">
                <template x-for="id in pickedTypes" :key="'picked' + id">
                    <input type="hidden" name="property_type_ids[]" :value="id">
                </template>
                <div id="filter-type-panel" class="uh-type-popover" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                    @include('public.partials.type-picker')
                    <div class="uh-search-panel-foot">
                        <button type="button" class="uh-search-reset" @click="clearTypes()">{{ __('Reset') }}</button>
                        <button type="submit" class="uh-search-apply">{{ __('Apply') }}</button>
                    </div>
                </div>
            </div>

            @if($mapAvailable ?? true)
                <a class="uh-btn-secondary uh-filter-map" href="{{ route('map', request()->except('page')) }}" data-track="map_view_click" data-track-location="filter_bar"
                   aria-label="{{ __('Map view') }}" title="{{ __('Map view') }}">
                    <x-icon name="map" class="size-4" />
                </a>
            @endif

            <button type="button" class="uh-btn-secondary" @click="toggleFilters()"
                    :aria-expanded="filtersOpen.toString()" aria-controls="more-filters"
                    aria-label="{{ __('More Filters') }}" title="{{ __('More Filters') }}">
                <x-icon name="filter" class="size-4" />
                @if($hasAdvanced)
                    <span class="uh-search-dot" aria-hidden="true"></span>
                    <span class="sr-only">{{ __('(active)') }}</span>
                @endif
            </button>

        </form>
    </div>
    <span class="uh-progress" x-show="submitting" x-cloak aria-hidden="true"></span>
</div>
@include('public.partials.filter-drawer')
