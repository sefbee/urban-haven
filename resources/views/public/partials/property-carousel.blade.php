@php
    $images = $property->relationLoaded('media') ? $property->galleryImages()->take(8)->values() : collect();

    if ($property->featured_media_id && $images->isNotEmpty()) {
        $images = $images->sortBy(fn ($image) => $image->id === $property->featured_media_id ? 0 : 1)->values();
    }

    $photoTotal = $images->count();
    $photoSizes = $sizes ?? '(min-width: 1024px) 380px, 100vw';
    $eagerFirst = $eager ?? false;
@endphp

<div class="absolute inset-0" x-data="{ slide: 0, count: {{ $photoTotal }} }">
    @forelse($images as $index => $image)
        <a href="{{ route('properties.show', $property->slug) }}"
           @class(['uh-carousel-slide', 'is-active' => $index === 0]) :class="slide === {{ $index }} ? 'is-active' : ''"
           tabindex="-1" aria-hidden="true">
            <img src="{{ $image->url(768) }}"
                 srcset="{{ $image->url(480) }} 480w, {{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w"
                 sizes="{{ $photoSizes }}"
                 alt=""
                 @if($index > 0 || ! $eagerFirst) loading="lazy" @endif
                 decoding="async">
        </a>
    @empty
        <span class="uh-media-placeholder">{{ $property->locationArea?->name ?? __('Urban Haven') }}</span>
    @endforelse

    @if($photoTotal > 1)
        <button type="button" class="uh-carousel-btn left-0"
                @click.stop.prevent="slide = (slide - 1 + count) % count"
                aria-label="{{ __('Previous photo') }}">
            <x-icon name="chevron-left" class="size-4" />
        </button>
        <button type="button" class="uh-carousel-btn right-0"
                @click.stop.prevent="slide = (slide + 1) % count"
                aria-label="{{ __('Next photo') }}">
            <x-icon name="chevron-right" class="size-4" />
        </button>
    @endif
</div>
