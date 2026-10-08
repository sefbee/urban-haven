<section class="uh-section uh-band-paper" aria-labelledby="home-projects-title">
    <div class="uh-container">
        <header class="uh-rail-head" data-reveal>
            <div>
                <h2 id="home-projects-title" class="uh-h2">{{ $sectionContent['title'] }}</h2>
                @if(filled($sectionContent['subtitle']))
                    <p class="uh-lede">{{ $sectionContent['subtitle'] }}</p>
                @endif
            </div>
            <x-ui.pill-link variant="light" :href="route('projects.index')">{{ $sectionContent['link_label'] }}</x-ui.pill-link>
        </header>

        <div class="uh-project-grid">
            @foreach($projects as $project)
                @include('public.projects.card', ['project' => $project])
            @endforeach
        </div>
    </div>
</section>
