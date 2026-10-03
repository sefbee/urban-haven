@extends('layouts.admin')
@section('title', 'Projects')

@section('content')
    <x-ui.page-header compact title="Projects"
                      description="Developments that group several listings together.">
        <x-slot:eyebrow>Inventory</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.projects.create') }}">
                <x-icon name="plus" class="size-4" />
                New project
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.admin-related label="Connected to">
        <a href="{{ route('admin.properties.index') }}">Properties</a>
        @if(auth()->user()?->hasPermission('project.publish'))
            <a href="{{ route('admin.review.index') }}">Review queue</a>
        @endif
        @can('reference.manage')
            <a href="{{ route('admin.amenities.index') }}">Amenities</a>
        @endcan
        @can('settings.update')
            <a href="{{ route('admin.listing-display') }}">Listing display</a>
        @endcan
    </x-ui.admin-related>

    @if($projects->isNotEmpty())
        <div class="uh-panel-flush overflow-hidden">
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
                            <tr class="uh-admin-clickrow">
                                <td class="min-w-56 max-w-72">
                                    <a class="uh-admin-row-main uh-link-quiet font-medium" href="{{ route('admin.projects.edit', $project) }}">{{ $project->name }}</a>
                                    <span class="mt-0.5 block text-xs text-[var(--color-muted)]">{{ $project->city ?? '—' }}</span>
                                </td>
                                <td><x-ui.status :status="$project->development_stage" /></td>
                                <td><x-ui.status :status="$project->editorialStatus()" /></td>
                                <td class="uh-admin-row-actions">
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
        <x-ui.empty icon="building" title="No projects yet"
                    description="Group listings under a project when you sell several homes in one development.">
            <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.projects.create') }}">New project</a>
        </x-ui.empty>
    @endif
@endsection
