@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\LocationArea> $areas */
    $areaName = $areaName ?? 'location_area_id';
    $selectedArea = old($areaName, $selectedArea ?? '');
    $cityName = $cityName ?? null;
    $selectedCity = $cityName ? old($cityName, $selectedCity ?? '') : ($selectedCity ?? '');
    $required = $required ?? true;
    $idPrefix = $idPrefix ?? 'place';
    $canAddArea = ($allowAdd ?? true) && auth()->user()?->can('reference.manage');
    $picker = [
        'areas' => $areas->map(fn ($area) => ['id' => $area->id, 'name' => $area->name, 'city' => $area->city, 'country' => $area->country ?: \App\Models\LocationArea::DEFAULT_COUNTRY])->values(),
        'areaId' => (string) $selectedArea,
        'city' => (string) $selectedCity,
        'defaultCountry' => \App\Models\LocationArea::DEFAULT_COUNTRY,
    ];
@endphp

<div class="dd-place" x-data="uhPlacePicker(@js($picker))">
    <div class="uh-field">
        <label class="uh-label" for="{{ $idPrefix }}-country">Country</label>
        <select id="{{ $idPrefix }}-country" class="uh-select" x-model="country">
            <template x-for="item in countries" :key="item">
                <option :value="item" x-text="item" :selected="item === country"></option>
            </template>
        </select>
    </div>

    <div class="uh-field">
        <label class="uh-label" for="{{ $idPrefix }}-city">City</label>
        <select id="{{ $idPrefix }}-city" class="uh-select" x-model="city" @if($cityName) name="{{ $cityName }}" required @endif>
            <option value="">{{ $cityName ? 'Choose a city' : 'All cities' }}</option>
            <template x-for="item in cities" :key="item">
                <option :value="item" x-text="item" :selected="item === city"></option>
            </template>
        </select>
        @if($cityName)
            @error($cityName)<p class="uh-error">{{ $message }}</p>@enderror
        @endif
    </div>

    <div class="uh-field">
        <label class="uh-label" for="{{ $idPrefix }}-area">
            Area
            @unless($required)<span class="uh-label-optional">optional</span>@endunless
        </label>
        <div @class(['uh-admin-select-row' => $canAddArea])>
            <select id="{{ $idPrefix }}-area" name="{{ $areaName }}" class="uh-select" x-model="areaId" data-place-area @if($required) required @endif
                    @error($areaName) aria-invalid="true" @enderror>
                <option value="">Choose an area</option>
                <template x-for="area in choices" :key="area.id">
                    <option :value="area.id" x-text="area.name" :selected="area.id === areaId"></option>
                </template>
            </select>
            @if($canAddArea)
                <button type="button" class="uh-admin-quick-add" aria-label="Add an area"
                        @click.prevent="$dispatch('uh-quick-add', { kind: 'area', target: '{{ $idPrefix }}-area' })">
                    <x-icon name="plus" class="size-4" />
                </button>
            @endif
        </div>
        <p class="uh-hint" x-show="country && choices.length === 0" x-cloak>No areas here yet. Use + to add one.</p>
        @error($areaName)<p class="uh-error">{{ $message }}</p>@enderror
    </div>
</div>
