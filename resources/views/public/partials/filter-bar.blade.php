@php
    $browseRoute ??= 'properties.index';
    $listing = (string) ($filters['listing_type'] ?? '');
    $kept = request()->except(['listing_type', 'page']);
    $selectedAreaIds = array_values(array_filter(array_map('intval', (array) ($filters['location_area_ids'] ?? []))));
    $priceBand = (string) ($filters['price_band'] ?? '');
    $priceGroups = $listing === 'rent'
        ? config('urbanhaven.price_bands.rent.options', [])
        : config('urbanhaven.price_bands.sale.options', []);
@endphp

<div class="uh-filter-bar">
    <div class="uh-container">
        <form id="property-filters" method="GET" action="{{ route($browseRoute) }}"
              class="uh-filter-grid"
              @submit="submitting = true; $event.target.querySelectorAll('input, select, textarea').forEach((field) => { if (field.value === '' && field.type !== 'checkbox' && field.type !== 'hidden') field.disabled = true })">
            @if($listing !== '')
                <input type="hidden" name="listing_type" value="{{ $listing }}">
            @endif
            @if(filled($filters['q'] ?? null))
                <input type="hidden" name="q" value="{{ $filters['q'] }}">
            @endif
            <input type="hidden" name="lat" value="{{ $filters['lat'] ?? '' }}">
            <input type="hidden" name="lng" value="{{ $filters['lng'] ?? '' }}">
            <input type="hidden" name="radius_km" value="{{ $filters['radius_km'] ?? '3' }}">

            @if(count($purposes) > 1)
                <div class="uh-seg uh-filter-wide justify-self-start" aria-label="{{ __('Buy or rent') }}">
                    <a href="{{ route($browseRoute, $kept) }}" @class(['uh-seg-btn', 'is-on' => $listing === ''])>{{ __('All') }}</a>
                    <a href="{{ route($browseRoute, ['listing_type' => 'sale'] + $kept) }}" @if($listing === 'sale') aria-current="page" @endif
                       class="uh-seg-btn">{{ __('Buy') }}</a>
                    <a href="{{ route($browseRoute, ['listing_type' => 'rent'] + $kept) }}" @if($listing === 'rent') aria-current="page" @endif
                       class="uh-seg-btn">{{ __('Rent') }}</a>
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

            <div class="uh-field">
                <label class="sr-only" for="price-range">{{ __('Price Range') }}</label>
                <select id="price-range" name="price_band" class="uh-select" data-uh-select data-uh-select-clear="true">
                    <option value="">{{ __('Price Range') }}</option>
                    @foreach($priceGroups as $value => $band)
                        <option value="{{ $value }}" @selected($priceBand === $value)>{{ $band['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <x-ui.select name="property_type_id" :label="__('Property Type')" sr-label data-uh-select data-uh-select-clear="true">
                <option value="">{{ __('Property Type') }}</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" @selected(($filters['property_type_id'] ?? '') == $type->id)>{{ $type->label }}</option>
                @endforeach
            </x-ui.select>

            <button type="button" class="uh-btn-secondary" @click="toggleFilters()"
                    :aria-expanded="filtersOpen.toString()" aria-controls="more-filters">
                {{ __('Filters') }}
                @if($hasAdvanced)
                    <span class="uh-search-dot" aria-hidden="true"></span>
                    <span class="sr-only">{{ __('(active)') }}</span>
                @endif
            </button>

            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                {{ __('Search') }}
            </button>
        </form>
    </div>
    <span class="uh-progress" x-show="submitting" x-cloak aria-hidden="true"></span>
</div>
@include('public.partials.filter-drawer')
