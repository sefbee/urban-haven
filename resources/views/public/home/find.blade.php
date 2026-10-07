@php
    $priceStops = [
        'sale' => [0, 1_000_000, 2_000_000, 3_000_000, 4_000_000, 5_000_000, 6_000_000, 7_500_000, 10_000_000, 12_500_000, 15_000_000, 20_000_000, 25_000_000, 30_000_000, 40_000_000, 50_000_000, 75_000_000, 100_000_000, 150_000_000, 200_000_000],
        'rent' => [0, 10_000, 15_000, 20_000, 25_000, 30_000, 40_000, 50_000, 60_000, 75_000, 100_000, 125_000, 150_000, 200_000, 250_000, 300_000, 500_000],
    ];
    $typeOptions = $types->map(fn ($propertyType): array => [
        'id' => (string) $propertyType->id,
        'label' => $propertyType->label,
        'category' => $propertyType->category,
        'counts' => collect($purposes)->mapWithKeys(fn (string $purpose): array => [$purpose => (int) ($propertyType->{$purpose.'_listings_count'} ?? 0)]),
    ])->values();
    $searchLabels = [
        'anyType' => __('Property type'),
        'typesMore' => __(':first +:count'),
        'categories' => \App\Models\PropertyType::categoryLabels(),
        'anyPrice' => __('Price range'),
        'from' => __('From :price'),
        'upTo' => __('Up to :price'),
        'crore' => __('crore'),
        'lakh' => __('lakh'),
        'homes' => __(':count homes'),
        'oneHome' => __('1 home'),
    ];
@endphp

