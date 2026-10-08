<section class="uh-section uh-showcase-section" aria-labelledby="featured-title"
         x-data="uhBrowse()" @open-preview="openPreview($event.detail)"
         @keydown.escape.window="onEscape()"
         @keydown.left.window="preview && previousSlide()"
         @keydown.right.window="preview && nextSlide()">
    <div class="uh-container">
        <header class="uh-section-head" data-reveal>
            <h2 id="featured-title" class="uh-h2">{{ __('Featured Real Estate in Bangladesh') }}</h2>
            <p class="uh-lede">{{ __('A closer look at the places our team is featuring right now.') }}</p>
        </header>
    </div>

    <div class="uh-container">
        <div class="uh-showcase" x-data="uhShowcase({{ $featured->count() }})"
             role="region" aria-roledescription="{{ __('carousel') }}" aria-label="{{ __('Featured properties') }}"
             tabindex="0" @keydown.left.prevent="previous()" @keydown.right.prevent="next()"
             @pointerdown="dragStart($event)" @pointerup="dragEnd($event)" @pointercancel="dragging = false"
             data-reveal>
            <div class="uh-showcase-stage" aria-live="polite">
                @foreach($featured as $property)
                    @include('public.partials.showcase-slide', [
                        'property' => $property,
                        'amenityCatalog' => $featuredAmenities,
                        'index' => $loop->index,
                        'total' => $loop->count,
                    ])
                @endforeach

                @if($featured->count() > 1)
                    <button type="button" class="uh-showcase-arrow is-prev" @click.stop="previous()" aria-label="{{ __('Previous property') }}">
                        <x-icon name="chevron-left" class="size-4" />
                    </button>
                    <button type="button" class="uh-showcase-arrow is-next" @click.stop="next()" aria-label="{{ __('Next property') }}">
                        <x-icon name="chevron-right" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </div>

    @include('public.partials.property-preview')
</section>
