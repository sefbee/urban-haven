<section class="uh-home-cta">
    <div class="uh-container max-w-3xl text-center">
        <h2 class="uh-home-title">{{ __('Not sure where to start?') }}</h2>
        <p class="uh-home-lede mx-auto">{{ __('Tell us your budget, preferred area and timeline. Our sales team will shortlist suitable properties for you.') }}</p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
            <a class="uh-search-submit" href="{{ route('cms.show', 'contact') }}" data-track="home_cta_click" data-track-cta="contact">{{ __('Tell us what you need') }}</a>
            <a class="uh-home-link text-sm" href="{{ route('properties.index') }}">{{ __('Search Properties') }}</a>
        </div>
    </div>
</section>
