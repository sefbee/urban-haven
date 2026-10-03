@php
    $count = $properties->count();
@endphp

<div class="uh-featured-stage"
     data-uh-featured
     x-data="uhFeaturedSlider({{ $count }})"
     @uh-featured-sync="sync()"
     @keydown="onKey($event)"
     tabindex="0"
     aria-roledescription="carousel"
     aria-label="{{ __('Featured Properties') }}">
    <div class="uh-featured-scroller" data-count="{{ $count }}" x-ref="scroller" @scroll.passive="onScroll()">
        @if($count > 1)
            @include('public.partials.featured-slide', [
                'property' => $properties->last(),
                'slideIndex' => $count - 1,
                'clone' => true,
            ])
        @endif
        @foreach($properties as $property)
            @include('public.partials.featured-slide', ['property' => $property, 'slideIndex' => $loop->index])
        @endforeach
        @if($count > 1)
            @include('public.partials.featured-slide', [
                'property' => $properties->first(),
                'slideIndex' => 0,
                'clone' => true,
            ])
        @endif
    </div>

    @if($count > 1)
        <button type="button" class="uh-featured-nav uh-featured-nav-prev" @click="previous()" aria-label="{{ __('Previous listing') }}">
            <x-icon name="chevron-left" class="size-5" />
        </button>
        <button type="button" class="uh-featured-nav uh-featured-nav-next" @click="next()" aria-label="{{ __('Next listing') }}">
            <x-icon name="chevron-right" class="size-5" />
        </button>
        <div class="uh-featured-dots" role="tablist" aria-label="{{ __('Featured listings') }}">
            @foreach($properties as $index => $property)
                <button type="button" class="uh-featured-dot"
                        :class="index === {{ $index }} ? 'is-on' : ''"
                        :aria-current="index === {{ $index }} ? 'true' : null"
                        @click="go({{ $index }})"
                        aria-label="{{ __('Show :title', ['title' => $property->title]) }}"></button>
            @endforeach
        </div>
    @endif
</div>
