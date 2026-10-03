@extends('layouts.admin')
@section('title', 'Review queue')

@section('content')
    <x-ui.page-header compact title="Review queue" description="Listings and projects submitted by editors. Approve and publish, or return them with a note.">
        <x-slot:eyebrow>Inventory</x-slot:eyebrow>
    </x-ui.page-header>

    <x-ui.admin-related label="After you decide">
        <a href="{{ route('admin.properties.index') }}">Properties</a>
        <a href="{{ route('admin.projects.index') }}">Projects</a>
    </x-ui.admin-related>

    @foreach(['properties' => 'Properties', 'projects' => 'Projects'] as $key => $heading)
        @php($items = $$key)
        <section @class(['mt-6' => ! $loop->first]) aria-labelledby="review-{{ $key }}">
            <h2 id="review-{{ $key }}" class="uh-h4">{{ $heading }} <span class="uh-numeric text-[var(--color-muted)]">({{ $items->count() }})</span></h2>
            @if($items->isNotEmpty())
                <div class="uh-panel-flush mt-3 overflow-hidden">
                    <div class="uh-table-scroll">
                        <table class="uh-table">
                            <caption class="sr-only">{{ $heading }} awaiting review</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Title</th>
                                    <th scope="col">State</th>
                                    <th scope="col">Submitted by</th>
                                    <th scope="col">Submitted</th>
                                    <th scope="col"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    @php($editRoute = $key === 'properties' ? route('admin.properties.edit', $item) : route('admin.projects.edit', $item))
                                    <tr class="uh-admin-clickrow">
                                        <td class="min-w-56">
                                            <a class="uh-admin-row-main uh-link-quiet font-medium" href="{{ $editRoute }}">{{ $item->title ?? $item->name }}</a>
                                            @if($key === 'properties')
                                                <span class="mt-0.5 block text-xs text-[var(--color-muted)]">{{ $item->reference ?? '—' }} · {{ $item->locationArea?->name ?? '—' }}</span>
                                            @endif
                                        </td>
                                        <td><x-ui.status :status="$item->editorialStatus()" /></td>
                                        <td class="whitespace-nowrap text-sm">{{ $item->publicationState?->submitter?->name ?? '—' }}</td>
                                        <td class="whitespace-nowrap text-xs text-[var(--color-muted)]">{{ $item->publicationState?->submitted_at ? \App\Support\DisplayTimezone::format($item->publicationState->submitted_at) : '—' }}</td>
                                        <td class="uh-admin-row-actions"><a class="uh-btn-outline uh-btn-sm" href="{{ $editRoute }}">Review</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="mt-3 text-sm text-[var(--color-muted)]">Nothing waiting.</p>
            @endif
        </section>
    @endforeach
@endsection
