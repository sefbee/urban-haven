<section class="uh-section uh-home-requirements-section" aria-labelledby="requirements-cta-title">
    <div class="uh-container">
        <div class="uh-home-requirements" data-reveal>
            <div class="uh-home-requirements-copy">
                <p class="uh-home-requirements-kicker">{{ __('Personal property search') }}</p>
                <h2 id="requirements-cta-title" class="uh-h2">{{ __('Can’t find what you’re looking for?') }}</h2>
                <p>{{ __('Tell us your preferred area, budget and needs. Our team will help you find a property that fits.') }}</p>
                <x-ui.pill-link variant="light" class="mt-5" :href="route('cms.show', 'contact')">
                    {{ __('Tell us what you need') }}
                </x-ui.pill-link>
            </div>
            <div class="uh-home-requirements-art" aria-hidden="true">
                <span class="uh-home-requirements-art-window">
                    <x-icon name="home" class="size-14" />
                </span>
                <span class="uh-home-requirements-art-search">
                    <x-icon name="search" class="size-7" />
                </span>
                <span class="uh-home-requirements-art-dot is-one"></span>
                <span class="uh-home-requirements-art-dot is-two"></span>
            </div>
        </div>
    </div>
</section>
