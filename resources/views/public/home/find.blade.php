@php
    $budgetLabel = fn (int $amount): string => 'BDT '.(\App\Support\MoneyFormatter::compactBdt($amount) ?? number_format($amount));
    $budgets = [
        'sale' => collect([2_500_000, 5_000_000, 7_500_000, 10_000_000, 15_000_000, 20_000_000, 30_000_000, 50_000_000, 100_000_000, 200_000_000])
            ->map(fn (int $amount): array => ['value' => $amount, 'label' => $budgetLabel($amount)])->all(),
        'rent' => collect([10_000, 15_000, 20_000, 30_000, 50_000, 75_000, 100_000, 150_000, 200_000, 300_000])
            ->map(fn (int $amount): array => ['value' => $amount, 'label' => $budgetLabel($amount)])->all(),
    ];
    $searchLabels = [
        'idle' => __('Search Properties'),
        'one' => __('Show 1 property'),
        'many' => __('Show :count properties'),
        'none' => __('No matches yet'),
    ];
@endphp

<section id="find" class="uh-find scroll-mt-20" aria-labelledby="find-title"
         x-data="uhHeroSearch({ initial: @js($purposes[0] ?? 'sale'), countUrl: @js(route('properties.count')), labels: @js($searchLabels), budgets: @js($budgets) })">
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
                <div class="uh-search-cell is-location">
                    <label for="find-location" class="uh-search-label">{{ __('Location') }}</label>
                    <div class="uh-search-control is-select2">
                        <x-icon name="pin" class="size-4 shrink-0 text-[var(--uh-faint)]" />
                        <select id="find-location" x-ref="location" @change="pickLocation($event.target)"
                                data-uh-select data-uh-select-ajax="{{ route('search.locations') }}" data-uh-select-tags data-uh-select-clear="true"
                                data-placeholder="{{ __('Area, street or project') }}"
                                data-uh-select-keyword="{{ __('Search for “:term”') }}"
                                data-uh-select-empty="{{ __('No matching areas') }}"
                                data-uh-select-searching="{{ __('Searching…') }}">
                            <option value=""></option>
                        </select>
                    </div>
                    <input type="hidden" name="location_area_id" :value="area" :disabled="! area">
                    <input type="hidden" name="q" :value="query" :disabled="area !== '' || query.trim() === ''">
                </div>

                <div class="uh-search-cell">
                    <label for="find-type" class="uh-search-label">{{ __('Property type') }}</label>
                    <div class="uh-search-control is-select2">
                        <select id="find-type" name="property_type_id" x-model="type" data-uh-select data-uh-select-clear="true">
                            <option value="">{{ __('Any type') }}</option>
                            @foreach($types as $propertyType)
                                <option value="{{ $propertyType->id }}">{{ $propertyType->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="uh-search-cell">
                    <label for="find-beds" class="uh-search-label">{{ __('Bedrooms') }}</label>
                    <div class="uh-search-control is-select2">
                        <select id="find-beds" name="min_beds" x-model="beds" data-uh-select data-uh-select-search="off">
                            <option value="">{{ __('Any') }}</option>
                            @foreach([1, 2, 3, 4, 5] as $bedCount)
                                <option value="{{ $bedCount }}">{{ __(':count+ bedrooms', ['count' => $bedCount]) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="uh-search-cell is-budget" role="group" aria-labelledby="find-budget">
                    <span id="find-budget" class="uh-search-label">{{ __('Price range') }}</span>
                    <div class="uh-search-range">
                        <div class="uh-search-control is-select2">
                            <label for="find-min" class="sr-only">{{ __('Minimum price') }}</label>
                            <select id="find-min" name="min_price" x-model="min" data-uh-select data-uh-select-search="on" data-uh-select-auto-width="true">
                                <option value="">{{ __('No min') }}</option>
                                <template x-for="option in minBudgets" :key="purpose + option.value">
                                    <option :value="option.value" x-text="option.label" :selected="String(option.value) === min"></option>
                                </template>
                            </select>
                        </div>
                        <span class="uh-search-dash" aria-hidden="true">–</span>
                        <div class="uh-search-control is-select2">
                            <label for="find-max" class="sr-only">{{ __('Maximum price') }}</label>
                            <select id="find-max" name="max_price" x-model="max" data-uh-select data-uh-select-search="on" data-uh-select-auto-width="true">
                                <option value="">{{ __('No max') }}</option>
                                <template x-for="option in maxBudgets" :key="purpose + option.value">
                                    <option :value="option.value" x-text="option.label" :selected="String(option.value) === max"></option>
                                </template>
                            </select>
                        </div>
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
