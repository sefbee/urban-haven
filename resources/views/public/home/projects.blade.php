<section class="uh-section uh-band-paper" @if(filled($sectionContent['title'])) aria-labelledby="home-projects-title" @else aria-label="{{ __('Featured developments') }}" @endif>
    <div class="uh-container">
        @if(filled($sectionContent['title']) || filled($sectionContent['subtitle']) || filled($sectionContent['link_label']))
            <header class="uh-rail-head" data-reveal>
                @if(filled($sectionContent['title']) || filled($sectionContent['subtitle']))
                <div>
                    @if(filled($sectionContent['title']))
                        <h2 id="home-projects-title" class="uh-h2">{{ $sectionContent['title'] }}</h2>
                    @endif
                    @if(filled($sectionContent['subtitle']))
                        <p class="uh-lede">{{ $sectionContent['subtitle'] }}</p>
                    @endif
                </div>
                @endif
                @if(filled($sectionContent['link_label']))
                    <x-ui.pill-link variant="light" :href="route('projects.index')">{{ $sectionContent['link_label'] }}</x-ui.pill-link>
                @endif
            </header>
        @endif

        <div class="uh-project-grid uh-home-project-grid">
            @foreach($projects as $project)
                @include('public.projects.card', ['project' => $project])
            @endforeach
        </div>
    </div>
</section>
