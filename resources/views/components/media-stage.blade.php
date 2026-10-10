@props([
    'title',
    'photoCount' => 0,
    'video' => null,
    'tour' => null,
    'coordinates' => null,
])

@php
    $tourUrl = is_string($tour) && str_starts_with($tour, 'https://') ? $tour : null;
    $views = array_filter([
        'photos' => ['icon' => 'image', 'label' => __('Photo')],
        'video' => $video ? ['icon' => 'play', 'label' => __('Video')] : null,
        'map' => $coordinates ? ['icon' => 'map', 'label' => __('Map')] : null,
        'tour' => $tourUrl ? ['icon' => 'globe', 'label' => __('360° tour')] : null,
    ]);
@endphp

<div {{ $attributes->class(['uh-stage', 'has-switch' => count($views) > 1]) }} x-data="{ view: 'photos' }">
    @if(count($views) > 1)
        <div class="uh-stage-switch" role="group" aria-label="{{ __('Media') }}">
            @foreach($views as $key => $item)
                <button type="button"
                        :class="{ 'is-on': view === '{{ $key }}' }" :aria-pressed="(view === '{{ $key }}').toString()"
                        @if($key === 'map')
                            @click="view = 'map'; $nextTick(() => window.dispatchEvent(new CustomEvent('uh:refresh-maps')))"
                        @else
                            @click="view = '{{ $key }}'"
                        @endif
                        @class(['uh-stage-tab', 'is-on' => $loop->first, 'is-available' => $key === 'video'])>
                    <x-icon :name="$item['icon']" class="size-4" />
                    {{ $item['label'] }}
                </button>
            @endforeach
        </div>
    @endif

    <div class="uh-stage-view" x-show="view === 'photos'">
        {{ $slot }}
    </div>

    @if($video)
        <div class="uh-stage-view uh-stage-panel is-dark" x-show="view === 'video'" x-cloak>
            <template x-if="view === 'video'">
                <iframe src="{{ $video }}" title="{{ __('Video of :title', ['title' => $title]) }}"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </template>
        </div>
    @endif

    @if($coordinates)
        <div class="uh-stage-view uh-stage-panel" x-show="view === 'map'" x-cloak>
            <div class="uh-stage-map" role="region" aria-label="{{ __('Map of :title', ['title' => $title]) }}"
                 data-uh-map data-lat="{{ $coordinates['lat'] }}" data-lng="{{ $coordinates['lng'] }}"
                 data-zoom="{{ $coordinates['approximate'] ? 14 : 16 }}"
                 data-pin="{{ $coordinates['approximate'] ? 'false' : 'true' }}"
                 data-approximate="{{ $coordinates['approximate'] ? 'true' : 'false' }}"
                 data-tiles="{{ config('urbanhaven.maps.tile_url') }}" data-attribution="{{ config('urbanhaven.maps.attribution') }}"></div>
            @if($coordinates['approximate'])
                <p class="uh-stage-note">
                    <x-icon name="pin" class="size-3.5" />
                    {{ __('Approximate neighbourhood') }}
                </p>
            @endif
        </div>
    @endif

    @if($tourUrl)
        <div class="uh-stage-view uh-stage-panel is-dark" x-show="view === 'tour'" x-cloak>
            <template x-if="view === 'tour'">
                <iframe src="{{ $tourUrl }}" title="{{ __('Virtual tour of :title', ['title' => $title]) }}"
                        allow="accelerometer; fullscreen; gyroscope; xr-spatial-tracking"
                        allowfullscreen
                        referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </template>
            <a class="uh-stage-note is-link" href="{{ $tourUrl }}" target="_blank" rel="noopener">
                <x-icon name="external" class="size-3.5" />
                {{ __('Open full screen') }}
            </a>
        </div>
    @endif

</div>
