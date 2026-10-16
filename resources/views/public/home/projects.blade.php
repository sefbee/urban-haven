<section class="uh-section uh-band-paper uh-home-projects" @if(filled($sectionContent['title'])) aria-labelledby="home-projects-title" @else aria-label="{{ __('Featured developments') }}" @endif>
    <div class="uh-container">
        @if(filled($sectionContent['title']) || filled($sectionContent['subtitle']) || filled($sectionContent['link_label']))
            <header class="uh-home-projects-head" data-reveal>
                @if(filled($sectionContent['title']) || filled($sectionContent['subtitle']))
                <div class="uh-home-projects-copy">
                    @if(filled($sectionContent['title']))
                        <h2 id="home-projects-title" class="uh-h2">{{ $sectionContent['title'] }}</h2>
                    @endif
                    @if(filled($sectionContent['subtitle']))
                        <p class="uh-lede">{{ $sectionContent['subtitle'] }}</p>
                    @endif
                </div>
                @endif
                <a class="uh-home-mobile-section-action" href="{{ route('projects.index') }}">{{ $sectionContent['link_label'] ?: __('View all') }}</a>
                @if(filled($sectionContent['link_label']))
                    <x-ui.pill-link class="uh-home-desktop-section-action" variant="light" :href="route('projects.index')">{{ $sectionContent['link_label'] }}</x-ui.pill-link>
                @endif
            </header>
        @endif

        <div class="uh-home-mobile-rail" x-data="uhRail()">
            <button type="button" class="uh-housing-arrow uh-home-mobile-arrow is-prev" x-show="canPrev" x-cloak
                    @click="prev()" aria-label="{{ __('Previous developments') }}">
                <x-icon name="chevron-left" class="size-4" />
            </button>
            <div class="uh-project-grid uh-home-project-grid uh-home-mobile-track" x-ref="scroller" tabindex="0" aria-label="{{ __('Featured developments') }}">
                @foreach($projects as $project)
                    <div class="uh-home-mobile-item">
                        @include('public.projects.card', ['project' => $project, 'isHomeProjectCard' => true])
                    </div>
                @endforeach
            </div>
            <button type="button" class="uh-housing-arrow uh-home-mobile-arrow is-next" x-show="canNext" x-cloak
                    @click="next()" aria-label="{{ __('Next developments') }}">
                <x-icon name="chevron-right" class="size-4" />
            </button>
        </div>
    </div>
</section>
