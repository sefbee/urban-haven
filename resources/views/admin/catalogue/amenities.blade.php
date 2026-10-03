@extends('layouts.admin')
@section('title', 'Amenities')

@php
    $drawer = $errors->any() ? old('_drawer', 'create') : null;
@endphp

@section('content')
    <div x-data="uhAdminDrawers(@js($drawer))">
        <x-ui.page-header compact title="Amenities"
                          description="Features buyers filter by, such as lift, parking or a generator. Deactivating one hides it from filters without touching existing listings.">
            <x-slot:eyebrow>Catalogue</x-slot:eyebrow>
            <x-slot:actions>
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">
                    <x-icon name="plus" class="size-4" />
                    Add amenity
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('admin.catalogue._related')

        @if($amenities->isNotEmpty())
            <div class="uh-panel-flush overflow-hidden">
                <div class="uh-table-scroll">
                    <table class="uh-table">
                        <caption class="sr-only">Amenities</caption>
                        <thead>
                            <tr>
                                <th scope="col">Amenity</th>
                                <th scope="col">Status</th>
                                <th scope="col"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($amenities as $amenity)
                                <tr>
                                    <td class="min-w-44 font-medium">{{ $amenity->label }}</td>
                                    <td>
                                        @if($amenity->is_active)
                                            <x-ui.badge tone="success">Active</x-ui.badge>
                                        @else
                                            <x-ui.badge tone="outline">Inactive</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="uh-admin-row-actions">
                                        @if($amenity->is_active)
                                            <form method="POST" action="{{ route('admin.amenities.deactivate', $amenity) }}"
                                                  x-data="uhConfirm('Deactivate {{ $amenity->label }}?')">
                                                @csrf
                                                <button type="submit" class="uh-btn-ghost uh-btn-sm text-[var(--color-danger)]" @click="confirm($event)">Deactivate</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <x-ui.empty icon="sparkle" title="No amenities yet" description="Add the building features people ask for most often.">
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">Add amenity</button>
            </x-ui.empty>
        @endif

        <x-ui.admin-drawer name="create" title="Add an amenity">
            <form method="POST" action="{{ route('admin.amenities.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_drawer" value="create">
                <x-ui.input name="key" id="amenity-key" label="Key" required placeholder="lift" dir="ltr"
                            hint="Lowercase, no spaces. Used internally." />
                <x-ui.input name="label" id="amenity-label" label="Label" required placeholder="Lift" />
                <button type="submit" class="uh-btn-primary uh-btn-block">
                    <x-icon name="plus" class="size-3.5" />
                    Add amenity
                </button>
            </form>
        </x-ui.admin-drawer>
    </div>
@endsection
