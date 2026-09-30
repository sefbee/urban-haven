@php
    $image = $property->featuredImage();
    $photoCount = $property->relationLoaded('media') ? $property->media->count() : 0;
    $saved = in_array($property->id, array_map('intval', session('shortlist', [])), true);
    $area = $property->area_value
        ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit)
        : null;
    $compact = \App\Support\MoneyFormatter::compactBdt($property->price);
    $exact = \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headline = $property->listing_type !== 'rent' && $compact ? 'BDT '.$compact : $exact;
    $whatsapp = $property->whatsappEnquiryUrl();
    $preview = \App\Support\PropertyPreview::for($property);
@endphp

<article x-data="{ payload: {{ \Illuminate\Support\Js::from($preview) }} }"
         class="uh-card flex flex-col"
         :class="layout === 'list' ? 'sm:flex-row' : ''">
    <button type="button"
            class="uh-media group relative block w-full shrink-0 text-left"
            :class="layout === 'list' ? 'aspect-16/10 sm:aspect-auto sm:min-h-48 sm:w-72 lg:w-80' : 'aspect-4/3'"
            @click="openPreview(payload)"
            aria-label="{{ __('Quick view of :title', ['title' => $property->title]) }}">
        @if($image)
            <img src="{{ $image->url(768) }}"
                 srcset="{{ $image->url(480) }} 480w, {{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w"
                 sizes="(min-width: 1024px) 320px, 100vw"
                 alt=""
                 class="size-full object-cover"
                 :class="layout === 'list' ? 'sm:absolute sm:inset-0' : ''"
                 loading="lazy" decoding="async">
        @else
            <span class="uh-media-placeholder" :class="layout === 'list' ? 'sm:absolute sm:inset-0' : ''">
                <span class="flex items-center gap-2 text-sm font-medium">
                    <x-icon name="image" class="size-4 opacity-70" />
                    {{ $property->locationArea?->name ?? __('Urban Haven') }}
                </span>
            </span>
        @endif

        <span class="absolute inset-x-3 top-3 flex flex-wrap items-start justify-between gap-2">
            <span class="uh-badge uh-badge-dark">{{ $property->listing_type === 'rent' ? __('For rent') : __('For sale') }}</span>
            @if($property->availability !== 'available')
                <x-ui.status :status="$property->availability" />
            @endif
        </span>

        @if($photoCount > 1)
            <span class="absolute bottom-3 left-3 rounded-md bg-ink/80 px-2 py-1 text-xs font-semibold text-cream">
                <span class="uh-numeric">1/{{ $photoCount }}</span>
            </span>
        @endif
    </button>

    <div class="flex min-w-0 flex-1 flex-col p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                @if($property->propertyType)
                    <x-ui.badge>{{ $property->propertyType->label }}</x-ui.badge>
                @endif
                @if($property->is_furnished)
                    <x-ui.badge tone="outline">{{ __('Furnished') }}</x-ui.badge>
                @endif
            </div>
            @if($property->reference)
                <p class="shrink-0 text-xs font-medium text-[var(--color-muted)]"># <span class="uh-numeric">{{ $property->reference }}</span></p>
            @endif
        </div>

        <p class="uh-price mt-3 text-forest">{{ $headline }}</p>
        @if($headline !== $exact)
            <p class="mt-0.5 text-xs text-[var(--color-muted)] uh-numeric">{{ $exact }}</p>
        @endif

        <h2 class="uh-h3 mt-2">
            <a class="line-clamp-2 transition hover:text-forest" href="{{ route('properties.show', $property->slug) }}">{{ $property->title }}</a>
        </h2>

        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-[var(--color-muted)]">
            <x-icon name="pin" class="size-3.5 shrink-0 text-[var(--color-gold-ink)]" />
            <span class="truncate">{{ $property->locationArea?->name }}@if($property->locationArea?->city), {{ $property->locationArea->city }}@endif</span>
        </p>

        <ul class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm text-[var(--color-muted)]">
            @if($property->bedrooms)
                <li class="flex items-center gap-1.5"><x-icon name="bed" class="size-4 shrink-0" /><span class="uh-numeric">{{ $property->bedrooms }}</span> {{ __('bed') }}</li>
            @endif
            @if($property->bathrooms)
                <li class="flex items-center gap-1.5"><x-icon name="bath" class="size-4 shrink-0" /><span class="uh-numeric">{{ $property->bathrooms }}</span> {{ __('bath') }}</li>
            @endif
            @if($area)
                <li class="flex items-center gap-1.5"><x-icon name="area" class="size-4 shrink-0" /><span class="uh-numeric">{{ $area }}</span></li>
            @endif
            @if($property->floor_number)
                <li class="flex items-center gap-1.5"><x-icon name="floor" class="size-4 shrink-0" />{{ __('Floor :number', ['number' => $property->floor_number]) }}</li>
            @endif
        </ul>

        @if($property->last_updated_at)
            <p class="mt-2 text-xs text-[var(--color-muted)]">{{ __('Updated :time', ['time' => $property->last_updated_at->diffForHumans()]) }}</p>
        @endif

        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-line pt-4">
            <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.show', $property->slug) }}">{{ __('View details') }}</a>
            @if($whatsapp)
                <a class="uh-btn-whatsapp uh-btn-sm" href="{{ $whatsapp }}" rel="noopener">
                    <x-icon name="whatsapp" class="size-4" />
                    {{ __('WhatsApp') }}
                </a>
            @endif
            <button type="button" class="uh-btn-outline uh-btn-sm" @click="openPreview(payload)">
                <x-icon name="expand" class="size-4" />
                {{ __('Quick view') }}
            </button>
            @if($saved)
                <form method="POST" action="{{ route('shortlist.remove', $property->id) }}" x-data="uhForm" @submit="submit">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="uh-icon-btn size-9 text-forest" :disabled="submitting"
                            aria-label="{{ __('Remove :title from shortlist', ['title' => $property->title]) }}">
                        <x-icon name="heart-solid" class="size-4 text-[var(--color-danger)]" x-show="!submitting" />
                        <span class="uh-spinner" x-show="submitting" x-cloak></span>
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('shortlist.add') }}" x-data="uhForm" @submit="submit">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                    <button type="submit" class="uh-icon-btn size-9" :disabled="submitting"
                            aria-label="{{ __('Save :title to shortlist', ['title' => $property->title]) }}">
                        <x-icon name="heart" class="size-4" x-show="!submitting" />
                        <span class="uh-spinner" x-show="submitting" x-cloak></span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</article>
