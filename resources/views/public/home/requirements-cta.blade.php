@if(filled($sectionContent['eyebrow']) || filled($sectionContent['title']) || filled($sectionContent['body']) || (filled($sectionContent['cta_label']) && filled($sectionContent['cta_url'])))
<section class="uh-section uh-home-requirements-section" aria-label="{{ filled($sectionContent['title']) ? $sectionContent['title'] : __('Property search callout') }}">
    <div class="uh-container">
        <div class="uh-home-requirements" data-reveal>
            <div class="uh-home-requirements-copy">
                @if(filled($sectionContent['eyebrow']))
                    <p class="uh-home-requirements-kicker">{{ $sectionContent['eyebrow'] }}</p>
                @endif
                @if(filled($sectionContent['title']))
                    <h2 id="requirements-cta-title" class="uh-h2">{{ $sectionContent['title'] }}</h2>
                @endif
                @if(filled($sectionContent['body']))
                    <p>{{ $sectionContent['body'] }}</p>
                @endif
                @if(filled($sectionContent['cta_label']) && filled($sectionContent['cta_url']))
                    <x-ui.pill-link variant="light" :mobile-label="true" class="mt-5" :href="$sectionContent['cta_url']">
                        {{ $sectionContent['cta_label'] }}
                    </x-ui.pill-link>
                @endif
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
@endif
