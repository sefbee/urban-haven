@extends('layouts.admin')
@section('title', 'Properties')

@section('content')
    <x-ui.page-header compact title="Properties">
        <x-slot:eyebrow>Content library</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-primary uh-btn-sm uh-admin-add-action" href="{{ route('admin.properties.create') }}">
                <x-icon name="plus" class="size-4" />
                New property
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.admin-related label="Connected to">
        <a href="{{ route('admin.projects.index') }}">Projects</a>
        @can('reference.manage')
            <a href="{{ route('admin.areas.index') }}">Areas</a>
            <a href="{{ route('admin.property-types.index') }}">Property types</a>
            <a href="{{ route('admin.amenities.index') }}">Amenities</a>
        @endcan
        @can('settings.update')
            <a href="{{ route('admin.listing-display') }}">Listing display</a>
        @endcan
    </x-ui.admin-related>

    <a class="uh-admin-add-fab" href="{{ route('admin.properties.create') }}" aria-label="New property" title="New property">
        <x-icon name="plus" class="size-5" />
    </a>

    @if($properties->isNotEmpty())
        <div class="uh-admin-property-cards lg:hidden">
            @foreach($properties as $property)
                <article class="uh-admin-property-card">
                    <div class="uh-admin-property-card-heading">
                        <div class="min-w-0">
                            <a class="uh-admin-row-main uh-link-quiet" href="{{ route('admin.properties.edit', $property) }}">{{ $property->title }}</a>
                            <p>{{ $property->locationArea?->name ?? '—' }} · {{ $property->listing_type === 'rent' ? 'Rent' : 'Sale' }}</p>
                        </div>
                        <span class="uh-admin-property-price uh-numeric">{{ \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis) ?? '—' }}</span>
                    </div>
                    <dl class="uh-admin-property-card-meta">
                        <div><dt>Reference</dt><dd class="uh-numeric">{{ $property->reference ?? '—' }}</dd></div>
                        <div><dt>Editorial</dt><dd><x-ui.status :status="$property->editorialStatus()" /></dd></div>
                        <div><dt>Availability</dt><dd><x-ui.status :status="$property->availability" /></dd></div>
                    </dl>
                    <a class="uh-btn-outline uh-btn-sm uh-btn-block" href="{{ route('admin.properties.edit', $property) }}">Edit property</a>
                </article>
            @endforeach
        </div>

        <div class="uh-panel-flush overflow-hidden hidden lg:block">
            <div class="uh-table-scroll">
                <table class="uh-table">
                    <caption class="sr-only">Property listings</caption>
                    <thead>
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Reference</th>
                            <th scope="col">Editorial</th>
                            <th scope="col">Availability</th>
                            <th scope="col">Price</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($properties as $property)
                            <tr class="uh-admin-clickrow">
                                <td class="min-w-56 max-w-72">
                                    <a class="uh-admin-row-main uh-link-quiet font-medium" href="{{ route('admin.properties.edit', $property) }}">{{ $property->title }}</a>
                                    <span class="mt-0.5 block text-xs text-[var(--color-muted)]">
                                        {{ $property->locationArea?->name ?? '—' }} · {{ $property->listing_type === 'rent' ? 'Rent' : 'Sale' }}
                                    </span>
                                </td>
                                <td class="uh-numeric whitespace-nowrap text-xs">{{ $property->reference ?? '—' }}</td>
                                <td><x-ui.status :status="$property->editorialStatus()" /></td>
                                <td><x-ui.status :status="$property->availability" /></td>
                                <td class="uh-numeric whitespace-nowrap">{{ \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis) ?? '—' }}</td>
                                <td class="uh-admin-row-actions">
                                    <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.properties.edit', $property) }}">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($properties->hasPages())
            <div class="mt-6">{{ $properties->links() }}</div>
        @endif
    @else
        <x-ui.empty icon="home"
                    :title="filled($q ?? null) ? 'No listings match that search' : 'No properties yet'"
                    :description="filled($q ?? null) ? 'Try a different title or reference, or clear the search.' : 'Create your first listing, add photographs, then submit it for review before publishing.'">
            @if(filled($q ?? null))
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.properties.index') }}">Clear search</a>
            @endif
            <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.properties.create') }}">New property</a>
        </x-ui.empty>
    @endif
@endsection
