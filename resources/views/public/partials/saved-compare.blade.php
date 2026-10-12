@php
    $groups = [
        [
            'title' => __('Price & value'),
            'icon' => 'tag',
            'keys' => ['price', 'price_per_sqft'],
        ],
        [
            'title' => __('Property details'),
            'icon' => 'home',
            'keys' => ['bedrooms', 'bathrooms', 'area', 'type', 'is_furnished', 'floor_number', 'balconies', 'parking_spaces', 'facing'],
        ],
        [
            'title' => __('Location & lifestyle'),
            'icon' => 'pin',
            'keys' => ['city', 'area_name', 'address', 'road_width_ft'],
        ],
        [
            'title' => __('Listing details'),
            'icon' => 'document',
            'keys' => ['listing_type', 'availability', 'reference'],
        ],
    ];

    $labels = [
        'price' => [
            'name' => __('Price'),
            'icon' => 'tag',
        ],
        'price_per_sqft' => [
            'name' => __('Price per sq ft'),
            'icon' => 'arrow-up-right',
        ],
        'bedrooms' => [
            'name' => __('Bedrooms'),
            'icon' => 'bed',
        ],
        'bathrooms' => [
            'name' => __('Bathrooms'),
            'icon' => 'bath',
        ],
        'area' => [
            'name' => __('Area (BUA)'),
            'icon' => 'area',
        ],
        'type' => [
            'name' => __('Property type'),
            'icon' => 'building',
        ],
        'is_furnished' => [
            'name' => __('Condition'),
            'icon' => 'sofa',
        ],
        'floor_number' => [
            'name' => __('Floor'),
            'icon' => 'floor',
        ],
        'balconies' => [
            'name' => __('Balconies'),
            'icon' => 'expand',
        ],
        'parking_spaces' => [
            'name' => __('Parking spaces'),
            'icon' => 'car',
        ],
        'facing' => [
            'name' => __('Facing'),
            'icon' => 'compass',
        ],
        'city' => [
            'name' => __('City'),
            'icon' => 'pin',
        ],
        'area_name' => [
            'name' => __('Community'),
            'icon' => 'map',
        ],
        'address' => [
            'name' => __('Address'),
            'icon' => 'road',
        ],
        'road_width_ft' => [
            'name' => __('Road width'),
            'icon' => 'road',
        ],
        'listing_type' => [
            'name' => __('Listing status'),
            'icon' => 'tag',
        ],
        'availability' => [
            'name' => __('Availability'),
            'icon' => 'check-circle',
        ],
        'reference' => [
            'name' => __('Property reference'),
            'icon' => 'document',
        ],
    ];

    $rawValue = fn ($property, string $key) => match ($key) {
        'price' => $property->isPriceOnRequest() ? null : (float) $property->price,
        'price_per_sqft' => ($rate = \App\Support\CompareTradeoffs::pricePerSqft($property)) === null ? null : round($rate),
        'area' => is_numeric($property->area_value) ? (float) $property->area_value : null,
        'bedrooms' => is_numeric($property->bedrooms) ? (int) $property->bedrooms : null,
        'bathrooms' => is_numeric($property->bathrooms) ? (int) $property->bathrooms : null,
        'balconies' => is_numeric($property->balconies) ? (int) $property->balconies : null,
        'parking_spaces' => is_numeric($property->parking_spaces) ? (int) $property->parking_spaces : null,
        'road_width_ft' => is_numeric($property->road_width_ft) ? (int) $property->road_width_ft : null,
        'area_name' => $property->locationArea?->name,
        'city' => $property->locationArea?->city,
        'type' => $property->propertyType?->label,
        'is_furnished' => (bool) $property->is_furnished,
        'reference' => $property->reference ?: $property->slug,
        default => $property->{$key},
    };

    $sameRow = fn (string $key): bool => $properties->map(fn ($property) => (string) $rawValue($property, $key))->unique()->count() <= 1;

    // Detect leaders for green advantage badges
    $leaders = [];

    // 1. Price
    $priced = $properties->filter(fn ($p) => !$p->isPriceOnRequest() && is_numeric($p->price) && (float) $p->price > 0);
    if ($priced->count() === $properties->count()
        && $properties->pluck('listing_type')->unique()->count() === 1
        && $properties->pluck('price_basis')->unique()->count() === 1
        && !$sameRow('price')) {
        $minPrice = $priced->min('price');
        if ($priced->where('price', $minPrice)->count() === 1) {
            $leaders['price'] = [$priced->firstWhere('price', $minPrice)->id, __('Lower price')];
        }
    }

    // 2. Price per sq ft
    $rated = $properties->filter(fn ($p) => \App\Support\CompareTradeoffs::pricePerSqft($p) !== null);
    if ($rated->count() === $properties->count()
        && $properties->pluck('listing_type')->unique()->count() === 1
        && $properties->pluck('price_basis')->unique()->count() === 1
        && !$sameRow('price_per_sqft')) {
        $rates = $rated->mapWithKeys(fn ($p) => [$p->id => \App\Support\CompareTradeoffs::pricePerSqft($p)]);
        $minRate = $rates->min();
        if ($rates->filter(fn ($r) => $r == $minRate)->count() === 1) {
            $leaders['price_per_sqft'] = [$rates->search($minRate), __('Best value')];
        }
    }

    // 3. Size / Area
    $sized = $properties->filter(fn ($p) => is_numeric($p->area_value) && (float) $p->area_value > 0);
    if ($sized->count() === $properties->count() && $properties->pluck('area_unit')->unique()->count() === 1 && !$sameRow('area')) {
        $maxArea = $sized->max('area_value');
        if ($sized->where('area_value', $maxArea)->count() === 1) {
            $leaders['area'] = [$sized->firstWhere('area_value', $maxArea)->id, __('More space')];
        }
    }

    // 4. Bedrooms
    $bedded = $properties->filter(fn ($p) => is_numeric($p->bedrooms) && (int) $p->bedrooms > 0);
    if ($bedded->count() === $properties->count() && !$sameRow('bedrooms')) {
        $maxBeds = $bedded->max('bedrooms');
        if ($bedded->where('bedrooms', $maxBeds)->count() === 1) {
            $leaders['bedrooms'] = [$bedded->firstWhere('bedrooms', $maxBeds)->id, __('More bedrooms')];
        }
    }

    // 5. Bathrooms
    $bathed = $properties->filter(fn ($p) => is_numeric($p->bathrooms) && (int) $p->bathrooms > 0);
    if ($bathed->count() === $properties->count() && !$sameRow('bathrooms')) {
        $maxBaths = $bathed->max('bathrooms');
        if ($bathed->where('bathrooms', $maxBaths)->count() === 1) {
            $leaders['bathrooms'] = [$bathed->firstWhere('bathrooms', $maxBaths)->id, __('More bathrooms')];
        }
    }

    // 6. Balconies
    $balconied = $properties->filter(fn ($p) => is_numeric($p->balconies) && (int) $p->balconies > 0);
    if ($balconied->count() > 0 && !$sameRow('balconies')) {
        $maxBalc = $balconied->max('balconies');
        if ($balconied->where('balconies', $maxBalc)->count() === 1) {
            $leaders['balconies'] = [$balconied->firstWhere('balconies', $maxBalc)->id, __('More balconies')];
        }
    }

    // 7. Parking spaces
    $parked = $properties->filter(fn ($p) => is_numeric($p->parking_spaces) && (int) $p->parking_spaces > 0);
    if ($parked->count() > 0 && !$sameRow('parking_spaces')) {
        $maxPark = $parked->max('parking_spaces');
        if ($parked->where('parking_spaces', $maxPark)->count() === 1) {
            $leaders['parking_spaces'] = [$parked->firstWhere('parking_spaces', $maxPark)->id, __('More parking')];
        }
    }

    // 8. Road width
    $roaded = $properties->filter(fn ($p) => is_numeric($p->road_width_ft) && (int) $p->road_width_ft > 0);
    if ($roaded->count() > 0 && !$sameRow('road_width_ft')) {
        $maxRoad = $roaded->max('road_width_ft');
        if ($roaded->where('road_width_ft', $maxRoad)->count() === 1) {
            $leaders['road_width_ft'] = [$roaded->firstWhere('road_width_ft', $maxRoad)->id, __('Wider road')];
        }
    }

    // 9. Furnishing / Condition
    $furnished = $properties->filter(fn ($p) => (bool) $p->is_furnished);
    if ($furnished->count() > 0 && $furnished->count() < $properties->count()) {
        if ($furnished->count() === 1) {
            $leaders['is_furnished'] = [$furnished->first()->id, __('Furnished')];
        }
    }

    // 10. Availability / Condition
    $available = $properties->filter(fn ($p) => $p->availability === 'available');
    if ($available->count() > 0 && $available->count() < $properties->count()) {
        if ($available->count() === 1) {
            $leaders['availability'] = [$available->first()->id, __('Ready to move')];
        }
    }

    // Gather unique amenities
    $allAmenityIds = $properties->pluck('amenity_ids')->filter()->flatten()->unique()->values()->all();
    $allAmenities = !empty($allAmenityIds) ? \App\Models\Amenity::query()->whereIn('id', $allAmenityIds)->get() : collect();

    // Helper for property prices
    $formatPrice = function ($property) {
        if ($property->isPriceOnRequest()) {
            return __('Price on request');
        }
        $currency = $property->currency ?: 'BDT';
        if ($currency === 'BDT') {
            return \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
        }
        return $currency . ' ' . number_format((float) $property->price) . ($property->price_basis === 'monthly_rent' ? ' /mo' : '');
    };

    // Helper for price per sqft
    $formatRate = function ($property) {
        $rate = \App\Support\CompareTradeoffs::pricePerSqft($property);
        if ($rate === null) {
            return '—';
        }
        $currency = $property->currency ?: 'BDT';
        if ($currency === 'BDT') {
            return \App\Support\CompareTradeoffs::formattedPricePerSqft($property) ?? '—';
        }
        return $currency . ' ' . number_format($rate) . ' per sq ft';
    };
