@extends('layouts.admin')
@section('title', 'Areas')

@php
    $drawer = $errors->any() ? old('_drawer', 'create') : null;
@endphp

@section('content')
    <div x-data="uhAdminDrawers(@js($drawer))">
        <x-ui.page-header compact title="Areas"
                          description="Neighbourhoods used on listings, search filters, and area pages. Deactivating one hides it from filters without touching existing listings.">
            <x-slot:eyebrow>Catalogue</x-slot:eyebrow>
            <x-slot:actions>
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">
                    <x-icon name="plus" class="size-4" />
                    Add area
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('admin.catalogue._related')

        @if($areas->isNotEmpty())
            <div class="uh-panel-flush overflow-hidden">
                <div class="uh-table-scroll">
                    <table class="uh-table">
                        <caption class="sr-only">Areas</caption>
                        <thead>
                            <tr>
                                <th scope="col">Area</th>
                                <th scope="col">City</th>
                                <th scope="col">Status</th>
                                <th scope="col"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($areas as $area)
                                <tr>
                                    <td class="min-w-44 font-medium">{{ $area->name }}</td>
                                    <td>{{ $area->city }}</td>
                                    <td>
                                        @if($area->is_active)
                                            <x-ui.badge tone="success">Active</x-ui.badge>
                                        @else
                                            <x-ui.badge tone="outline">Inactive</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="uh-admin-row-actions">
                                        <button type="button" class="uh-btn-ghost uh-btn-sm" @click="open('edit-{{ $area->id }}')">Edit</button>
                                        @if($area->is_active)
                                            <form method="POST" action="{{ route('admin.areas.deactivate', $area) }}"
                                                  x-data="uhConfirm('Deactivate {{ $area->name }}?')">
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
            <x-ui.empty icon="pin" title="No areas yet" description="Add the neighbourhoods you list in, such as Gulshan or Dhanmondi.">
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">Add area</button>
            </x-ui.empty>
        @endif

        <x-ui.admin-drawer name="create" title="Add an area">
            <form method="POST" action="{{ route('admin.areas.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_drawer" value="create">
                <x-ui.input name="name" id="area-name" label="Area name" required placeholder="Gulshan 2" />
                <x-ui.input name="city" id="area-city" label="City" required value="Dhaka" />
                <button type="submit" class="uh-btn-primary uh-btn-block">
                    <x-icon name="plus" class="size-3.5" />
                    Add area
                </button>
            </form>
        </x-ui.admin-drawer>

        @foreach($areas as $area)
            <x-ui.admin-drawer :name="'edit-'.$area->id" :title="'Edit '.$area->name">
                <form method="POST" action="{{ route('admin.areas.update', $area) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_drawer" value="edit-{{ $area->id }}">
                    <x-ui.input name="name" label="Name" :value="$area->name" required :id="'an-'.$area->id" />
                    <x-ui.input name="city" label="City" :value="$area->city" required :id="'ac-'.$area->id" />
                    <x-ui.textarea name="intro" label="Area page introduction" rows="3" :value="$area->intro" optional :id="'ai-'.$area->id" />
                    <x-ui.input name="meta_description" label="Meta description" :value="$area->meta_description" maxlength="160" optional :id="'am-'.$area->id" />
                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.input name="lat" label="Latitude" type="number" step="0.0000001" :value="$area->lat" optional :id="'alat-'.$area->id" />
                        <x-ui.input name="lng" label="Longitude" type="number" step="0.0000001" :value="$area->lng" optional :id="'alng-'.$area->id" />
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="uh-check text-sm"><input type="checkbox" name="is_active" value="1" @checked($area->is_active)> <span>Active</span></label>
                    <button type="submit" class="uh-btn-primary uh-btn-block">Save area</button>
                </form>
            </x-ui.admin-drawer>
        @endforeach
    </div>
@endsection
