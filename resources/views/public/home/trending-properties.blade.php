<section class="uh-section uh-band-paper" aria-labelledby="trending-title">
    <div class="uh-container">
        <header class="uh-section-head" data-reveal>
            <h2 id="trending-title" class="uh-h2">{{ __('Trending Properties') }}</h2>
            <p class="uh-lede">{{ __('The listings people are looking at right now.') }}</p>
        </header>

        <div class="uh-grid-cards">
            @foreach($trending as $property)
                @include('public.partials.property-card', ['property' => $property, 'revealIndex' => $loop->index % 3])
            @endforeach
        </div>

        <div class="uh-section-foot">
            <x-ui.pill-link :href="route('properties.index')">{{ __('See every property') }}</x-ui.pill-link>
        </div>
    </div>
</section>
