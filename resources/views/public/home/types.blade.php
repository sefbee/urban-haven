@php
    $typeIcons = [
        'apartment' => 'building',
        'apt' => 'building',
        'house' => 'home',
        'duplex' => 'home',
        'land' => 'map',
        'plot' => 'map',
        'commercial' => 'building',
        'office' => 'dashboard',
        'shop' => 'tag',
    ];
@endphp

<section class="uh-section border-t border-line bg-sand">
    <div class="uh-container">
        <h2 class="uh-h2">{{ __('Explore by Property Type') }}</h2>
        <p class="uh-lede mt-3 max-w-2xl">{{ __('Browse Urban Haven inventory by the kinds of homes we actually publish.') }}</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($typeCards as $type)
                <a href="{{ route('properties.index', ['property_type_id' => $type->id]) }}"
                   class="group relative overflow-hidden rounded-2xl bg-ink p-5 text-cream ring-1 ring-ink/10 transition hover:-translate-y-0.5 hover:shadow-uh">
                    <span class="flex size-12 items-center justify-center rounded-xl bg-white/8 text-gold">
                        <x-icon :name="$typeIcons[$type->key] ?? 'home'" class="size-6" />
                    </span>
                    <span class="mt-5 block text-lg font-semibold tracking-tight">{{ $type->label }}</span>
                    <span class="mt-1 block text-sm text-cream/65">
                        {{ trans_choice(':count property|:count properties', $type->properties_count, ['count' => $type->properties_count]) }}
                    </span>
                    <span class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-gold">
                        {{ __('View listings') }}
                        <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" />
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
