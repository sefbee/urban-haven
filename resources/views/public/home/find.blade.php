@php
    $budgetLabel = fn (int $amount): string => 'BDT '.(\App\Support\MoneyFormatter::compactBdt($amount) ?? number_format($amount));
    $budgets = [
        'sale' => collect([2_500_000, 5_000_000, 7_500_000, 10_000_000, 15_000_000, 20_000_000, 30_000_000, 50_000_000, 100_000_000, 200_000_000])
            ->map(fn (int $amount): array => ['value' => $amount, 'label' => $budgetLabel($amount)])->all(),
        'rent' => collect([10_000, 15_000, 20_000, 30_000, 50_000, 75_000, 100_000, 150_000, 200_000, 300_000])
            ->map(fn (int $amount): array => ['value' => $amount, 'label' => $budgetLabel($amount)])->all(),
    ];
    $searchAreas = $heroAreas->map(fn ($area): array => [
        'id' => $area->id,
        'label' => $area->name.($area->city ? ', '.$area->city : ''),
    ])->values();
    $searchLabels = [
        'idle' => __('Search Properties'),
        'one' => __('Show 1 property'),
        'many' => __('Show :count properties'),
        'none' => __('No matches yet'),
    ];
@endphp

<section id="find" class="uh-find scroll-mt-20" aria-labelledby="find-title"
         x-data="uhHeroSearch({ initial: @js($purposes[0] ?? 'sale'), countUrl: @js(route('properties.count')), labels: @js($searchLabels), areas: @js($searchAreas), budgets: @js($budgets) })">
    <div class="uh-container">
        <form method="GET" action="{{ route('properties.index') }}" class="uh-search" data-reveal @submit="submit($event)">
            <h2 id="find-title" class="sr-only">{{ __('Search properties') }}</h2>
            <input type="hidden" name="listing_type" :value="purpose" value="{{ $purposes[0] ?? 'sale' }}">

            <div class="uh-search-top">
                @if(count($purposes) > 1)
                    <div class="uh-search-tabs" role="group" aria-label="{{ __('Buy or rent') }}">
                        <button type="button" class="uh-search-tab" :aria-pressed="(purpose === 'sale').toString()"
                                @click="setPurpose('sale')">{{ __('Buy') }}</button>
                        <button type="button" class="uh-search-tab" :aria-pressed="(purpose === 'rent').toString()"
                                @click="setPurpose('rent')">{{ __('Rent') }}</button>
                    </div>
                @endif
                <button type="button" class="uh-search-clear" x-cloak x-show="refined" x-transition.opacity @click="reset()">
                    <x-icon name="close" class="size-3" />
                    {{ __('Clear') }}
                </button>
            </div>

            <div class="uh-search-fields">
                <div class="uh-search-cell is-location" @click.outside="suggesting = false">
                    <label for="find-location" class="uh-search-label">{{ __('Location') }}</label>
                    <div class="uh-search-control">
                        <x-icon name="pin" class="size-4 shrink-0 text-[var(--uh-faint)]" />
                        <input id="find-location" type="search" x-model="query" maxlength="120" autocomplete="off"
                               :name="area ? '' : 'q'" name="q"
                               placeholder="{{ __('Area, street or project') }}"
                               role="combobox" aria-autocomplete="list" aria-controls="find-location-list"
                               :aria-expanded="(suggesting && suggestions.length > 0).toString()"
                               :aria-activedescendant="highlighted >= 0 ? 'find-location-' + highlighted : null"
                               @input="typed()" @focus="suggesting = true"
                               @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)"
                               @keydown.enter="pickHighlighted($event)" @keydown.escape="suggesting = false">
                    </div>
                    <input type="hidden" name="location_area_id" :value="area" :disabled="! area">
                    <ul id="find-location-list" class="uh-search-suggest" role="listbox" x-cloak
                        x-show="suggesting && suggestions.length > 0" x-transition.opacity.duration.150ms>
                        <template x-for="(option, index) in suggestions" :key="option.id">
                            <li :id="'find-location-' + index" role="option" :aria-selected="(index === highlighted).toString()"
                                :class="index === highlighted && 'is-highlighted'"
                                @mousedown.prevent="pick(option)" @mouseenter="highlighted = index">
                                <x-icon name="pin" class="size-3.5 shrink-0 text-[var(--uh-faint)]" />
                                <span x-text="option.label"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <label class="uh-search-cell">
                    <span class="uh-search-label">{{ __('Property type') }}</span>
                    <span class="uh-search-control">
                        <select name="property_type_id" x-model="type">
                            <option value="">{{ __('Any type') }}</option>
                            @foreach($types as $propertyType)
                                <option value="{{ $propertyType->id }}">{{ $propertyType->label }}</option>
                            @endforeach
                        </select>
                        <x-icon name="chevron-down" class="uh-search-chevron" />
                    </span>
                </label>

                <label class="uh-search-cell">
                    <span class="uh-search-label">{{ __('Bedrooms') }}</span>
                    <span class="uh-search-control">
                        <select name="min_beds" x-model="beds">
                            <option value="">{{ __('Any') }}</option>
                            @foreach([1, 2, 3, 4, 5] as $bedCount)
                                <option value="{{ $bedCount }}">{{ __(':count+ bedrooms', ['count' => $bedCount]) }}</option>
                            @endforeach
                        </select>
                        <x-icon name="chevron-down" class="uh-search-chevron" />
                    </span>
                </label>

                <div class="uh-search-cell is-budget" role="group" aria-labelledby="find-budget">
                    <span id="find-budget" class="uh-search-label">{{ __('Price range') }}</span>
                    <div class="uh-search-range">
                        <label class="uh-search-control">
                            <span class="sr-only">{{ __('Minimum price') }}</span>
                            <select name="min_price" x-model="min">
                                <option value="">{{ __('No min') }}</option>
                                <template x-for="option in minBudgets" :key="purpose + option.value">
                                    <option :value="option.value" x-text="option.label" :selected="String(option.value) === min"></option>
                                </template>
                            </select>
                            <x-icon name="chevron-down" class="uh-search-chevron" />
                        </label>
                        <span class="uh-search-dash" aria-hidden="true">–</span>
                        <label class="uh-search-control">
                            <span class="sr-only">{{ __('Maximum price') }}</span>
                            <select name="max_price" x-model="max">
                                <option value="">{{ __('No max') }}</option>
                                <template x-for="option in maxBudgets" :key="purpose + option.value">
                                    <option :value="option.value" x-text="option.label" :selected="String(option.value) === max"></option>
                                </template>
                            </select>
                            <x-icon name="chevron-down" class="uh-search-chevron" />
                        </label>
                    </div>
                </div>

                <div class="uh-search-action">
                    <button type="submit" class="uh-search-submit" :disabled="submitting" :aria-busy="submitting.toString()">
                        <span class="uh-spinner" x-show="submitting || counting" x-cloak></span>
                        <x-icon name="search" class="size-4" x-show="! submitting && ! counting" />
                        <span x-text="buttonLabel">{{ $searchLabels['idle'] }}</span>
                    </button>
                </div>
            </div>

            <p class="sr-only" aria-live="polite" x-text="matches === null ? '' : buttonLabel"></p>
        </form>
    </div>
</section>
