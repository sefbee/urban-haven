<section class="uh-hero-flat text-cream">
    <div class="uh-container flex flex-col items-start gap-7 py-14 md:flex-row md:items-center md:justify-between md:py-16">
        <div class="max-w-xl">
            <h2 class="uh-h2 text-cream">{{ __('Not sure where to start?') }}</h2>
            <p class="mt-3 text-cream/80">{{ __('Tell us your budget, preferred area and timeline. Our sales team will shortlist suitable properties for you.') }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a class="uh-btn-gold uh-btn-lg" href="{{ route('cms.show', 'contact') }}" data-track="home_cta_click" data-track-cta="contact">{{ __('Tell us what you need') }}</a>
            <a class="uh-btn-ondark uh-btn-lg" href="{{ route('properties.index') }}">{{ __('Search Properties') }}</a>
        </div>
    </div>
</section>
