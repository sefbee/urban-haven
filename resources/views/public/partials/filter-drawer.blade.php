@php
    $minBeds = (string) ($filters['min_beds'] ?? '');
    $minBaths = (string) ($filters['min_baths'] ?? '');
    $furnished = $filters['is_furnished'] ?? null;
@endphp

<div class="uh-scrim-modal z-[1200]" x-cloak
     x-bind:class="filtersOpen ? 'block' : 'hidden'"
     x-bind:aria-hidden="(!filtersOpen).toString()"
     @click="closeFilters()"></div>

<aside id="more-filters"
       class="uh-drawer fixed inset-y-0 right-0 z-[1201] flex-col"
       x-cloak
       x-bind:class="filtersOpen ? '!flex' : '!hidden'"
       role="dialog" aria-modal="true" aria-labelledby="more-filters-title"
       @change.debounce.250ms="search(document.getElementById('property-filters'))"
       x-bind:aria-hidden="(!filtersOpen).toString()"
       @click.stop @keydown.escape.stop="closeFilters()"
       @keydown.tab.prevent="filtersOpen && $trapFocus($el, $event)">
    <div class="uh-drawer-head">
        <div class="uh-drawer-heading">
            <span class="uh-drawer-mark"><x-icon name="filter" class="size-5" /></span>
            <div>
                <p class="uh-drawer-kicker">{{ __('Refine your search') }}</p>
                <h2 id="more-filters-title" class="uh-h4">{{ __('More Filters') }}</h2>
            </div>
        </div>
        <button type="button" class="uh-icon-btn" @click="closeFilters()" aria-label="{{ __('Close filters') }}">
            <x-icon name="close" class="size-5" />
        </button>
    </div>

    <div class="uh-drawer-body">
        <p class="uh-drawer-intro">{{ __('Fine tune your search with the details that matter most.') }}</p>
        <fieldset class="uh-drawer-group">
            <legend class="uh-legend">{{ __('Area size') }}</legend>
            <div class="flex flex-wrap gap-1.5">
                <label class="uh-choice">
                    <input type="radio" form="property-filters" name="area_band" value="" @checked(blank($filters['area_band'] ?? null))>
                    <span>{{ __('Any') }}</span>
                </label>
                @foreach(\App\Support\SearchBands::areas() as $key => $band)
                    <label class="uh-choice">
                        <input type="radio" form="property-filters" name="area_band" value="{{ $key }}" @checked(($filters['area_band'] ?? '') === $key)>
                        <span>{{ $band['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        @if($showBedroomFilters ?? true)
        <fieldset class="uh-drawer-group" x-data="{ count: {{ $minBeds !== '' ? (int) $minBeds : 0 }} }">
            <legend class="uh-legend">{{ __('Bedroom') }}</legend>
            <div class="mt-2 flex items-center gap-3">
                <button type="button" class="uh-icon-action" @click="count = Math.max(0, count - 1); $nextTick(() => search(document.getElementById('property-filters')))" aria-label="{{ __('Fewer bedrooms') }}">
                    <x-icon name="minus" class="size-4" />
                </button>
                <p class="min-w-10 text-center font-medium">
                    <span class="uh-numeric" x-text="count === 0 ? '{{ __('Any') }}' : (count >= 5 ? '5+' : count)">{{ $minBeds === '' ? __('Any') : ($minBeds === '5' ? '5+' : $minBeds) }}</span>
                </p>
                <button type="button" class="uh-icon-action" @click="count = Math.min(5, count + 1); $nextTick(() => search(document.getElementById('property-filters')))" aria-label="{{ __('More bedrooms') }}">
                    <x-icon name="plus" class="size-4" />
                </button>
                <input type="hidden" form="property-filters" name="min_beds" :value="count || ''" :disabled="count === 0">
            </div>
        </fieldset>

        <fieldset class="uh-drawer-group" x-data="{ count: {{ $minBaths !== '' ? (int) $minBaths : 0 }} }">
            <legend class="uh-legend">{{ __('Bathroom') }}</legend>
            <div class="mt-2 flex items-center gap-3">
                <button type="button" class="uh-icon-action" @click="count = Math.max(0, count - 1); $nextTick(() => search(document.getElementById('property-filters')))" aria-label="{{ __('Fewer bathrooms') }}">
                    <x-icon name="minus" class="size-4" />
                </button>
                <p class="min-w-10 text-center font-medium">
                    <span class="uh-numeric" x-text="count === 0 ? '{{ __('Any') }}' : (count >= 5 ? '5+' : count)">{{ $minBaths === '' ? __('Any') : ($minBaths === '5' ? '5+' : $minBaths) }}</span>
                </p>
                <button type="button" class="uh-icon-action" @click="count = Math.min(5, count + 1); $nextTick(() => search(document.getElementById('property-filters')))" aria-label="{{ __('More bathrooms') }}">
                    <x-icon name="plus" class="size-4" />
                </button>
                <input type="hidden" form="property-filters" name="min_baths" :value="count || ''" :disabled="count === 0">
            </div>
        </fieldset>

        @endif


        <fieldset class="uh-drawer-group">
            <legend class="uh-legend">{{ __('Furnished') }}</legend>
            <div class="grid gap-1">
                <label class="uh-choice">
                    <input type="radio" form="property-filters" name="is_furnished" value="" @checked($furnished === null)>
                    <span>{{ __('Any') }}</span>
                </label>
                <label class="uh-choice">
                    <input type="radio" form="property-filters" name="is_furnished" value="1" @checked($furnished === true || $furnished === '1')>
                    <span>{{ __('Yes') }}</span>
                </label>
                <label class="uh-choice">
                    <input type="radio" form="property-filters" name="is_furnished" value="0" @checked($furnished === false || $furnished === '0')>
                    <span>{{ __('No') }}</span>
                </label>
            </div>
        </fieldset>
<fieldset class="uh-drawer-group">
            <legend class="uh-legend">{{ __('Property Facing') }}</legend>
            <div class="grid grid-cols-2 gap-1">
                @foreach(config('urbanhaven.facings') as $value => $label)
                    <label class="uh-choice">
                        <input type="radio" form="property-filters" name="facing" value="{{ $value }}" @checked(($filters['facing'] ?? '') === $value)>
                        <span>{{ __($label) }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="uh-drawer-group">
            <legend class="uh-legend">{{ __('Road Access (Feet)') }}</legend>
            <div class="grid grid-cols-2 gap-3">
                <x-ui.select name="min_road_width" form="property-filters" :label="__('Min')" sr-label data-uh-select data-uh-select-search="off" data-uh-select-clear="true">
                    <option value="">{{ __('Min') }}</option>
                    @foreach([10, 12, 15, 20, 30, 40] as $feet)
                        <option value="{{ $feet }}" @selected((string) ($filters['min_road_width'] ?? '') === (string) $feet)>{{ $feet }} ft</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="max_road_width" form="property-filters" :label="__('Max')" sr-label data-uh-select data-uh-select-search="off" data-uh-select-clear="true">
                    <option value="">{{ __('Max') }}</option>
                    @foreach([20, 30, 40, 60, 80, 100] as $feet)
                        <option value="{{ $feet }}" @selected((string) ($filters['max_road_width'] ?? '') === (string) $feet)>{{ $feet }} ft</option>
                    @endforeach
                </x-ui.select>
            </div>
        </fieldset>

@if($amenities->isNotEmpty())
            <fieldset class="uh-drawer-group">
                <legend class="uh-legend">{{ __('Amenities') }}</legend>
                <div class="grid gap-1">
                    @foreach($amenities as $amenity)
                        <label class="uh-check">
                            <input type="checkbox" form="property-filters" name="amenities[]" value="{{ $amenity->id }}"
                                   @checked(in_array((string) $amenity->id, array_map('strval', (array) ($filters['amenities'] ?? [])), true))>
                            <span>{{ $amenity->label }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endif
    </div>

    <div class="absolute bottom-4 right-6 z-10">
        <a class="flex items-center justify-center w-10 h-10 rounded-full bg-slate-900 text-white shadow-lg hover:bg-slate-800 transition" href="{{ route('properties.index') }}" aria-label="{{ __('Clear all') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </a>
    </div>
</aside>
