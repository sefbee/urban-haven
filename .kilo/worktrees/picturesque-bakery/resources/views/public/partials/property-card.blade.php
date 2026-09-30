@php
    $image = $property->featuredImage();
    $showShortlist = $showShortlist ?? true;
    $saved = in_array($property->id, session('shortlist', []), true);
    $area = $property->area_value
        ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit)
        : null;
@endphp

<article class="uh-card uh-card-hover flex flex-col">
    <a href="{{ route('properties.show', $property->slug) }}" class="uh-media uh-media-zoom block aspect-4/3" tabindex="-1" aria-hidden="true">
        @if($image)
            <img src="{{ $image->url(768) }}"
                 srcset="{{ $image->url(480) }} 480w, {{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w"
                 sizes="(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw"
                 alt="" loading="lazy" decoding="async">
        @else
            <span class="uh-media-placeholder">
                <span class="flex items-center gap-2 text-sm font-medium">
                    <x-icon name="image" class="size-4 opacity-70" />
                    {{ $property->locationArea?->name ?? __('Urban Haven') }}
                </span>
            </span>
        @endif

        <span class="absolute inset-x-3 top-3 flex flex-wrap items-start justify-between gap-2">
            <x-ui.badge tone="dark">{{ $property->listing_type === 'rent' ? __('For rent') : __('For sale') }}</x-ui.badge>
            @if($property->availability !== 'available')
                <x-ui.status :status="$property->availability" />
            @endif
        </span>
    </a>

    <div class="flex flex-1 flex-col p-4 sm:p-5">
        <p class="flex items-center gap-1.5 text-xs font-medium text-[var(--color-muted)]">
            <x-icon name="pin" class="size-3.5 shrink-0 text-[var(--color-gold-ink)]" />
            <span class="truncate">{{ $property->locationArea?->name }}@if($property->locationArea?->city), {{ $property->locationArea->city }}@endif</span>
        </p>

        <h3 class="uh-h3 mt-2">
            <a class="line-clamp-2 transition hover:text-forest" href="{{ route('properties.show', $property->slug) }}">{{ $property->title }}</a>
        </h3>

        <p class="uh-price mt-3 text-forest">{{ \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis) }}</p>

        <ul class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-[var(--color-muted)]">
            @if($property->bedrooms)
                <li class="flex items-center gap-1.5"><x-icon name="bed" class="size-4 shrink-0" /><span class="uh-numeric">{{ $property->bedrooms }}</span> {{ __('bed') }}</li>
            @endif
            @if($property->bathrooms)
                <li class="flex items-center gap-1.5"><x-icon name="bath" class="size-4 shrink-0" /><span class="uh-numeric">{{ $property->bathrooms }}</span> {{ __('bath') }}</li>
            @endif
            @if($area)
                <li class="flex items-center gap-1.5"><x-icon name="area" class="size-4 shrink-0" /><span class="uh-numeric">{{ $area }}</span></li>
            @endif
            @if($property->propertyType)
                <li class="flex items-center gap-1.5"><x-icon name="building" class="size-4 shrink-0" />{{ $property->propertyType->label }}</li>
            @endif
        </ul>

        <div class="mt-5 flex items-center gap-2 border-t border-line pt-4">
            <a class="uh-btn-primary uh-btn-sm flex-1" href="{{ route('properties.show', $property->slug) }}">
                {{ __('View details') }}
            </a>
            @if($showShortlist)
                @if($saved)
                    <form method="POST" action="{{ route('shortlist.remove', $property->id) }}" x-data="uhForm" @submit="submit">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="uh-btn-outline uh-btn-sm text-forest" :disabled="submitting"
                                title="{{ __('Remove from shortlist') }}"
                                aria-label="{{ __('Remove :title from shortlist', ['title' => $property->title]) }}">
                            <x-icon name="heart-solid" class="size-4 text-[var(--color-danger)]" x-show="!submitting" />
                            <span class="uh-spinner" x-show="submitting" x-cloak></span>
                            <span class="hidden sm:inline">{{ __('Saved') }}</span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('shortlist.add') }}" x-data="uhForm" @submit="submit">
                        @csrf
                        <input type="hidden" name="property_id" value="{{ $property->id }}">
                        <button type="submit" class="uh-btn-outline uh-btn-sm" :disabled="submitting"
                                title="{{ __('Save to shortlist') }}" aria-label="{{ __('Save :title to shortlist', ['title' => $property->title]) }}">
                            <x-icon name="heart" class="size-4" x-show="!submitting" />
                            <span class="uh-spinner" x-show="submitting" x-cloak></span>
                            <span class="hidden sm:inline">{{ __('Save') }}</span>
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</article>
