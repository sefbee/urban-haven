<section class="uh-section uh-home-housing-section uh-highlighted-property-section uh-trending-section"
         x-data="uhBrowse()" @open-preview="openPreview($event.detail)"
         @keydown.escape.window="onEscape()"
         @keydown.left.window="preview && previousSlide()"
         @keydown.right.window="preview && nextSlide()"
         aria-labelledby="housing-projects-title">
    <div class="uh-container">
        <header class="uh-trending-head" data-reveal>
            <h2 id="housing-projects-title" class="uh-h2">{{ $sectionContent['title'] }}</h2>
            <x-ui.pill-link variant="dark" :href="route('properties.index', ['category' => \App\Models\PropertyType::CATEGORY_RESIDENTIAL])">
                {{ $sectionContent['link_label'] }}
            </x-ui.pill-link>
        </header>

        <div class="uh-housing-rail" x-data="uhRail()">
            <button type="button" class="uh-housing-arrow is-prev" x-show="canPrev" x-cloak
                    @click="prev()" aria-label="{{ __('Previous housing listings') }}">
                <x-icon name="chevron-left" class="size-4" />
            </button>
            <div class="uh-housing-track" x-ref="scroller" tabindex="0" aria-label="{{ __('Homes and apartments') }}">
            @foreach($housingListings as $property)
                <div class="uh-housing-item" data-reveal style="--uh-i: {{ $loop->index % 4 }}">
                    @include('public.partials.property-card', [
                        'property' => $property,
                        'revealIndex' => $loop->index % 4,
                        'quickView' => true,
                        'highlight' => true,
                    ])
                </div>
            @endforeach
            </div>
            <button type="button" class="uh-housing-arrow is-next" x-show="canNext" x-cloak
                    @click="next()" aria-label="{{ __('Next housing listings') }}">
                <x-icon name="chevron-right" class="size-4" />
            </button>
        </div>
    </div>

    @include('public.partials.property-preview')
</section>
