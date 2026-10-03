@php
    $images = $property->relationLoaded('media') ? $property->galleryImages()->take(8)->values() : collect();

    if ($property->featured_media_id && $images->isNotEmpty()) {
        $images = $images->sortBy(fn ($image) => $image->id === $property->featured_media_id ? 0 : 1)->values();
    }

    $photoTotal = $images->count();
    $photoSizes = $sizes ?? '(min-width: 1024px) 380px, 100vw';
@endphp

<div class="absolute inset-0" x-data="{ slide: 0, count: {{ $photoTotal }} }">
    @forelse($images as $index => $image)
        <a href="{{ route('properties.show', $property->slug) }}"
           x-show="slide === {{ $index }}" @if($index) x-cloak @endif
           class="absolute inset-0 block"
           tabindex="-1" aria-hidden="true">
            <img src="{{ $image->url(768) }}"
                 srcset="{{ $image->url(480) }} 480w, {{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w"
                 sizes="{{ $photoSizes }}"
                 alt=""
                 class="size-full object-cover"
                 @if($index === 0) @else loading="lazy" @endif
                 decoding="async">
        </a>
    @empty
        <span class="uh-media-placeholder absolute inset-0">
            <span class="flex items-center gap-2 text-sm font-medium">
                <x-icon name="image" class="size-4 opacity-70" />
                {{ $property->locationArea?->name ?? __('Urban Haven') }}
            </span>
        </span>
    @endforelse

    @if($photoTotal > 1)
        <button type="button"
                class="absolute left-2 top-1/2 z-10 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-white text-[#1d1d1f] shadow-sm"
                @click.stop.prevent="slide = (slide - 1 + count) % count"
                aria-label="{{ __('Previous photo') }}">
            <x-icon name="chevron-left" class="size-5" />
        </button>
        <button type="button"
                class="absolute right-2 top-1/2 z-10 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-white text-[#1d1d1f] shadow-sm"
                @click.stop.prevent="slide = (slide + 1) % count"
                aria-label="{{ __('Next photo') }}">
            <x-icon name="chevron-right" class="size-5" />
        </button>
        <span class="pointer-events-none absolute bottom-3 left-3 z-10 rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-[#1d1d1f]">
            <span class="uh-numeric" x-text="(slide + 1) + '/' + count">1/{{ $photoTotal }}</span>
        </span>
    @endif
</div>