<section id="find" class="uh-find scroll-mt-20" aria-labelledby="find-title"
         x-data="uhHeroSearch({ initial: @js($purposes[0] ?? 'sale'), suggestUrl: @js(route('search.locations')), labels: @js($searchLabels), stops: @js($priceStops), types: @js($typeOptions) })"
         @keydown.escape="close(true)" @click.outside="close()">
    <div class="uh-container">
        <form method="GET" action="{{ route('properties.index') }}" class="uh-search" data-reveal @submit="submit($event)">
            <h2 id="find-title" class="sr-only">{{ __('Search properties') }}</h2>
            <input type="hidden" name="listing_type" :value="purpose" value="{{ $purposes[0] ?? 'sale' }}">

            @if(count($purposes) > 1)
                <div class="uh-search-tabs" role="group" aria-label="{{ __('Buy or rent') }}">
                    <button type="button" class="uh-search-tab" :aria-pressed="(purpose === 'sale').toString()" aria-pressed="{{ ($purposes[0] ?? 'sale') === 'sale' ? 'true' : 'false' }}"
                            @click="setPurpose('sale')">{{ __('Buy') }}</button>
                    <button type="button" class="uh-search-tab" :aria-pressed="(purpose === 'rent').toString()" aria-pressed="{{ ($purposes[0] ?? 'sale') === 'rent' ? 'true' : 'false' }}"
                            @click="setPurpose('rent')">{{ __('Rent') }}</button>
                </div>
            @endif

            <div class="uh-search-bar">
                {{-- Property type --}}
                <div class="uh-search-cell is-type" :class="{ 'is-open': open === 'type' }">
                    <span id="find-type-label" class="sr-only">{{ __('Property type') }}</span>
                    <button type="button" class="uh-search-control uh-search-toggle" :aria-labelledby="typeText() ? 'find-type-label find-type-value' : 'find-type-label'"
                            aria-labelledby="find-type-label" aria-controls="find-type-panel" :aria-expanded="(open === 'type').toString()" aria-expanded="false" @click="toggle('type')">
                        <x-icon name="building" class="uh-search-icon" />
                        <span id="find-type-value" class="uh-search-value" :class="{ 'is-set': typeText() }" x-text="typeSummary">{{ __('Property type') }}</span>
                        <x-icon name="chevron-down" class="uh-search-chevron" />
                    </button>
                    <input type="hidden" name="category" :value="category" :disabled="category === ''">
                    <template x-for="id in pickedTypes" :key="'picked' + id">
                        <input type="hidden" name="property_type_ids[]" :value="id">
                    </template>

                    <div id="find-type-panel" class="uh-search-panel is-type" role="group" aria-labelledby="find-type-label"
                         x-show="open === 'type'" x-cloak x-transition.opacity.duration.150ms>
                        @include('public.partials.type-picker')
                    </div>
                </div>

                {{-- Location: free text with area, category and property suggestions --}}
                <div class="uh-search-cell is-location" :class="{ 'is-open': open === 'location' }">
                    <label for="find-location" class="sr-only">{{ __('Location') }}</label>
                    <div class="uh-search-control">
                        <x-icon name="pin" class="uh-search-icon" />
                        <input id="find-location" type="search" autocomplete="off" enterkeyhint="search"
                               role="combobox" aria-autocomplete="list" aria-controls="find-location-panel"
                               :aria-expanded="(open === 'location').toString()" aria-expanded="false"
                               :aria-activedescendant="active >= 0 ? 'find-option-' + active : null"
                               placeholder="{{ __('Search area or property') }}"
                               x-model="query" @focus="show('location')" @click="show('location')" @input="typed()"
                               @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter="pickActive($event)">
                        <x-icon name="chevron-down" class="uh-search-chevron" @mousedown.prevent @click="toggleLocation()" />
                    </div>
                    <input type="hidden" name="location_area_id" :value="area" :disabled="! area">
                    <input type="hidden" name="property_type_ids[]" :value="locationType" :disabled="locationType === '' || pickedTypes.includes(locationType)">
                    <input type="hidden" name="q" :value="query" :disabled="area !== '' || locationType !== '' || query.trim() === ''">

                    <div id="find-location-panel" class="uh-search-panel is-suggest" role="listbox" aria-label="{{ __('Suggestions') }}"
                         x-show="open === 'location'" x-cloak x-transition.opacity.duration.150ms>
                        <div class="uh-search-group">
                            <p class="uh-search-group-title">{{ __('Popular categories') }}</p>
                            <p class="uh-search-empty" x-show="! typeSuggestions.length">{{ __('No category found') }}</p>
                            <ul class="uh-search-list" x-show="typeSuggestions.length">
                                <template x-for="(option, index) in typeSuggestions" :key="'type' + option.id">
                                    <li>
                                        <button type="button" class="uh-search-option" role="option" :id="'find-option-' + index"
                                                :aria-selected="(active === index).toString()" :class="{ 'is-active': active === index }"
                                                @mouseenter="active = index" @click="pickCategory(option)">
                                            <x-icon name="building" class="size-4 shrink-0" />
                                            <span class="uh-search-option-name" x-text="option.label"></span>
                                            <span class="uh-search-option-meta" x-text="typeMeta(option)"></span>
                                            <x-icon name="check" class="uh-search-option-check" x-show="locationType === option.id" />
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div class="uh-search-group">
                            <p class="uh-search-group-title">{{ __('Popular areas') }}</p>
                            <p class="uh-search-empty" x-show="! areas.length" x-text="loading ? @js(__('Searching…')) : @js(__('No areas found'))"></p>
                            <ul class="uh-search-list" x-show="areas.length">
                                <template x-for="(item, index) in areas" :key="'area' + item.id">
                                    <li>
                                        <button type="button" class="uh-search-option" role="option" :id="'find-option-' + (typeSuggestions.length + index)"
                                                :aria-selected="(active === typeSuggestions.length + index).toString()" :class="{ 'is-active': active === typeSuggestions.length + index }"
                                                @mouseenter="active = typeSuggestions.length + index" @click="pickArea(item)">
                                            <x-icon name="pin" class="size-4 shrink-0" />
                                            <span class="uh-search-option-name" x-text="item.text"></span>
                                            <span class="uh-search-option-meta" x-text="[item.city, item.meta].filter(Boolean).join(' · ')"></span>
                                            <x-icon name="check" class="uh-search-option-check" x-show="area === String(item.id)" />
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div class="uh-search-group">
                            <p class="uh-search-group-title">{{ __('Properties') }}</p>
                            <p class="uh-search-empty" x-show="! properties.length" x-text="loading ? @js(__('Searching…')) : @js(__('No property found'))"></p>
                            <ul class="uh-search-list" x-show="properties.length">
                                <template x-for="(item, index) in properties" :key="'home' + item.url">
                                    <li>
                                        <button type="button" class="uh-search-option" role="option" :id="'find-option-' + (propertyOffset + index)"
                                                :aria-selected="(active === propertyOffset + index).toString()" :class="{ 'is-active': active === propertyOffset + index }"
                                                @mouseenter="active = propertyOffset + index" @click="pickHome(item)">
                                            <x-icon name="home" class="size-4 shrink-0" />
                                            <span class="uh-search-option-name" x-text="item.title"></span>
                                            <span class="uh-search-option-meta" x-text="item.place"></span>
                                            <x-icon name="check" class="uh-search-option-check" x-show="home === item.url" />
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Price range: slider plus exact minimum and maximum --}}
                <div class="uh-search-cell is-budget" :class="{ 'is-open': open === 'price' }">
                    <span id="find-price-label" class="sr-only">{{ __('Price range') }}</span>
                    <button type="button" class="uh-search-control uh-search-toggle" :aria-labelledby="hasMin || hasMax ? 'find-price-label find-price-value' : 'find-price-label'"
                            aria-labelledby="find-price-label" aria-controls="find-price-panel" :aria-expanded="(open === 'price').toString()" aria-expanded="false" @click="toggle('price')">
                        <x-icon name="tag" class="uh-search-icon" />
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

                <div class="uh-search-action">
                    <button type="submit" class="uh-search-submit" aria-label="{{ __('Search') }}" :disabled="submitting" :aria-busy="submitting.toString()">
                        <span class="uh-spinner" x-show="submitting" x-cloak></span>
                        <x-icon name="search" class="size-4" x-show="! submitting" />
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>
