<section class="uh-home-section">
    <div class="uh-container">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <h2 class="uh-home-title">{{ __('Featured Projects') }}</h2>
                <p class="uh-home-lede">{{ __('Developments by Urban Haven, with their current stage and listed units.') }}</p>
            </div>
            <a class="uh-home-pill" href="{{ route('projects.index') }}">{{ __('All projects') }}</a>
        </div>

        <div class="mt-8 space-y-10">
            @foreach($projects as $project)
                @include('public.partials.home-project', ['project' => $project])
            @endforeach
        </div>
    </div>
</section>
