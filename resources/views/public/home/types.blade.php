@php
    $categoryLabels = \App\Models\PropertyType::categoryLabels();
    $firstCategory = $typeGroups->keys()->first();
    $typeIcons = [
        'apartment' => 'building',
        'penthouse' => 'building',
        'residential-building' => 'building',
        'commercial-building' => 'building',
        'duplex' => 'home',
        'house' => 'home',
        'townhouse' => 'home',
        'plot' => 'area',
        'commercial-plot' => 'area',
        'land' => 'area',
        'single-room' => 'bed',
        'sublet-room' => 'bed',
        'hotel' => 'bed',
        'hostel' => 'users',
        'co-working' => 'users',
        'office' => 'dashboard',
        'shop' => 'tag',
        'showroom' => 'car',
        'restaurant' => 'sofa',
        'warehouse' => 'inbox',
        'factory' => 'settings',
    ];
@endphp

<section id="home-types" class="uh-section scroll-mt-20" x-data="{ category: @js($firstCategory) }" aria-labelledby="types-title">
    <div class="uh-container">
        <header class="uh-section-head" data-reveal>
            <h2 id="types-title" class="uh-h2">{{ __('Explore by Property Type') }}</h2>
            <p class="uh-lede">{{ __('Start with the kind of space you have in mind.') }}</p>
        </header>

        <div class="uh-type-tiles-bar" data-reveal>
            @if($typeGroups->count() > 1)
                <div class="uh-seg" role="group" aria-label="{{ __('Main property type') }}">
                    @foreach($typeGroups->keys() as $category)
                        <button type="button" class="uh-seg-btn" @click="category = @js($category)"
                                :aria-pressed="(category === @js($category)).toString()" aria-pressed="{{ $category === $firstCategory ? 'true' : 'false' }}">
                            {{ $categoryLabels[$category] ?? $category }}
                        </button>
                    @endforeach
                </div>
            @endif
            <x-ui.pill-link :href="route('properties.index', ['category' => $firstCategory])" x-bind:href="{{ Js::from(route('properties.index')) }} + '?category=' + category">
                {{ __('View all') }}
            </x-ui.pill-link>
        </div>

        @foreach($typeGroups as $category => $group)
            <ul class="uh-type-tiles" x-show="category === @js($category)" @if($category !== $firstCategory) style="display: none" @endif
                aria-label="{{ $categoryLabels[$category] ?? $category }}">
                @foreach($group as $type)
                    <li data-reveal style="--uh-i: {{ $loop->index % 5 }}">
                        <a class="uh-type-tile" href="{{ route('properties.index', ['property_type_ids' => [$type->id]]) }}">
                            <span class="uh-type-tile-icon" aria-hidden="true">
                                <x-icon :name="$typeIcons[$type->key] ?? 'building'" class="size-5" />
                            </span>
                            <span class="uh-type-tile-name">{{ $type->label }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>
</section>
