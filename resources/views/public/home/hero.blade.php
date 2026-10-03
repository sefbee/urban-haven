<section @class(['uh-hero-stage', 'has-photo' => (bool) $heroImage])>
    <div @class(['uh-hero-photo', 'uh-hero-photo-empty' => ! $heroImage])>
        @if($heroImage)
            <img src="{{ $heroImage->url(1920) }}"
                 srcset="{{ $heroImage->url(768) }} 768w, {{ $heroImage->url(1280) }} 1280w, {{ $heroImage->url(1920) }} 1920w"
                 sizes="100vw"
                 alt="{{ $heroImageAlt }}" fetchpriority="high" decoding="async">
        @endif
    </div>
    <div class="uh-hero-veil" aria-hidden="true"></div>

    <div class="uh-container uh-hero-copy">
        <h1 class="uh-hero-title">
            {{ $hero['title'] ?? __('Find a property in Dhaka') }}
        </h1>
        <p class="uh-hero-lede">
            {{ $hero['body'] ?? __('Search apartments, homes, land and commercial space listed directly by Urban Haven.') }}
        </p>
    </div>
</section>

<form method="GET" action="{{ route('properties.index') }}"
      class="uh-search-card"
      x-data="uhHeroSearch(@js($purposes[0] ?? 'sale'))"
      @submit="submit(); $event.target.querySelectorAll('input, select').forEach((field) => { if (field.value === '' && field.type !== 'hidden') field.disabled = true })">
    <h2 class="sr-only">{{ __('Search properties') }}</h2>

    @if(count($purposes) > 1)
        <div class="uh-search-tabs" role="group" aria-label="{{ __('Buy or rent') }}">
            <button type="button" class="uh-search-tab"
                    :class="purpose === 'sale' ? 'is-on' : ''"
                    :aria-pressed="(purpose === 'sale').toString()"
                    @click="setPurpose('sale')">{{ __('Buy') }}</button>
            <button type="button" class="uh-search-tab"
                    :class="purpose === 'rent' ? 'is-on' : ''"
                    :aria-pressed="(purpose === 'rent').toString()"
                    @click="setPurpose('rent')">{{ __('Rent') }}</button>
        </div>
    @endif
    <input type="hidden" name="listing_type" :value="purpose" value="{{ $purposes[0] ?? 'sale' }}">

    <div class="uh-search-grid">
        <label class="uh-search-field">
            <span>{{ __('Location') }}</span>
            <span class="uh-home-select">
                <select name="location_area_id">
                    <option value="">{{ __('Any location') }}</option>
                    @foreach($heroAreas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}@if($area->city), {{ $area->city }}@endif</option>
                    @endforeach
                </select>
            </span>
        </label>

        <label class="uh-search-field">
            <span>{{ __('Property type') }}</span>
            <span class="uh-home-select">
                <select name="property_type_id">
                    <option value="">{{ __('Any type') }}</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}">{{ $type->label }}</option>
                    @endforeach
                </select>
            </span>
        </label>

        <label class="uh-search-field">
            <span>{{ __('Bedrooms') }}</span>
            <span class="uh-home-select">
                <select name="min_beds">
                    <option value="">{{ __('Any') }}</option>
                    @foreach([1, 2, 3, 4] as $beds)
                        <option value="{{ $beds }}">{{ __(':count+ bedrooms', ['count' => $beds]) }}</option>
                    @endforeach
                </select>
            </span>
        </label>

        <div class="uh-search-field">
            <span>{{ __('Price range') }}</span>
            <div class="flex items-center gap-2">
                <label class="min-w-0 flex-1">
                    <span class="sr-only">{{ __('Minimum') }}</span>
                    <input type="number" min="0" :max="ceiling" :step="step"
                           class="uh-home-input uh-numeric w-full" x-model="min"
                           @change="clampMin" inputmode="numeric" placeholder="{{ __('Min') }}">
                </label>
                <span class="text-[#1d1d1f]/30" aria-hidden="true">–</span>
                <label class="min-w-0 flex-1">
                    <span class="sr-only">{{ __('Maximum') }}</span>
                    <input type="number" min="0" :max="ceiling" :step="step"
                           class="uh-home-input uh-numeric w-full" x-model="max"
                           @change="clampMax" inputmode="numeric" placeholder="{{ __('Max') }}">
                </label>
            </div>
            <input type="hidden" name="min_price" :value="min" :disabled="!hasMin">
            <input type="hidden" name="max_price" :value="max" :disabled="!hasMax">
        </div>

        <div class="uh-search-go">
            <button type="submit" class="uh-search-submit w-full" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                {{ __('Search Properties') }}
            </button>
        </div>
    </div>
</form>