@endphp

<div x-data="{ differencesOnly: false, addModalOpen: false, showTradeoffs: false }">
    <!-- Top Header: Title & Subtitle matching design -->
    <div class="mb-6">
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-bold text-[var(--uh-ink)] tracking-tight">
            {{ __('Compare properties') }}
        </h1>
        <p class="mt-2 text-base text-[var(--uh-muted)]">
            {{ __('See the differences side by side and find the right home for you.') }}
        </p>
    </div>

    <!-- Top Property Pills Bar -->
    <div class="flex flex-wrap items-center gap-3 pb-6 border-b border-[var(--uh-line)]">
        @foreach($properties as $property)
            @php
                $thumb = $property->featuredImage();
                $place = collect([$property->locationArea?->name, $property->locationArea?->city])->filter()->implode(', ');
            @endphp
            <div class="inline-flex items-center gap-3 bg-[var(--uh-paper)] border border-[var(--uh-line)] rounded-xl px-3 py-2 shadow-2xs hover:shadow-xs transition-shadow">
                @if($thumb)
                    <img src="{{ $thumb->url(120) }}" alt="{{ $property->title }}" class="w-12 h-10 object-cover rounded-lg flex-shrink-0 bg-[var(--uh-paper-2)]">
                @else
                    <div class="w-12 h-10 rounded-lg bg-[var(--uh-paper-2)] flex items-center justify-center flex-shrink-0 text-[var(--uh-muted)]">
                        <x-icon name="image" class="size-4" />
                    </div>
                @endif
                <div class="min-w-0 pr-1 max-w-[150px] sm:max-w-[200px]">
                    <a href="{{ route('properties.show', $property->slug) }}" class="text-xs sm:text-sm font-semibold text-[var(--uh-ink)] truncate block hover:text-[var(--color-accent)] transition-colors leading-snug">
                        {{ $property->title }}
                    </a>
                    <p class="text-[11px] text-[var(--uh-muted)] truncate mt-0.5">
                        {{ $place ?: '—' }}
                    </p>
                </div>
                <button type="button" 
                        class="text-[var(--uh-muted)] hover:text-red-500 dark:hover:text-red-400 p-1 rounded-md hover:bg-[var(--uh-paper-2)] transition-colors cursor-pointer"
                        title="{{ __('Remove from comparison') }}"
                        @click="$store.saved.toggle('compare', {{ $property->id }})">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    <span class="sr-only">{{ __('Remove') }}</span>
                </button>
            </div>
        @endforeach

        @if($properties->count() < 4)
            <button type="button" 
                    @click="addModalOpen = true"
                    class="inline-flex items-center gap-2 border border-[var(--color-accent)]/70 hover:border-[var(--color-accent)] bg-[var(--uh-paper)] hover:bg-[var(--color-accent)]/10 text-[var(--color-accent)] px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-2xs cursor-pointer">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>{{ __('Add property') }}</span>
            </button>
        @endif
    </div>

    <!-- Smart Tradeoff Insight Highlight (Creative Touch) -->
    @if($properties->count() >= 2)
        @php
            $base = $properties->first();
            $other = $properties->skip(1)->first();
            $tradeoff = \App\Support\CompareTradeoffs::against($base, $other);
        @endphp
        @if(!empty($tradeoff['summary']))
            <div class="mt-6 p-4 rounded-xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/25 flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-3">
                    <span class="size-8 rounded-lg bg-[var(--color-accent)] text-white flex items-center justify-center flex-shrink-0">
                        <x-icon name="sparkle" class="size-4" />
                    </span>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--color-accent)]">{{ __('Key Tradeoff: What changes if you choose another') }}</h4>
                        <p class="text-sm font-medium text-[var(--uh-ink)] mt-0.5">
                            <strong class="font-semibold text-[var(--color-accent)]">{{ $other->title }}</strong>: {{ $tradeoff['summary'] }}
                        </p>
                    </div>
                </div>
                @if(!empty($tradeoff['deltas']))
                    <button type="button" @click="showTradeoffs = !showTradeoffs" class="text-xs font-semibold text-[var(--color-accent)] hover:text-[var(--color-accent-hover)] hover:underline flex items-center gap-1 cursor-pointer">
                        <span x-text="showTradeoffs ? '{{ __('Hide breakdown') }}' : '{{ __('View breakdown') }}'"></span>
                        <svg class="size-3.5 transition-transform" :class="{ 'rotate-180': showTradeoffs }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                @endif
            </div>
            @if(!empty($tradeoff['deltas']))
                <div x-show="showTradeoffs" x-cloak class="mt-2 p-4 rounded-xl bg-[var(--uh-paper)] border border-[var(--uh-line)] grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    @foreach($tradeoff['deltas'] as $delta)
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-[var(--uh-paper-2)]/50">
                            @if($delta['tone'] === \App\Support\CompareTradeoffs::TONE_PLUS)
                                <span class="size-5 rounded-full bg-[var(--color-accent)]/15 text-[var(--color-accent)] flex items-center justify-center flex-shrink-0 font-bold">+</span>
                            @elseif($delta['tone'] === \App\Support\CompareTradeoffs::TONE_MINUS)
                                <span class="size-5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 flex items-center justify-center flex-shrink-0 font-bold">−</span>
                            @else
                                <span class="size-5 rounded-full bg-[var(--uh-paper-2)] text-[var(--uh-muted)] flex items-center justify-center flex-shrink-0 font-bold">=</span>
                            @endif
                            <span class="font-medium text-[var(--uh-ink)]">{{ $delta['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    @endif

    <!-- Comparison Table Container -->
    <div class="mt-8 border border-[var(--uh-line)] rounded-2xl bg-[var(--uh-paper)] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <!-- Header Row: "At a glance" + Property Cards -->
                <thead>
                    <tr class="border-b border-[var(--uh-line)]">
                        <!-- Left Column: "At a glance" -->
                        <th scope="col" class="w-64 min-w-[240px] max-w-[260px] p-6 align-top border-r border-[var(--uh-line)] bg-[var(--uh-paper-2)]/30">
                            <h2 class="text-xl sm:text-2xl font-serif font-bold text-[var(--uh-ink)] leading-tight">
                                {{ __('At a glance') }}
                            </h2>
                            <p class="text-xs sm:text-sm text-[var(--uh-muted)] mt-2 leading-relaxed">
                                {{ __('Key details at a glance before diving into the full comparison.') }}
                            </p>

                            <!-- Differences Only Toggle -->
                            <div class="mt-6 pt-6 border-t border-[var(--uh-line)]">
                                <label class="inline-flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-[var(--uh-ink)] select-none">
                                    <input type="checkbox" x-model="differencesOnly" class="sr-only peer">
                                    <div class="w-9 h-5 bg-[var(--uh-line-strong)] peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-[var(--uh-line)] after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--color-accent)] relative"></div>
                                    <span>{{ __('Show differences only') }}</span>
                                </label>
                            </div>
                        </th>

                        <!-- Property Hero Cards (Columns) -->
                        @foreach($properties as $property)
                            @php
                                $image = $property->featuredImage();
                                $place = collect([$property->locationArea?->name, $property->locationArea?->city])->filter()->implode(', ');
                                $priceFormatted = $formatPrice($property);
                            @endphp
                            <th scope="col" class="min-w-[280px] p-5 sm:p-6 align-top border-r border-[var(--uh-line)] last:border-r-0">
                                <!-- Photo Card -->
                                <div class="relative rounded-2xl overflow-hidden aspect-[16/10] bg-[var(--uh-paper-2)] border border-[var(--uh-line)] group shadow-2xs">
                                    @if($image)
                                        <img src="{{ $image->url(640) }}" alt="{{ $property->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-[var(--uh-muted)]">
                                            <x-icon name="image" class="size-8" />
                                        </div>
                                    @endif

                                    <!-- For Sale / For Rent Badge -->
                                    <span class="absolute top-3 left-3 bg-[var(--color-accent)] text-white text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
                                        {{ $property->listing_type === 'rent' ? __('For Rent') : __('For Sale') }}
                                    </span>

                                    <!-- Favorite Heart Button -->
                                    <button type="button" 
                                            @click.stop.prevent="$store.saved.toggle('shortlist', {{ $property->id }})"
                                            class="absolute top-3 right-3 size-9 rounded-full bg-[var(--uh-paper)]/95 backdrop-blur-sm shadow-md flex items-center justify-center text-[var(--uh-ink)] hover:text-red-500 transition-colors z-10 cursor-pointer"
                                            title="{{ __('Save to shortlist') }}">
                                        <span x-show="$store.saved.has('shortlist', {{ $property->id }})">
                                            <x-icon name="heart-solid" class="size-4 text-red-500" />
                                        </span>
                                        <span x-show="!$store.saved.has('shortlist', {{ $property->id }})">
                                            <x-icon name="heart" class="size-4" />
                                        </span>
                                    </button>
                                </div>

                                <!-- Property Info: Title, Location & Price -->
                                <div class="mt-3.5 flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1">
                                    <div class="min-w-0 pr-2">
                                        <a href="{{ route('properties.show', $property->slug) }}" class="font-bold text-base sm:text-lg text-[var(--uh-ink)] hover:text-[var(--color-accent)] transition-colors line-clamp-1 block leading-snug">
                                            {{ $property->title }}
                                        </a>
                                        <div class="flex items-center gap-1.5 text-xs sm:text-sm text-[var(--uh-muted)] mt-1 line-clamp-1">
                                            <x-icon name="pin" class="size-3.5 flex-shrink-0 text-[var(--color-accent)]" />
                                            <span>{{ $place ?: '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="text-left sm:text-right flex-shrink-0 mt-1 sm:mt-0">
                                        <span class="text-base sm:text-lg font-bold text-[var(--uh-ink)] tracking-tight">
                                            {{ $priceFormatted }}
                                        </span>
                                    </div>
                                </div>
                            </th>
                        @endforeach

                        @if($properties->count() === 1)
                            <th scope="col" class="min-w-[280px] p-5 sm:p-6 align-top">
                                <button type="button" 
                                        @click="addModalOpen = true"
                                        class="w-full h-full min-h-[220px] rounded-2xl border-2 border-dashed border-[var(--uh-line-strong)] p-6 flex flex-col items-center justify-center text-center bg-[var(--uh-paper-2)]/30 hover:bg-[var(--color-accent)]/5 hover:border-[var(--color-accent)]/60 transition-all cursor-pointer group">
                                    <span class="size-12 rounded-full bg-[var(--color-accent)]/10 text-[var(--color-accent)] flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    </span>
                                    <span class="font-bold text-sm text-[var(--uh-ink)]">{{ __('+ Add a second property') }}</span>
                                    <span class="text-xs text-[var(--uh-muted)] mt-1 max-w-[200px] leading-relaxed">{{ __('Pick another property to compare side-by-side') }}</span>
                                </button>
                            </th>
                        @endif
                    </tr>
                </thead>

                <!-- Comparison Specification Groups -->
                @foreach($groups as $group)
                    <tbody class="divide-y divide-[var(--uh-line)]">
                        <!-- Group Category Header -->
                        <tr class="bg-[var(--uh-paper-2)]/60">
                            <th scope="colgroup" colspan="{{ $properties->count() + 1 + ($properties->count() === 1 ? 1 : 0) }}" class="px-6 py-3 text-xs sm:text-sm font-bold text-[var(--uh-ink)]">
                                <div class="flex items-center gap-2.5">
                                    <x-icon :name="$group['icon']" class="size-4 text-[var(--color-accent)]" />
                                    <span>{{ $group['title'] }}</span>
                                </div>
                            </th>
                        </tr>

                        <!-- Rows for this group -->
                        @foreach($group['keys'] as $key)
                            @php $same = $sameRow($key); @endphp
                            <tr @if($same) x-show="!differencesOnly" @endif class="hover:bg-[var(--uh-paper-2)]/40 transition-colors">
                                <!-- Feature Name / Icon Column -->
                                <th scope="row" class="px-6 py-3.5 text-xs sm:text-sm font-medium text-[var(--uh-muted)] border-r border-[var(--uh-line)] align-middle bg-[var(--uh-paper-2)]/20">
                                    <div class="flex items-center gap-2.5">
                                        @if(isset($labels[$key]['icon']))
                                            <x-icon :name="$labels[$key]['icon']" class="size-4 text-[var(--uh-muted)] flex-shrink-0" />
                                        @endif
                                        <span>{{ $labels[$key]['name'] ?? $key }}</span>
                                    </div>
                                </th>

                                <!-- Property Value Columns -->
                                @foreach($properties as $property)
                                    @php $leads = isset($leaders[$key]) && $leaders[$key][0] === $property->id; @endphp
                                    <td class="px-6 py-3.5 text-xs sm:text-sm text-[var(--uh-ink)] border-r border-[var(--uh-line)] last:border-r-0 align-middle">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="font-medium">
                                                @switch($key)
                                                    @case('price')
                                                        {{ $formatPrice($property) }}
                                                        @break
                                                    @case('price_per_sqft')
                                                        {{ $formatRate($property) }}
                                                        @break
                                                    @case('area')
                                                        {{ $property->area_value ? number_format((float) $property->area_value) . ' ' . ($property->area_unit ?? 'sq ft') : '—' }}
                                                        @break
                                                    @case('bedrooms')
                                                        {{ $property->bedrooms ?? '—' }}
                                                        @break
                                                    @case('bathrooms')
                                                        {{ $property->bathrooms ?? '—' }}
                                                        @break
                                                    @case('type')
                                                        {{ $property->propertyType?->label ?? '—' }}
                                                        @break
                                                    @case('is_furnished')
                                                        {{ $property->is_furnished ? __('Furnished') : ($property->availability === 'available' ? __('Ready to move') : __('Unfurnished')) }}
                                                        @break
                                                    @case('floor_number')
                                                        {{ $property->floor_number ? __('Floor :num', ['num' => $property->floor_number]) : '—' }}
                                                        @break
                                                    @case('balconies')
                                                        {{ $property->balconies ?? '—' }}
                                                        @break
                                                    @case('parking_spaces')
                                                        {{ $property->parking_spaces ?? '—' }}
                                                        @break
                                                    @case('facing')
                                                        {{ $property->facing ? ucfirst($property->facing) : '—' }}
                                                        @break
                                                    @case('city')
                                                        {{ $property->locationArea?->city ?? '—' }}
                                                        @break
                                                    @case('area_name')
                                                        {{ $property->locationArea?->name ?? '—' }}
                                                        @break
                                                    @case('address')
                                                        {{ $property->address ?? '—' }}
                                                        @break
                                                    @case('road_width_ft')
                                                        {{ $property->road_width_ft ? $property->road_width_ft . ' ft' : '—' }}
                                                        @break
                                                    @case('listing_type')
                                                        <span class="inline-flex items-center gap-1.5">
                                                            <span class="size-2 rounded-full bg-[var(--color-accent)]"></span>
                                                            {{ $property->listing_type === 'rent' ? __('For Rent') : __('For Sale') }}
                                                        </span>
                                                        @break
                                                    @case('availability')
                                                        <x-ui.status :status="$property->availability" />
                                                        @break
                                                    @case('reference')
                                                        <span class="font-mono text-xs text-[var(--uh-muted)]">{{ $property->reference ?: ($property->slug ? '#'.$property->slug : '—') }}</span>
                                                        @break
                                                    @default
                                                        {{ $property->{$key} ?? '—' }}
                                                @endswitch
                                            </span>

                                            <!-- Advantage Pill Badge (from image: Lower price, Better value, More bedrooms, etc.) -->
                                            @if($leads)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[var(--color-accent)]/10 text-[var(--color-accent)] border border-[var(--color-accent)]/25 flex-shrink-0">
                                                    <svg class="size-3.5 text-[var(--color-accent)]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                                    <span>{{ $leaders[$key][1] }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach

                                @if($properties->count() === 1)
                                    <td class="px-6 py-3.5 text-xs text-[var(--uh-muted)] italic align-middle">
                                        {{ __('Add property to compare') }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach

                <!-- Features & Amenities Matrix (Creative Touch) -->
                @if($allAmenities->isNotEmpty())
                    <tbody class="divide-y divide-[var(--uh-line)]">
                        <tr class="bg-[var(--uh-paper-2)]/60">
                            <th scope="colgroup" colspan="{{ $properties->count() + 1 + ($properties->count() === 1 ? 1 : 0) }}" class="px-6 py-3 text-xs sm:text-sm font-bold text-[var(--uh-ink)]">
                                <div class="flex items-center gap-2.5">
                                    <x-icon name="sparkle" class="size-4 text-[var(--color-accent)]" />
                                    <span>{{ __('Features & amenities') }}</span>
                                </div>
                            </th>
                        </tr>
                        @foreach($allAmenities as $amenity)
                            @php
                                $amenityInProps = $properties->map(fn ($p) => in_array($amenity->id, (array) ($p->amenity_ids ?? []), true));
                                $allSame = $amenityInProps->unique()->count() <= 1;
                            @endphp
                            <tr @if($allSame) x-show="!differencesOnly" @endif class="hover:bg-[var(--uh-paper-2)]/40 transition-colors">
                                <th scope="row" class="px-6 py-3 text-xs sm:text-sm font-medium text-[var(--uh-muted)] border-r border-[var(--uh-line)] align-middle bg-[var(--uh-paper-2)]/20">
                                    <div class="flex items-center gap-2.5">
                                        <x-icon name="check-circle" class="size-4 text-[var(--color-accent)]/70 flex-shrink-0" />
                                        <span>{{ $amenity->label }}</span>
                                    </div>
                                </th>
                                @foreach($properties as $property)
                                    @php $hasAmenity = in_array($amenity->id, (array) ($property->amenity_ids ?? []), true); @endphp
                                    <td class="px-6 py-3 text-xs sm:text-sm border-r border-[var(--uh-line)] last:border-r-0 align-middle">
                                        @if($hasAmenity)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[var(--color-accent)]/10 text-[var(--color-accent)]">
                                                <svg class="size-3.5 text-[var(--color-accent)]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                                <span>{{ __('Included') }}</span>
                                            </span>
                                        @else
                                            <span class="text-[var(--uh-muted)]/50 font-bold">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                @if($properties->count() === 1)
                                    <td class="px-6 py-3 text-xs text-[var(--uh-muted)] italic align-middle">—</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                @endif

                <!-- Next Steps / Actions Footer -->
                <tfoot>
                    <tr class="bg-[var(--uh-paper-2)]/40 border-t border-[var(--uh-line)]">
                        <th scope="row" class="px-6 py-5 text-xs sm:text-sm font-bold text-[var(--uh-ink)] border-r border-[var(--uh-line)] align-middle">
                            {{ __('Next step') }}
                        </th>
                        @foreach($properties as $property)
                            <td class="px-6 py-5 border-r border-[var(--uh-line)] last:border-r-0 align-middle">
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                                    <a href="{{ route('properties.show', $property->slug) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-[var(--color-accent)] hover:bg-[var(--color-accent-hover)] text-white transition-colors shadow-2xs">
                                        {{ __('View property') }}
                                    </a>
                                    @unless($property->isUnavailable())
                                        <a href="{{ route('properties.show', $property->slug).'#contact' }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold border border-[var(--uh-line-strong)] text-[var(--uh-ink)] hover:bg-[var(--uh-paper-2)] transition-colors">
                                            {{ __('Book a visit') }}
                                        </a>
                                    @endunless
                                </div>
                            </td>
                        @endforeach
                        @if($properties->count() === 1)
                            <td class="px-6 py-5 align-middle"></td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Interactive "+ Add property" Modal Dialog -->
    <div x-show="addModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         @keydown.escape.window="addModalOpen = false">
        <div class="bg-[var(--uh-paper)] rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-[var(--uh-line)] max-h-[85vh] flex flex-col"
             @click.outside="addModalOpen = false">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-[var(--uh-line)]">
                <div>
                    <h3 class="text-lg font-serif font-bold text-[var(--uh-ink)]">{{ __('Add property to compare') }}</h3>
                    <p class="text-xs text-[var(--uh-muted)] mt-0.5">{{ __('Select another property to compare side-by-side (max 4).') }}</p>
                </div>
                <button type="button" @click="addModalOpen = false" class="text-[var(--uh-muted)] hover:text-[var(--uh-ink)] p-1 rounded-lg hover:bg-[var(--uh-paper-2)] transition-colors cursor-pointer">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Modal Body: Suggested Properties -->
            <div class="overflow-y-auto py-4 space-y-3 flex-1">
                @php $hasSuggestedProperties = isset($suggestedProperties) && $suggestedProperties->isNotEmpty(); @endphp
                @if($hasSuggestedProperties)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($suggestedProperties as $suggested)
                            @php
                                $sThumb = $suggested->featuredImage();
                                $sPlace = collect([$suggested->locationArea?->name, $suggested->locationArea?->city])->filter()->implode(', ');
                                $sPrice = $formatPrice($suggested);
                            @endphp
                            <div class="p-3 rounded-xl border border-[var(--uh-line)] bg-[var(--uh-paper-2)]/30 flex items-center gap-3 hover:border-[var(--color-accent)]/50 transition-colors">
                                @if($sThumb)
                                    <img src="{{ $sThumb->url(120) }}" alt="{{ $suggested->title }}" class="w-16 h-14 object-cover rounded-lg flex-shrink-0 bg-[var(--uh-paper-2)]">
                                @else
                                    <div class="w-16 h-14 rounded-lg bg-[var(--uh-paper-2)] flex items-center justify-center flex-shrink-0 text-[var(--uh-muted)]">
                                        <x-icon name="image" class="size-5" />
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-semibold text-xs text-[var(--uh-ink)] truncate">{{ $suggested->title }}</h4>
                                    <p class="text-[11px] text-[var(--uh-muted)] truncate mt-0.5">{{ $sPlace ?: '—' }}</p>
                                    <p class="text-xs font-bold text-[var(--color-accent)] mt-1">{{ $sPrice }}</p>
                                </div>
                                <button type="button" 
                                        @click="$store.saved.toggle('compare', {{ $suggested->id }}); addModalOpen = false"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-[var(--color-accent)] hover:bg-[var(--color-accent-hover)] text-white transition-colors flex-shrink-0 cursor-pointer">
                                    {{ __('+ Add') }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-sm text-[var(--uh-muted)] py-6">{{ __('No other properties available to add right now.') }}</p>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="pt-4 border-t border-[var(--uh-line)] flex items-center justify-between">
                <span class="text-xs text-[var(--uh-muted)]">{{ __('Looking for something specific?') }}</span>
                <a href="{{ route('properties.index') }}" class="text-xs font-semibold text-[var(--color-accent)] hover:text-[var(--color-accent-hover)] hover:underline flex items-center gap-1">
                    <span>{{ __('Browse all listings') }}</span>
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </div>
</div>
