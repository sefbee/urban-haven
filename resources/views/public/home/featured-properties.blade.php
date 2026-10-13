<section class="uh-section uh-showcase-section" @if(filled($sectionContent['title'])) aria-labelledby="featured-title" @else aria-label="{{ __('Featured properties') }}" @endif
         x-data="uhBrowse()" @open-preview="openPreview($event.detail)"
         @keydown.escape.window="onEscape()"
         @keydown.left.window="preview && previousSlide()"
         @keydown.right.window="preview && nextSlide()">
    <div class="uh-container">
        @if(filled($sectionContent['title']) || filled($sectionContent['subtitle']))
            <header class="uh-section-head" data-reveal>
                @if(filled($sectionContent['title']))
                    <h2 id="featured-title" class="uh-h2">{{ $sectionContent['title'] }}</h2>
                @endif
                @if(filled($sectionContent['subtitle']))
                    <p class="uh-lede">{{ $sectionContent['subtitle'] }}</p>
                @endif
            </header>
        @endif
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

    @if(filled($sectionContent['link_label']))
        <div class="uh-container uh-section-foot">
            <x-ui.pill-link :href="route('properties.index')">{{ $sectionContent['link_label'] }}</x-ui.pill-link>
        </div>
    @endif

    @include('public.partials.property-preview')
</section>
