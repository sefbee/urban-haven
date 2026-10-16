<section class="uh-section uh-showcase-section" @if(filled($sectionContent['title'])) aria-labelledby="featured-title" @else aria-label="{{ __('Featured properties') }}" @endif
         x-data="uhBrowse()" @open-preview="openPreview($event.detail)"
         @keydown.escape.window="onEscape()"
         @keydown.left.window="preview && previousSlide()"
         @keydown.right.window="preview && nextSlide()">
    <div class="uh-container">
        @if(filled($sectionContent['title']) || filled($sectionContent['subtitle']) || filled($sectionContent['link_label']))
            <header class="uh-section-head" data-reveal>
                @if(filled($sectionContent['title']))
                    <h2 id="featured-title" class="uh-h2">{{ $sectionContent['title'] }}</h2>
                @endif
                @if(filled($sectionContent['subtitle']))
                    <p class="uh-lede">{{ $sectionContent['subtitle'] }}</p>
                @endif
                <a href="{{ route('properties.index') }}" class="uh-home-mobile-section-action uh-showcase-mobile-section-action">
                    {{ $sectionContent['link_label'] ?: __('View all') }}
                </a>
            </header>
        @endif
    </div>

    <div class="uh-container">
        <div class="uh-showcase" x-data="uhShowcase({{ $featured->count() }})"
             role="region" aria-roledescription="{{ __('carousel') }}" aria-label="{{ __('Featured properties') }}"
             tabindex="0" @keydown.left.prevent="previous()" @keydown.right.prevent="next()"
             @click.capture="preventGalleryClick($event)"
             @pointerdown="dragStart($event)" @pointerup="dragEnd($event)" @pointercancel="dragging = false; draggingGallery = false"
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

                <button type="button" class="uh-showcase-arrow is-prev"
                        x-show="isMobileViewport() ? photoCount(active) > 1 : count > 1" x-cloak
                        @click.stop="isMobileViewport() ? previousPhoto() : previous()"
                        :aria-label="isMobileViewport() ? @js(__('Previous photo')) : @js(__('Previous property'))">
                        <x-icon name="chevron-left" class="size-4" />
                </button>
                <button type="button" class="uh-showcase-arrow is-next"
                        x-show="isMobileViewport() ? photoCount(active) > 1 : count > 1" x-cloak
                        @click.stop="isMobileViewport() ? nextPhoto() : next()"
                        :aria-label="isMobileViewport() ? @js(__('Next photo')) : @js(__('Next property'))">
                        <x-icon name="chevron-right" class="size-4" />
                </button>
            </div>
            @if($featured->count() > 1)
                <nav class="uh-showcase-pagination" aria-label="{{ __('Featured property pages') }}">
                    @foreach($featured as $property)
                        <button type="button" @click="go({{ $loop->index }})"
                                :class="{ 'is-on': active === {{ $loop->index }} }"
                                :aria-current="active === {{ $loop->index }} ? 'true' : 'false'"
                                aria-label="{{ __('Show featured property :number', ['number' => $loop->iteration]) }}"></button>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>

    @if(filled($sectionContent['link_label']))
        <div class="uh-container uh-section-foot uh-showcase-desktop-section-action">
            <x-ui.pill-link :href="route('properties.index')">{{ $sectionContent['link_label'] }}</x-ui.pill-link>
        </div>
    @endif

    @include('public.partials.property-preview')
</section>
