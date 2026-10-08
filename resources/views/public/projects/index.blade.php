@extends('layouts.public')

@section('content')
    <header class="uh-page-head">
        <div class="uh-container">
            <h1 class="uh-h1 uh-page-title">{{ __('Property projects') }}</h1>
            <p class="uh-page-lede">{{ __('Explore current and upcoming property developments from Urban Haven.') }}</p>
        </div>
    </header>

    <div class="uh-container uh-section-tight pt-0">
        @if($projects->isNotEmpty())
            <div class="uh-project-grid">
                @foreach($projects as $project)
                    @include('public.projects.card', ['project' => $project])
                @endforeach
            </div>
            @if($projects->hasPages())
                <div class="uh-pagination-wrap">{{ $projects->onEachSide(1)->links() }}</div>
            @endif
        @else
            <x-ui.empty icon="building" :title="__('No projects published yet')"
                        :description="__('New developments from our team will appear here.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Browse properties') }}</a>
            </x-ui.empty>
        @endif
    </div>
@endsection
