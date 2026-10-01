<section class="uh-hero-flat text-cream">
    <div class="uh-container flex flex-col items-start gap-7 py-14 md:flex-row md:items-center md:justify-between md:py-16">
        <div class="max-w-xl">
            <h2 class="uh-h2 text-cream">{{ __('Looking for the right property?') }}</h2>
            <p class="mt-3 text-cream/80">{{ __('Tell us what you are looking for and let Urban Haven help you find it.') }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a class="uh-btn-gold uh-btn-lg" href="{{ route('properties.index') }}">{{ __('Find a Property') }}</a>
            <a class="uh-btn-ondark uh-btn-lg" href="{{ route('cms.show', 'contact') }}">{{ __('Contact Urban Haven') }}</a>
        </div>
    </div>
</section>
