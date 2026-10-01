@php
    $groups = [
        __('Price') => ['price'],
        __('Size & layout') => ['area', 'bedrooms', 'bathrooms', 'floor_number', 'is_furnished'],
        __('Location & type') => ['area_name', 'city', 'type'],
        __('Availability') => ['listing_type', 'availability'],
    ];
    $labels = [
        'price' => __('Price'),
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
@endphp

                    <div class="uh-panel-flush mt-4 overflow-hidden">
    <div class="uh-table-scroll">
        <table class="uh-table">
            <caption class="sr-only">{{ __('Property comparison') }}</caption>
            <thead>
                <tr>
                    <th scope="col" class="sticky left-0 z-10 bg-sand">{{ __('Detail') }}</th>
                    @foreach($properties as $property)
                        <th scope="col" class="min-w-44 align-top">
                            <a class="uh-link-quiet" href="{{ route('properties.show', $property->slug) }}">{{ $property->title }}</a>
                            <button type="button" class="mt-2 flex items-center gap-1 text-xs font-semibold text-[var(--color-danger)]"
                                    @click="$store.saved.toggle('compare', {{ $property->id }})">
                                <x-icon name="trash" class="size-3.5" />{{ __('Remove') }}
                            </button>
                        </th>
                    @endforeach
                </tr>
            </thead>

            @foreach($groups as $group => $keys)
                <tbody>
                    <tr>
                        <th scope="colgroup" colspan="{{ $properties->count() + 1 }}"
                            class="bg-sand/60 text-left text-[0.6875rem] font-semibold uppercase tracking-[0.14em] text-[var(--color-muted)]">
                            {{ $group }}
                        </th>
                    </tr>
                    @foreach($keys as $key)
                        <tr>
                            <th scope="row" class="sticky left-0 z-10 bg-paper text-left font-medium">{{ $labels[$key] }}</th>
                            @foreach($properties as $property)
                                <td>
                                    @switch($key)
                                        @case('price')
                                            <span class="uh-numeric font-semibold text-forest">{{ $property->isPriceOnRequest() ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis) }}</span>
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
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            @endforeach
        </table>
    </div>
</div>

