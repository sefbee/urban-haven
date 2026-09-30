@extends('layouts.public')

@section('content')
    <div class="bg-ink text-cream">
        <div class="uh-container py-12 md:py-16">
            <x-ui.breadcrumbs on-dark :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Projects')],
            ]" />
            <p class="uh-eyebrow-light mt-4">{{ __('Developments') }}</p>
            <h1 class="uh-display mt-3 text-cream">{{ __('Urban Haven projects') }}</h1>
            <p class="uh-lede mt-4 max-w-2xl text-cream/70">
                {{ __('A project is a whole development — a building or community we have built, with several homes inside it. Open one to see the homes still available.') }}
            </p>
        </div>
    </div>

    <div class="uh-container uh-section-tight">
        @if($projects->isNotEmpty())
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($projects as $project)
                    @include('public.partials.project-card', ['project' => $project])
                @endforeach
            </div>
        @else
            <x-ui.empty icon="building" :title="__('No projects published yet')"
                        :description="__('Our developments are being prepared for publication. In the meantime, browse the individual homes we have available.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Browse homes') }}</a>
            </x-ui.empty>
        @endif
    </div>
@endsection
