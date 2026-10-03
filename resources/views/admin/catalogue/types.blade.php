@extends('layouts.admin')
@section('title', 'Property types')

@php
    $drawer = $errors->any() ? old('_drawer', 'create') : null;
@endphp

@section('content')
    <div x-data="uhAdminDrawers(@js($drawer))">
        <x-ui.page-header compact title="Property types"
                          description="Apartment, plot, commercial and any other types used on listings. The field profile decides which details a listing asks for.">
            <x-slot:eyebrow>Catalogue</x-slot:eyebrow>
            <x-slot:actions>
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">
                    <x-icon name="plus" class="size-4" />
                    Add type
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('admin.catalogue._related')

        @if($types->isNotEmpty())
            <div class="uh-panel-flush overflow-hidden">
                <div class="uh-table-scroll">
                    <table class="uh-table">
                        <caption class="sr-only">Property types</caption>
                        <thead>
                            <tr>
                                <th scope="col">Type</th>
                                <th scope="col">Category</th>
                                <th scope="col">Fields</th>
                                <th scope="col">Status</th>
                                <th scope="col"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($types as $type)
                                <tr>
                                    <td class="min-w-40 font-medium">{{ $type->label }}</td>
                                    <td class="text-sm">{{ ucfirst($type->category) }}</td>
                                    <td class="text-xs text-[var(--color-muted)]">{{ ucfirst($type->field_profile) }}</td>
                                    <td>
                                        @if($type->is_active)
                                            <x-ui.badge tone="success">Active</x-ui.badge>
                                        @else
                                            <x-ui.badge tone="outline">Inactive</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="uh-admin-row-actions">
                                        <button type="button" class="uh-btn-ghost uh-btn-sm" @click="open('edit-{{ $type->id }}')">Edit</button>
                                        @if($type->is_active)
                                            <form method="POST" action="{{ route('admin.property-types.deactivate', $type) }}"
                                                  x-data="uhConfirm('Deactivate {{ $type->label }}?')">
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
            <x-ui.empty icon="tag" title="No property types yet" description="Add types such as apartment, duplex or plot before creating listings.">
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">Add type</button>
            </x-ui.empty>
        @endif

        <x-ui.admin-drawer name="create" title="Add a type">
            <form method="POST" action="{{ route('admin.property-types.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_drawer" value="create">
                <x-ui.input name="key" id="type-key" label="Key" required placeholder="apartment" dir="ltr"
                            hint="Lowercase, no spaces. Used internally." />
                <x-ui.input name="label" id="type-label" label="Label" required placeholder="Apartment" />
                <x-ui.select name="category" id="type-category" label="Category">
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="field_profile" id="type-profile" label="Fields shown" hint="Plots hide bedrooms, bathrooms, balconies and floor.">
                    @foreach($profiles as $profile)
                        <option value="{{ $profile }}">{{ ucfirst($profile) }}</option>
                    @endforeach
                </x-ui.select>
                <button type="submit" class="uh-btn-primary uh-btn-block">
                    <x-icon name="plus" class="size-3.5" />
                    Add type
                </button>
            </form>
        </x-ui.admin-drawer>

        @foreach($types as $type)
            <x-ui.admin-drawer :name="'edit-'.$type->id" :title="'Edit '.$type->label">
                <form method="POST" action="{{ route('admin.property-types.update', $type) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_drawer" value="edit-{{ $type->id }}">
                    <x-ui.input name="label" label="Label" :value="$type->label" required :id="'tl-'.$type->id" />
                    <x-ui.select name="category" label="Category" :id="'tc-'.$type->id">
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected($type->category === $category)>{{ ucfirst($category) }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="field_profile" label="Fields shown" :id="'tp-'.$type->id">
                        @foreach($profiles as $profile)
                            <option value="{{ $profile }}" @selected($type->field_profile === $profile)>{{ ucfirst($profile) }}</option>
                        @endforeach
                    </x-ui.select>
                    <input type="hidden" name="is_active" value="0">
                    <label class="uh-check text-sm"><input type="checkbox" name="is_active" value="1" @checked($type->is_active)> <span>Active</span></label>
                    <button type="submit" class="uh-btn-primary uh-btn-block">Save type</button>
                </form>
            </x-ui.admin-drawer>
        @endforeach
    </div>
@endsection
