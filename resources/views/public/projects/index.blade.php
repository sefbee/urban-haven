@extends('layouts.public')

@php
    $stages = [
        '' => __('All'),
        'available' => __('Available'),
        'ongoing' => __('Under Construction'),
        'upcoming' => __('Upcoming'),
        'completed' => __('Completed'),
    ];
    $currentStage = array_key_exists($stage, $stages) ? $stage : '';
    $completedProjects = $stage === '' ? $completedProjects : collect();
    $featureFirst = $projects->count() > 2;
@endphp

@section('content')
    @include('public.partials.page-head', [
        'title' => __('Urban Haven projects'),
        'lede' => __('Whole developments — buildings and communities we have built, with homes still available inside.'),
        'crumbs' => [
            ['label' => __('Home'), 'url' => route('home')],
            ['label' => __('Projects')],
        ],
    ])

    <div class="uh-container uh-section-tight">
        <nav class="uh-text-toggle uh-stage-filter" aria-label="{{ __('Development stage') }}">
            @foreach($stages as $value => $label)
                <a href="{{ route('projects.index', array_filter(['development_stage' => $value, 'category' => $category ?: null])) }}"
                   @class(['is-on' => $currentStage === $value])
                   @if($currentStage === $value) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        @if($projects->isNotEmpty())
            <div class="uh-project-grid">
                @foreach($projects as $project)
                    <div @class(['md:col-span-2 lg:col-span-3' => $loop->first && $featureFirst])
                         data-reveal style="--uh-i: {{ $loop->index % 3 }}">
                        @include('public.partials.project-card', ['project' => $project, 'feature' => $loop->first && $featureFirst])
                    </div>
                @endforeach
            </div>
        @elseif($completedProjects->isEmpty())
            <x-ui.empty icon="building" :title="__('No projects published yet')"
                        :description="__('Our developments are being prepared for publication. In the meantime, browse the individual homes we have available.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Browse homes') }}</a>
            </x-ui.empty>
        @endif

        @if($completedProjects->isNotEmpty())
            <section @class(['uh-section-tight' => $projects->isNotEmpty()]) aria-labelledby="completed-heading">
                <header class="uh-section-head">
                    <h2 id="completed-heading" class="uh-h2">{{ __('Completed developments') }}</h2>
                </header>
                <div class="uh-project-grid">
                    @foreach($completedProjects as $project)
                        <div data-reveal style="--uh-i: {{ $loop->index % 3 }}">
                            @include('public.partials.project-card', ['project' => $project])
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
