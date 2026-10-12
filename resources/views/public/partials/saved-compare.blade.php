@php
    $groups = [
        __('Price') => ['price', 'price_per_sqft'],
        __('Size & layout') => ['area', 'bedrooms', 'bathrooms', 'floor_number', 'is_furnished'],
        __('Location & type') => ['area_name', 'city', 'type'],
        __('Availability') => ['listing_type', 'availability'],
    ];
    $labels = [
        'price' => __('Price'),
        'price_per_sqft' => __('Price per sq ft'),
        'area' => __('Size'),
        'bedrooms' => __('Bedrooms'),
        'bathrooms' => __('Bathrooms'),
        'floor_number' => __('Floor'),
        'is_furnished' => __('Furnishing'),
        'area_name' => __('Area'),
        'city' => __('City'),
        'type' => __('Property type'),
        'listing_type' => __('Purpose'),
        'availability' => __('Status'),
    ];

    $rawValue = fn ($property, string $key) => match ($key) {
        'price' => $property->isPriceOnRequest() ? null : $property->price,
        'price_per_sqft' => ($rate = \App\Support\CompareTradeoffs::pricePerSqft($property)) === null ? null : round($rate),
        'area' => $property->area_value ? $property->area_value.' '.$property->area_unit : null,
        'area_name' => $property->locationArea?->name,
        'city' => $property->locationArea?->city,
        'type' => $property->propertyType?->label,
        default => $property->{$key},
    };
    $sameRow = fn (string $key): bool => $properties->map(fn ($property) => (string) $rawValue($property, $key))->unique()->count() <= 1;

    $leaders = [];
    $priced = $properties->filter(fn ($property) => ! $property->isPriceOnRequest() && is_numeric($property->price));
    if ($priced->count() === $properties->count()
        && $properties->pluck('listing_type')->unique()->count() === 1
        && $properties->pluck('price_basis')->unique()->count() === 1
        && ! $sameRow('price')) {
        $leaders['price'] = [$priced->sortBy('price')->first()->id, __('Lowest')];
    }
    $rated = $properties->filter(fn ($property) => \App\Support\CompareTradeoffs::pricePerSqft($property) !== null);
    if ($rated->count() === $properties->count()
        && $properties->pluck('listing_type')->unique()->count() === 1
        && $properties->pluck('price_basis')->unique()->count() === 1
        && ! $sameRow('price_per_sqft')) {
        $leaders['price_per_sqft'] = [$rated->sortBy(fn ($property) => \App\Support\CompareTradeoffs::pricePerSqft($property))->first()->id, __('Best value')];
    }
    $sized = $properties->filter(fn ($property) => is_numeric($property->area_value));
    if ($sized->count() === $properties->count() && $properties->pluck('area_unit')->unique()->count() === 1 && ! $sameRow('area')) {
        $leaders['area'] = [$sized->sortByDesc('area_value')->first()->id, __('Largest')];
    }
    foreach (['bedrooms', 'bathrooms'] as $countKey) {
        $counted = $properties->filter(fn ($property) => is_numeric($property->{$countKey}));
        if ($counted->count() === $properties->count() && ! $sameRow($countKey)) {
            $top = $counted->max($countKey);
            if ($counted->where($countKey, $top)->count() === 1) {
                $leaders[$countKey] = [$counted->firstWhere($countKey, $top)->id, __('Most')];
            }
        }
    }
@endphp

