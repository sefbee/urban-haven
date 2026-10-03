@extends('layouts.public')

@section('content')
    @include('public.partials.page-head', [
        'title' => __('Urban Haven projects'),
        'lede' => __('A project is a whole development — a building or community we have built, with several homes inside it. Open one to see the homes still available.'),
        'crumbs' => [
            ['label' => __('Home'), 'url' => route('home')],
            ['label' => __('Projects')],
        ],
    ])

    <div class="uh-container uh-section-tight">
        @if($projects->isNotEmpty())
            <div class="grid gap-x-6 gap-y-10 md:grid-cols-2 lg:grid-cols-3">
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
