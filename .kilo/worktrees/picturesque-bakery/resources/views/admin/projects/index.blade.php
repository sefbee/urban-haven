@extends('layouts.admin')
@section('title', 'Projects')

@section('content')
    <x-ui.page-header compact title="Projects"
                      description="Developments that group several listings together.">
        <x-slot:actions>
            <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.projects.create') }}">
                <x-icon name="plus" class="size-4" />
                New project
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if($projects->isNotEmpty())
        <div class="uh-panel-flush mt-6 overflow-hidden">
            <div class="uh-table-scroll">
                <table class="uh-table">
                    <caption class="sr-only">Development projects</caption>
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Stage</th>
                            <th scope="col">Editorial</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                            <tr>
                                <td class="min-w-56 max-w-72">
                                    <a class="uh-link-quiet font-medium" href="{{ route('admin.projects.edit', $project) }}">{{ $project->name }}</a>
                                    <span class="mt-0.5 block text-xs text-[var(--color-muted)]">{{ $project->city ?? '—' }}</span>
                                </td>
                                <td><x-ui.status :status="$project->development_stage" /></td>
                                <td><x-ui.status :status="$project->editorialStatus()" /></td>
                                <td class="whitespace-nowrap text-right">
                                    <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.projects.edit', $project) }}">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($projects->hasPages())
            <div class="mt-6">{{ $projects->links() }}</div>
        @endif
    @else
        <x-ui.empty class="mt-6" icon="building" title="No projects yet"
                    description="Group listings under a project when you sell several homes in one development.">
            <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.projects.create') }}">New project</a>
        </x-ui.empty>
    @endif
@endsection
