@php
    $minBeds = (string) ($filters['min_beds'] ?? '');
    $minBaths = (string) ($filters['min_baths'] ?? '');
    $furnished = $filters['is_furnished'] ?? null;
@endphp

<div class="uh-scrim-modal z-[1200]" x-cloak
     x-bind:class="filtersOpen ? 'block' : 'hidden'"
     x-bind:aria-hidden="(!filtersOpen).toString()"
     @click="closeFilters()"></div>

<aside id="filters-drawer" class="uh-drawer is-end fixed inset-y-0 right-0 z-[1201] flex flex-col !w-[420px] max-w-[calc(100vw-1rem)] bg-[var(--color-page-background)] h-full shadow-2xl"
       role="dialog" aria-modal="true" aria-labelledby="more-filters-title"
       x-show="filtersOpen" x-cloak
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="translate-x-full"
       @change.debounce.250ms="search(document.getElementById('property-filters'))"
       x-bind:aria-hidden="(!filtersOpen).toString()"
       @click.stop @keydown.escape.stop="closeFilters()"
       @keydown.tab.prevent="filtersOpen && $trapFocus($el, $event)">
    
    <div class="uh-filter-drawer-header flex items-center justify-between px-6 py-5 bg-[var(--color-card-surface)] border-b border-[var(--color-divider)] flex-shrink-0">
        <div class="uh-filter-drawer-heading flex items-center gap-4">
            <span class="uh-filter-drawer-mark flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm text-teal-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            </span>
            <div class="uh-filter-drawer-copy">
                <p class="text-[0.65rem] font-bold uppercase tracking-wider text-teal-600 mb-0.5">{{ __('Refine your search') }}</p>
                <h2 id="more-filters-title" class="text-xl font-bold text-slate-900">{{ __('More Filters') }}</h2>
                <p class="text-xs text-slate-500 mt-1">{{ __('Find your perfect property with the right filters') }}</p>
            </div>
        </div>
        <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm text-slate-600 hover:text-slate-900 transition" @click="closeFilters()" aria-label="{{ __('Close filters') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
    </div>
    <div class="uh-drawer-body flex-1 min-h-0 overflow-y-auto bg-[var(--color-page-background)] p-4 sm:p-6 space-y-4">
        
        <fieldset class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <legend class="sr-only">{{ __('Area size') }}</legend>
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z"/><path d="M4 8h16"/><path d="M8 4v16"/><path d="M4 16h16"/><path d="M16 4v16"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">{{ __('Area size') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Select the total area of the property') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <label class="group relative flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white py-2.5 px-3 text-xs font-medium text-slate-700 hover:bg-slate-50 has-[:checked]:border-orange-200 has-[:checked]:bg-orange-50 has-[:checked]:text-orange-900 transition">
                    <input type="radio" class="peer sr-only" form="property-filters" name="area_band" value="" @checked(blank($filters['area_band'] ?? null))>
                    <span>{{ __('Any') }}</span>
                </label>
                @foreach(\App\Support\SearchBands::areas() as $key => $band)
                    <label class="group relative flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white py-2.5 px-3 text-xs font-medium text-slate-700 hover:bg-slate-50 has-[:checked]:border-orange-200 has-[:checked]:bg-orange-50 has-[:checked]:text-orange-900 transition">
                        <input type="radio" class="peer sr-only" form="property-filters" name="area_band" value="{{ $key }}" @checked(($filters['area_band'] ?? '') === $key)>
                        <span>{{ $band['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        @if($showBedroomFilters ?? true)
        <div class="grid grid-cols-2 gap-4">
            <fieldset class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col" x-data="{ count: {{ $minBeds !== '' ? (int) $minBeds : 0 }} }">
                <legend class="sr-only">{{ __('Bedroom') }}</legend>
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">{{ __('Bedroom') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Number of bedrooms') }}</p>
                    </div>
                </div>
                <div class="mt-auto flex items-center justify-between gap-2">
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full border border-teal-200 text-teal-600 hover:bg-teal-50 transition" @click="count = Math.max(0, count - 1); $nextTick(() => search(document.getElementById('property-filters')))" aria-label="{{ __('Fewer bedrooms') }}">
                        <x-icon name="minus" class="size-4" />
                    </button>
                    <p class="min-w-8 text-center font-bold text-slate-900">
                        <span x-text="count === 0 ? '{{ __('Any') }}' : (count >= 5 ? '5+' : count)">{{ $minBeds === '' ? __('Any') : ($minBeds === '5' ? '5+' : $minBeds) }}</span>
                    </p>
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full border border-teal-200 text-teal-600 hover:bg-teal-50 transition" @click="count = Math.min(5, count + 1); $nextTick(() => search(document.getElementById('property-filters')))" aria-label="{{ __('More bedrooms') }}">
                        <x-icon name="plus" class="size-4" />
                    </button>
                    <input type="hidden" form="property-filters" name="min_beds" :value="count || ''" :disabled="count === 0">
                </div>
            </fieldset>

            <fieldset class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col" x-data="{ count: {{ $minBaths !== '' ? (int) $minBaths : 0 }} }">
                <legend class="sr-only">{{ __('Bathroom') }}</legend>
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><path d="M10 5L8 7l4 4 6-6-4-4Z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">{{ __('Bathroom') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Number of bathrooms') }}</p>
                    </div>
                </div>
                <div class="mt-auto flex items-center justify-between gap-2">
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full border border-teal-200 text-teal-600 hover:bg-teal-50 transition" @click="count = Math.max(0, count - 1); $nextTick(() => search(document.getElementById('property-filters')))" aria-label="{{ __('Fewer bathrooms') }}">
                        <x-icon name="minus" class="size-4" />
                    </button>
                    <p class="min-w-8 text-center font-bold text-slate-900">
                        <span x-text="count === 0 ? '{{ __('Any') }}' : (count >= 5 ? '5+' : count)">{{ $minBaths === '' ? __('Any') : ($minBaths === '5' ? '5+' : $minBaths) }}</span>
                    </p>
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full border border-teal-200 text-teal-600 hover:bg-teal-50 transition" @click="count = Math.min(5, count + 1); $nextTick(() => search(document.getElementById('property-filters')))" aria-label="{{ __('More bathrooms') }}">
                        <x-icon name="plus" class="size-4" />
                    </button>
                    <input type="hidden" form="property-filters" name="min_baths" :value="count || ''" :disabled="count === 0">
                </div>
            </fieldset>
        </div>
        @endif

        <fieldset class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <legend class="sr-only">{{ __('Furnished') }}</legend>
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 9V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v2"/><path d="M4 16v-2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2"/><path d="M4 16v4"/><path d="M20 16v4"/><path d="M6 16h12"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">{{ __('Furnished') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Select furnishing status') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <label class="group relative flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white py-2.5 px-3 text-xs font-medium text-slate-700 hover:bg-slate-50 has-[:checked]:border-orange-200 has-[:checked]:bg-orange-50 has-[:checked]:text-orange-900 transition">
                    <input type="radio" class="peer sr-only" form="property-filters" name="is_furnished" value="" @checked($furnished === null)>
                    <span>{{ __('Any') }}</span>
                </label>
                <label class="group relative flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white py-2.5 px-3 text-xs font-medium text-slate-700 hover:bg-slate-50 has-[:checked]:border-orange-200 has-[:checked]:bg-orange-50 has-[:checked]:text-orange-900 transition">
                    <input type="radio" class="peer sr-only" form="property-filters" name="is_furnished" value="1" @checked($furnished === true || $furnished === '1')>
                    <span>{{ __('Yes') }}</span>
                </label>
                <label class="group relative flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white py-2.5 px-3 text-xs font-medium text-slate-700 hover:bg-slate-50 has-[:checked]:border-orange-200 has-[:checked]:bg-orange-50 has-[:checked]:text-orange-900 transition">
                    <input type="radio" class="peer sr-only" form="property-filters" name="is_furnished" value="0" @checked($furnished === false || $furnished === '0')>
                    <span>{{ __('No') }}</span>
                </label>
            </div>
        </fieldset>

        <fieldset class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <legend class="sr-only">{{ __('Property Facing') }}</legend>
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m16.24 7.76-1.804 5.411a2 2 0 0 1-1.265 1.265L7.76 16.24l1.804-5.411a2 2 0 0 1 1.265-1.265z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">{{ __('Property Facing') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Select the main facing direction') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                @foreach(config('urbanhaven.facings') as $value => $label)
                    <label class="group relative flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white py-2 px-2 text-xs font-medium text-slate-700 hover:bg-slate-50 has-[:checked]:border-teal-200 has-[:checked]:bg-teal-50 has-[:checked]:text-teal-900 transition gap-2">
                        <input type="radio" class="peer sr-only" form="property-filters" name="facing" value="{{ $value }}" @checked(($filters['facing'] ?? '') === $value)>
                        @php
                            $icon = match($value) {
                                'north' => 'arrow-up',
                                'south' => 'arrow-down',
                                'east' => 'arrow-right',
                                'west' => 'arrow-left',
                                'north_east' => 'arrow-up-right',
                                'north_west' => 'arrow-up-left',
                                'south_east' => 'arrow-down-right',
                                'south_west' => 'arrow-down-left',
                                default => null,
                            };
                        @endphp
                        @if($icon)
                            <x-icon :name="$icon" class="size-3.5 text-slate-400 group-hover:text-slate-600 peer-checked:text-teal-600" />
                        @endif
                        <span>{{ __($label) }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <legend class="sr-only">{{ __('Road Access (Feet)') }}</legend>
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16"/><path d="M4 2h16"/><path d="M12 6v12"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">{{ __('Road Access (Feet)') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Select minimum and maximum road width') }}</p>
                </div>
            </div>
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
            <fieldset class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <legend class="sr-only">{{ __('Amenities') }}</legend>
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">{{ __('Amenities') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Select the amenities you\'re looking for') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-y-3 gap-x-2">
                    @foreach($amenities as $amenity)
                        <label class="flex cursor-pointer items-center gap-2.5 group">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" form="property-filters" name="amenities[]" value="{{ $amenity->id }}" class="peer h-5 w-5 cursor-pointer appearance-none rounded border border-slate-300 bg-slate-50 transition-all checked:border-teal-500 checked:bg-teal-500 hover:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20" @checked(in_array((string) $amenity->id, array_map('strval', (array) ($filters['amenities'] ?? [])), true))>
                                <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                            <span class="text-xs font-medium text-slate-700 group-hover:text-slate-900">{{ $amenity->label }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endif
    </div>

    <!-- Drawer Footer -->
    <div class="p-4 bg-[var(--color-card-surface)] border-t border-[var(--color-divider)] flex-shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.03)]">
        <a class="flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-[var(--color-divider)] bg-[var(--color-card-surface)] text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition" href="{{ route('properties.index') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            {{ __('Reset all filters') }}
        </a>
    </div>
</aside>
