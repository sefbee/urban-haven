@php
    $image = $property->featuredImage();
    $photoCount = $property->relationLoaded('media') ? $property->galleryImages()->count() : 0;
    $area = $property->area_value
        ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit)
        : null;
    $onRequest = $property->isPriceOnRequest();
    $compact = $onRequest ? null : \App\Support\MoneyFormatter::compactBdt($property->price);
    $exact = $onRequest ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headline = $property->listing_type !== 'rent' && $compact ? 'BDT '.$compact : $exact;
    $whatsapp = $property->whatsappEnquiryUrl();
    $preview = \App\Support\PropertyPreview::for($property);
@endphp

<article x-data="{ payload: {{ \Illuminate\Support\Js::from($preview) }} }"
         class="flex flex-col gap-5 sm:flex-row sm:items-start">
    <div class="uh-home-tile-photo relative w-full shrink-0 sm:w-72 lg:w-80">
        @include('public.partials.property-carousel', ['sizes' => '(min-width: 1024px) 320px, 100vw'])

        <span class="pointer-events-none absolute inset-x-3 top-3 z-10 flex flex-wrap items-start justify-between gap-2">
            <span class="uh-badge">{{ $property->listing_type === 'rent' ? __('For rent') : __('For sale') }}</span>
            <span class="rounded-full bg-white/90 px-2 py-1 text-[0.6875rem] font-medium tracking-wide text-[#1d1d1f]">#{{ $property->reference ?: $property->id }}</span>
        </span>
        @if($property->availability !== 'available')
            <span class="pointer-events-none absolute bottom-3 left-3 z-10">
                <x-ui.status :status="$property->availability" />
            </span>
        @endif
        <x-save-button :property="$property" class="absolute bottom-3 right-3 z-20 rounded-full bg-white shadow-sm" />
    </div>

    <div class="flex min-w-0 flex-1 flex-col">
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

        <p class="mt-3 text-lg font-semibold tracking-tight">{{ $headline }}</p>
        @if($headline !== $exact)
            <p class="mt-0.5 text-xs text-[var(--color-muted)] uh-numeric">{{ $exact }}</p>
        @endif

        <h2 class="mt-2 text-lg font-semibold tracking-tight">
            <a class="uh-home-link line-clamp-2" href="{{ route('properties.show', $property->slug) }}">{{ $property->title }}</a>
        </h2>

        <p class="mt-1.5 truncate text-sm text-[var(--color-muted)]">
            {{ $property->locationArea?->name }}@if($property->locationArea?->city), {{ $property->locationArea->city }}@endif
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

        <div class="mt-auto">
            @include('public.partials.card-actions', [
                'url' => route('properties.show', $property->slug),
                'title' => $property->title,
                'whatsapp' => $property->isUnavailable() ? null : $whatsapp,
                'trackPropertyId' => $property->id,
                'trackLocation' => 'list',
                'showQuickView' => true,
            ])
        </div>
    </div>
</article>
