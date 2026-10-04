<div class="uh-scrim-modal z-[70]" x-cloak
     x-bind:class="filtersOpen ? 'block' : 'hidden'"
     x-bind:aria-hidden="(!filtersOpen).toString()"
     @click="closeFilters()"></div>

<aside id="more-filters"
       class="uh-drawer"
       x-cloak
       x-bind:class="filtersOpen ? '!flex' : '!hidden'"
       role="dialog" aria-modal="true" aria-labelledby="more-filters-title"
       x-bind:aria-hidden="(!filtersOpen).toString()"
       @click.stop @keydown.escape.stop="closeFilters()">
    <div class="uh-drawer-head">
        <h2 id="more-filters-title" class="uh-h4">{{ __('More Filters') }}</h2>
        <button type="button" class="uh-icon-btn" @click="closeFilters()" aria-label="{{ __('Close filters') }}">
            <x-icon name="close" class="size-5" />
        </button>
    </div>

    <div class="uh-drawer-body">
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
                <button type="button" class="uh-icon-action" @click="count = Math.max(0, count - 1)" aria-label="{{ __('Fewer bedrooms') }}">
                    <x-icon name="minus" class="size-4" />
                </button>
                <p class="min-w-10 text-center font-medium">
                    <span class="uh-numeric" x-text="count === 0 ? '{{ __('Any') }}' : (count >= 5 ? '5+' : count)">{{ $minBeds === '' ? __('Any') : ($minBeds === '5' ? '5+' : $minBeds) }}</span>
                </p>
                <button type="button" class="uh-icon-action" @click="count = Math.min(5, count + 1)" aria-label="{{ __('More bedrooms') }}">
                    <x-icon name="plus" class="size-4" />
                </button>
                <input type="hidden" form="property-filters" name="min_beds" :value="count || ''" :disabled="count === 0">
            </div>
        </fieldset>

        <fieldset class="uh-drawer-group" x-data="{ count: {{ $minBaths !== '' ? (int) $minBaths : 0 }} }">
            <legend class="uh-legend">{{ __('Bathroom') }}</legend>
            <div class="mt-2 flex items-center gap-3">
                <button type="button" class="uh-icon-action" @click="count = Math.max(0, count - 1)" aria-label="{{ __('Fewer bathrooms') }}">
                    <x-icon name="minus" class="size-4" />
                </button>
                <p class="min-w-10 text-center font-medium">
                    <span class="uh-numeric" x-text="count === 0 ? '{{ __('Any') }}' : (count >= 5 ? '5+' : count)">{{ $minBaths === '' ? __('Any') : ($minBaths === '5' ? '5+' : $minBaths) }}</span>
                </p>
                <button type="button" class="uh-icon-action" @click="count = Math.min(5, count + 1)" aria-label="{{ __('More bathrooms') }}">
                    <x-icon name="plus" class="size-4" />
                </button>
                <input type="hidden" form="property-filters" name="min_baths" :value="count || ''" :disabled="count === 0">
            </div>
        </fieldset>

        @endif

        @php
            $verification = array_map('strval', (array) ($filters['verification'] ?? []));
            if ($verification === [] && ! empty($filters['is_verified'])) {
                $verification = ['verified'];
            }
        @endphp
        <fieldset class="uh-drawer-group">
            <legend class="uh-legend">{{ __('Property Verification') }}</legend>
            <label class="uh-check">
                <input type="checkbox" form="property-filters" name="verification[]" value="verified" @checked(in_array('verified', $verification, true))>
                <span>{{ __('Verified Listings') }}</span>
            </label>
            <label class="uh-check">
                <input type="checkbox" form="property-filters" name="verification[]" value="unverified" @checked(in_array('unverified', $verification, true))>
                <span>{{ __('Unverified') }}</span>
            </label>
        </fieldset>

        <fieldset class="uh-drawer-group">
            <legend class="uh-legend">{{ __('Furnishing Status') }}</legend>
            @php
                $furnishing = array_map('strval', (array) ($filters['furnishing'] ?? []));
                if ($furnishing === [] && ($furnished === true || $furnished === 1 || $furnished === '1')) {
                    $furnishing = ['full'];
                }
                if ($furnishing === [] && ($furnished === false || $furnished === 0 || $furnished === '0')) {
                    $furnishing = ['unfurnished'];
                }
            @endphp
            <div class="grid gap-1">
                <label class="uh-check">
                    <input type="checkbox" form="property-filters" name="furnishing[]" value="full" @checked(in_array('full', $furnishing, true))>
                    <span>{{ __('Full Furnished') }}</span>
                </label>
                <label class="uh-check">
                    <input type="checkbox" form="property-filters" name="furnishing[]" value="semi" @checked(in_array('semi', $furnishing, true))>
                    <span>{{ __('Semi Furnished') }}</span>
                </label>
                <label class="uh-check">
                    <input type="checkbox" form="property-filters" name="furnishing[]" value="unfurnished" @checked(in_array('unfurnished', $furnishing, true))>
                    <span>{{ __('Unfurnished') }}</span>
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
                <x-ui.select name="min_road_width" form="property-filters" :label="__('Min')" sr-label>
                    <option value="">{{ __('Min') }}</option>
                    @foreach([10, 12, 15, 20, 30, 40] as $feet)
                        <option value="{{ $feet }}" @selected((string) ($filters['min_road_width'] ?? '') === (string) $feet)>{{ $feet }} ft</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="max_road_width" form="property-filters" :label="__('Max')" sr-label>
                    <option value="">{{ __('Max') }}</option>
                    @foreach([20, 30, 40, 60, 80, 100] as $feet)
                        <option value="{{ $feet }}" @selected((string) ($filters['max_road_width'] ?? '') === (string) $feet)>{{ $feet }} ft</option>
                    @endforeach
                </x-ui.select>
            </div>
        </fieldset>

        <fieldset class="uh-drawer-group">
            <legend class="uh-legend">{{ __('Property Age') }}</legend>
            @php
                $ageChips = [
                    '1' => '<1 Year',
                    '3' => '<3 Years',
                    '5' => '<5 Years',
                    '10' => '<10 Years',
                    '10plus' => '10+ Years',
                ];
            @endphp
            <div class="flex flex-wrap gap-1.5">
                @foreach($ageChips as $years => $label)
                    <label class="uh-choice">
                        <input type="radio" form="property-filters" name="max_age" value="{{ $years }}" @checked((string) ($filters['max_age'] ?? '') === $years)>
                        <span>{{ __($label) }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="uh-drawer-group">
            <legend class="uh-legend">{{ __('Road Type') }}</legend>
            <div class="grid gap-1">
                @foreach(['Blacktopped', 'Concrete', 'Gravelled', 'Alley'] as $road)
                    <label class="uh-choice">
                        <input type="radio" form="property-filters" name="road_type" value="{{ $road }}" @checked((string) ($filters['road_type'] ?? '') === $road)>
                        <span>{{ __($road) }}</span>
                    </label>
                @endforeach
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

    <div class="uh-drawer-foot">
        <a class="uh-btn-text" href="{{ route('properties.index') }}">{{ __('Clear all') }}</a>
        <button type="submit" form="property-filters" class="uh-btn-primary flex-1">{{ __('Apply filters') }}</button>
    </div>
</aside>