<div x-data="{ differencesOnly: false, baseline: {{ (int) $properties->first()?->id }} }">
    @if($properties->count() >= 2)
    <section class="uh-tradeoffs" aria-labelledby="tradeoffs-heading">
        <div class="uh-tradeoffs-head">
            <div>
                <h2 id="tradeoffs-heading" class="uh-h3 flex items-center gap-2">
                    <x-icon name="sparkle" class="size-5 text-[var(--uh-accent)]" />
                    {{ __('Smart Comparison') }}
                </h2>
                <p class="mt-2 text-sm text-[var(--uh-muted)]">{{ __('Our automated analysis highlights the key tradeoffs to help you decide.') }}</p>
            </div>
            <div class="uh-tradeoffs-pick" role="group" aria-label="{{ __('Starting point') }}">
                @foreach($properties as $property)
                    <button type="button" class="uh-chip" :class="baseline === {{ (int) $property->id }} ? 'uh-chip-active' : ''"
                            :aria-pressed="(baseline === {{ (int) $property->id }}).toString()"
                            @click="baseline = {{ (int) $property->id }}">
                        {{ \Illuminate\Support\Str::limit($property->title, 34) }}
                    </button>
                @endforeach
            </div>
        </div>

        @foreach($properties as $base)
            <div class="uh-tradeoffs-grid" x-show="baseline === {{ (int) $base->id }}" @unless($loop->first) x-cloak @endunless>
                @foreach($properties->reject(fn ($other) => $other->id === $base->id) as $other)
                    @php($tradeoff = \App\Support\CompareTradeoffs::against($base, $other))
                    <article class="relative bg-[var(--uh-paper)] rounded-[var(--uh-radius)] border border-[var(--uh-line)] p-5 sm:p-6 shadow-sm overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[var(--uh-accent)] to-purple-500"></div>
                        <p class="text-sm font-medium text-[var(--uh-muted)] mb-1">{{ __('Compared with :title', ['title' => \Illuminate\Support\Str::limit($base->title, 40)]) }}</p>
                        <h3 class="text-lg font-semibold text-[var(--uh-ink)] mb-4 leading-tight">
                            <a href="{{ route('properties.show', $other->slug) }}" class="hover:text-[var(--uh-accent)] transition-colors">{{ $other->title }}</a>
                        </h3>
                        
                        @if($tradeoff['summary'])
                            <div class="p-3 mb-4 rounded bg-[var(--uh-accent-ring)] border border-[var(--uh-accent)]/20 text-[var(--uh-accent)] font-medium text-sm sm:text-base leading-snug">
                                {{ $tradeoff['summary'] }}
                            </div>
                        @elseif($tradeoff['deltas'] === [])
                            <div class="p-3 mb-4 rounded bg-gray-50 dark:bg-white/5 border border-[var(--uh-line)] text-[var(--uh-muted)] text-sm sm:text-base">
                                {{ __('No measurable difference in price, size or rooms.') }}
                            </div>
                        @endif
                        
                        @if($tradeoff['deltas'] !== [])
                            <ul class="space-y-2.5">
                                @foreach($tradeoff['deltas'] as $delta)
                                    <li class="flex items-start gap-2.5 text-[0.9375rem] text-[var(--uh-ink-soft)]">
                                        @if($delta['tone'] === \App\Support\CompareTradeoffs::TONE_PLUS)
                                            <span class="mt-0.5 flex-shrink-0 size-5 rounded-full bg-[var(--uh-accent-ring)] text-[var(--uh-accent)] flex items-center justify-center">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                            </span>
                                        @elseif($delta['tone'] === \App\Support\CompareTradeoffs::TONE_MINUS)
                                            <span class="mt-0.5 flex-shrink-0 size-5 rounded-full bg-[var(--uh-accent-ring)] text-[var(--uh-accent)] flex items-center justify-center">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                                            </span>
                                        @else
                                            <span class="mt-0.5 flex-shrink-0 size-5 flex items-center justify-center text-[var(--uh-faint)] font-bold">
                                                =
                                            </span>
                                        @endif
                                        <span class="font-medium">{{ $delta['label'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @endforeach
            </div>
        @endforeach
    </section>
    @endif

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-[var(--uh-muted)]">
            {{ trans_choice(':count property side by side|:count properties side by side', $properties->count(), ['count' => $properties->count()]) }}
        </p>
        <label class="uh-check">
            <input type="checkbox" x-model="differencesOnly">
            {{ __('Show differences only') }}
        </label>
    </div>

    <p class="mb-3 text-sm text-[var(--uh-muted)] sm:hidden">{{ __('Scroll the table sideways to see every property.') }}</p>
    <div class="uh-compare-table">
        <div class="uh-table-scroll">
            <table class="uh-table">
                <caption class="sr-only">{{ __('Property comparison') }}</caption>
                <thead>
                    <tr>
                        <th scope="col" class="uh-compare-corner">
                            <span class="uh-keyfact-label">{{ __('Detail') }}</span>
                        </th>
                        @foreach($properties as $property)
                            @php($image = $property->featuredImage())
                            <th scope="col" class="uh-compare-head">
                                <a href="{{ route('properties.show', $property->slug) }}" class="uh-compare-photo" tabindex="-1" aria-hidden="true">
                                    @if($image)
                                        <img src="{{ $image->url(480) }}" alt="" loading="lazy" decoding="async">
                                    @else
                                        <span class="uh-media-placeholder"></span>
                                    @endif
                                </a>
                                <a class="uh-compare-name" href="{{ route('properties.show', $property->slug) }}">{{ $property->title }}</a>
                                <button type="button" class="uh-btn-text uh-compare-remove"
                                        @click="$store.saved.toggle('compare', {{ $property->id }})">{{ __('Remove') }}</button>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                @foreach($groups as $group => $keys)
                    <tbody>
                        <tr>
                            <th scope="colgroup" colspan="{{ $properties->count() + 1 }}" class="uh-compare-group">{{ $group }}</th>
                        </tr>
                        @foreach($keys as $key)
                            @php($same = $sameRow($key))
                            <tr @if($same) x-show="!differencesOnly" @endif @class(['is-same' => $same])>
                                <th scope="row" class="uh-compare-label">{{ $labels[$key] }}</th>
                                @foreach($properties as $property)
                                    @php($leads = isset($leaders[$key]) && $leaders[$key][0] === $property->id)
                                    <td @class(['is-lead' => $leads])>
                                        @switch($key)
                                            @case('price')
                                                <span class="uh-numeric font-medium">{{ $property->isPriceOnRequest() ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis) }}</span>
                                                @break
                                            @case('price_per_sqft')
                                                <span class="uh-numeric">{{ \App\Support\CompareTradeoffs::formattedPricePerSqft($property) ?? '—' }}</span>
                                                @break
                                            @case('area')
                                                {{ $property->area_value ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit) : '—' }}
                                                @break
                                            @case('is_furnished')
                                                {{ $property->is_furnished ? __('Furnished') : __('Unfurnished') }}
                                                @break
                                            @case('area_name')
                                                {{ $property->locationArea?->name ?? '—' }}
                                                @break
                                            @case('city')
                                                {{ $property->locationArea?->city ?? '—' }}
                                                @break
                                            @case('type')
                                                {{ $property->propertyType?->label ?? '—' }}
                                                @break
                                            @case('listing_type')
                                                {{ $property->listing_type === 'rent' ? __('For rent') : __('For sale') }}
                                                @break
                                            @case('availability')
                                                <x-ui.status :status="$property->availability" />
                                                @break
                                            @default
                                                <span class="uh-numeric">{{ $property->{$key} ?? '—' }}</span>
                                        @endswitch
                                        @if($leads)
                                            <span class="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded bg-[var(--uh-accent-ring)] text-[var(--uh-accent)] text-xs font-medium">
                                                <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                                {{ $leaders[$key][1] }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach
                <tfoot>
                    <tr class="uh-compare-decide">
                        <th scope="row" class="uh-compare-label">{{ __('Next step') }}</th>
                        @foreach($properties as $property)
                            <td>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                    <x-ui.pill-link variant="light" :href="route('properties.show', $property->slug)">
                                        {{ \App\Support\PropertyStory::for($property)->exploreLabel() }}
                                    </x-ui.pill-link>
                                    @unless($property->isUnavailable())
                                        <x-ui.pill-link variant="light" :href="route('properties.show', $property->slug).'#contact'">
                                            {{ __('Book a visit') }}
                                        </x-ui.pill-link>
                                    @endunless
                                </div>
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
