<section class="uh-section border-t border-line bg-paper">
    <div class="uh-container">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="max-w-2xl">
                <h2 class="uh-h2">{{ __('Featured Projects') }}</h2>
                <p class="uh-lede mt-3">{{ __('Developments by Urban Haven, with their current stage and listed units.') }}</p>
            </div>
            <a class="uh-link-quiet inline-flex items-center gap-1.5 text-sm" href="{{ route('projects.index') }}">
                {{ __('All projects') }}
                <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>

        <div class="uh-home-scroll mt-8">
            @foreach($projects as $project)
                @include('public.partials.project-card', ['project' => $project])
            @endforeach
        </div>
    </div>
</section>
