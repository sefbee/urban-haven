@extends('layouts.admin')
@section('title', 'Amenities')

@php
    $drawer = $errors->any() ? old('_drawer', 'create') : null;
@endphp

@section('content')
    <div x-data="uhAdminDrawers(@js($drawer))">
        <x-ui.page-header compact title="Amenities"
                          description="Features buyers filter by, such as lift, parking or a generator. Deactivating one hides it from filters without touching existing listings.">
            <x-slot:eyebrow>Content library</x-slot:eyebrow>
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
                                <th scope="col"><span class="sr-only">Icon</span></th>
                                <th scope="col">Amenity</th>
                                <th scope="col">Slug</th>
                                <th scope="col">Status</th>
                                <th scope="col"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                                @foreach($amenities as $amenity)
                                    <tr>
                                        <td class="w-16">
                                            @if($amenity->iconUrl())
                                                <img class="size-10 rounded object-contain" src="{{ $amenity->iconUrl() }}" alt="" loading="lazy">
                                            @else
                                                <x-icon name="sparkle" class="size-6 text-[var(--color-muted)]" />
                                            @endif
                                        </td>
                                        <td class="min-w-44 font-medium">{{ $amenity->label }}</td>
                                        <td class="text-xs text-[var(--color-muted)]" dir="ltr">{{ $amenity->key }}</td>
                                        <td>
                                            @if($amenity->is_active)
                                                <x-ui.badge tone="success">Active</x-ui.badge>
                                            @else
                                                <x-ui.badge tone="outline">Inactive</x-ui.badge>
                                            @endif
                                        </td>
                                        <td class="uh-admin-row-actions">
                                            <button type="button" class="uh-btn-ghost uh-btn-sm" @click="open('edit-{{ $amenity->id }}')">Edit</button>
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
            <form method="POST" action="{{ route('admin.amenities.store') }}" class="space-y-4" enctype="multipart/form-data" x-data="uhAutoSlug({ value: @js((string) old('key', '')) })">
                @csrf
                <input type="hidden" name="_drawer" value="create">
                <x-ui.input name="label" id="amenity-label" label="Amenity name" required maxlength="80" @input="fill($event.target.value)" />
                <x-ui.input name="key" id="amenity-key" label="Slug" optional dir="ltr" maxlength="50" x-model="slug" @change="edited()"
                            hint="Generated from the name. Lowercase letters, numbers and dashes." />
                <div class="uh-field">
                    <label class="uh-label" for="amenity-icon">Icon image <span class="uh-label-optional">optional</span></label>
                    <input class="uh-input" type="file" id="amenity-icon" name="icon" accept="image/png,image/jpeg,image/webp">
                    <p class="uh-hint">PNG, JPEG or WebP, up to 2 MB and 512 × 512 pixels.</p>
                    @error('icon')<p class="uh-error">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="uh-btn-primary uh-btn-block">
                    <x-icon name="plus" class="size-3.5" />
                    Add amenity
                </button>
            </form>
        </x-ui.admin-drawer>

        @foreach($amenities as $amenity)
            <x-ui.admin-drawer :name="'edit-'.$amenity->id" :title="'Edit '.$amenity->label">
                <form method="POST" action="{{ route('admin.amenities.update', $amenity) }}" class="space-y-4" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_drawer" value="edit-{{ $amenity->id }}">
                    <x-ui.input name="label" label="Amenity name" :value="$amenity->label" required maxlength="80" :id="'aml-'.$amenity->id" />
                    <p class="text-xs text-[var(--color-muted)]">Slug: <span dir="ltr">{{ $amenity->key }}</span>. It stays the same so saved searches keep working.</p>
                    <div class="uh-field">
                        <label class="uh-label" for="amenity-icon-{{ $amenity->id }}">Icon image <span class="uh-label-optional">optional</span></label>
                        @if($amenity->iconUrl())
                            <img class="mb-3 size-14 rounded border border-[var(--color-line)] bg-white p-2 object-contain" src="{{ $amenity->iconUrl() }}" alt="Current {{ $amenity->label }} icon">
                        @endif
                        <input class="uh-input" type="file" id="amenity-icon-{{ $amenity->id }}" name="icon" accept="image/png,image/jpeg,image/webp">
                        <p class="uh-hint">Upload to replace the current icon. PNG, JPEG or WebP, up to 2 MB and 512 × 512 pixels.</p>
                        @error('icon')<p class="uh-error">{{ $message }}</p>@enderror
                        @if($amenity->icon_path)
                            <label class="uh-check mt-2 text-sm"><input type="checkbox" name="remove_icon" value="1"> <span>Remove current icon</span></label>
                        @endif
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="uh-check text-sm"><input type="checkbox" name="is_active" value="1" @checked($amenity->is_active)> <span>Active</span></label>
                    <button type="submit" class="uh-btn-primary uh-btn-block">Save amenity</button>
                </form>
            </x-ui.admin-drawer>
        @endforeach
    </div>
@endsection
