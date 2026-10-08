@extends('layouts.admin')
@section('title', 'Property types')

@php
    $drawer = $errors->any() ? old('_drawer', 'create') : null;
@endphp

@section('content')
    <div x-data="uhAdminDrawers(@js($drawer))">
        <x-ui.page-header compact title="Property types">
            <x-slot:eyebrow>Content library</x-slot:eyebrow>
            <x-slot:actions>
                <button type="button" class="uh-btn-primary uh-btn-sm dd-type-add-button" @click="open('create')">
                    <x-icon name="plus" class="size-4" />
                    Add sub-type
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('admin.catalogue._related')

        @if($types->isNotEmpty())
            <div class="grid min-w-0 gap-4 lg:grid-cols-2">
                @foreach($typesByCategory as $category => $categoryTypes)
                    <section class="uh-panel-flush min-w-0 overflow-hidden" aria-labelledby="types-{{ $category }}">
                        <div class="flex items-center justify-between gap-3 border-b border-[var(--color-line)] px-4 py-3">
                            <h2 id="types-{{ $category }}" class="text-sm font-semibold">
                                {{ $categories[$category] }}
                                <span class="ml-1 font-normal text-[var(--color-muted)]">{{ $categoryTypes->count() }} {{ Str::plural('sub-type', $categoryTypes->count()) }}</span>
                            </h2>
                            <button type="button" class="uh-btn-primary uh-btn-sm dd-type-add-button" @click="open('create-{{ $category }}')">
                                <x-icon name="plus" class="size-3.5" />
                                Add to {{ $categories[$category] }}
                            </button>
                        </div>
                        @if($categoryTypes->isNotEmpty())
                            <div class="uh-table-scroll">
                                <table class="uh-table dd-types-table">
                                    <caption class="sr-only">{{ $categories[$category] }} sub-types</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">Sub-type</th>
                                            <th scope="col">Slug</th>
                                            <th scope="col">Fields</th>
                                            <th scope="col">Listings</th>
                                            <th scope="col">Status</th>
                                            <th scope="col"><span class="sr-only">Actions</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($categoryTypes as $type)
                                            <tr>
                                                <td class="min-w-0 font-medium">{{ $type->label }}</td>
                                                <td class="text-xs text-[var(--color-muted)]" dir="ltr">{{ $type->key }}</td>
                                                <td class="text-xs text-[var(--color-muted)]">{{ ucfirst($type->field_profile) }}</td>
                                                <td class="text-sm tabular-nums">{{ $type->properties_count }}</td>
                                                <td>
                                                    @if($type->is_active)
                                                        <x-ui.badge tone="success">Active</x-ui.badge>
                                                    @else
                                                        <x-ui.badge tone="outline">Inactive</x-ui.badge>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="uh-admin-row-actions">
                                                        <button type="button" class="uh-btn-ghost uh-btn-sm" @click="open('edit-{{ $type->id }}')">Edit</button>
                                                        @if($type->is_active)
                                                            <form method="POST" action="{{ route('admin.property-types.deactivate', $type) }}"
                                                                  x-data="uhConfirm('Deactivate {{ $type->label }}?')">
                                                                @csrf
                                                                <button type="submit" class="uh-btn-ghost uh-btn-sm text-[var(--color-danger)]" @click="confirm($event)">Deactivate</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="px-4 py-6 text-sm text-[var(--color-muted)]">No {{ Str::lower($categories[$category]) }} sub-types yet.</p>
                        @endif
                    </section>
                @endforeach
            </div>
        @else
            <x-ui.empty icon="tag" title="No property types yet" description="Add sub-types such as apartment, plot or office before creating listings.">
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">Add sub-type</button>
            </x-ui.empty>
        @endif

        @foreach(['create' => null, ...collect($categories)->keys()->mapWithKeys(fn ($key) => ['create-'.$key => $key])->all()] as $drawerName => $presetCategory)
            <x-ui.admin-drawer :name="$drawerName" :title="$presetCategory ? 'Add a '.Str::lower($categories[$presetCategory]).' sub-type' : 'Add a sub-type'">
                <form method="POST" action="{{ route('admin.property-types.store') }}" class="space-y-4" x-data="uhAutoSlug({ value: @js((string) old('key', '')) })">
                    @csrf
                    <input type="hidden" name="_drawer" value="{{ $drawerName }}">
                    <x-ui.select name="category" :id="$drawerName.'-category'" label="Main type">
                        @foreach($categories as $category => $categoryLabel)
                            <option value="{{ $category }}" @selected(old('category', $presetCategory) === $category)>{{ $categoryLabel }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input name="label" :id="$drawerName.'-label'" label="Sub-type name" required @input="fill($event.target.value)" />
                    <x-ui.input name="key" :id="$drawerName.'-key'" label="Slug" optional dir="ltr" maxlength="50" x-model="slug" @change="edited()"
                                hint="Generated from the name. Lowercase letters, numbers and dashes." />
                    <x-ui.select name="field_profile" :id="$drawerName.'-profile'" label="Fields shown" hint="Plots hide bedrooms, bathrooms, balconies and floor.">
                        @foreach($profiles as $profile)
                            <option value="{{ $profile }}" @selected(old('field_profile', $presetCategory === 'commercial' ? 'commercial' : null) === $profile)>{{ ucfirst($profile) }}</option>
                        @endforeach
                    </x-ui.select>
                    <button type="submit" class="uh-btn-primary uh-btn-block">
                        <x-icon name="plus" class="size-3.5" />
                        Add sub-type
                    </button>
                </form>
            </x-ui.admin-drawer>
        @endforeach

        @foreach($types as $type)
            <x-ui.admin-drawer :name="'edit-'.$type->id" :title="'Edit '.$type->label">
                <form method="POST" action="{{ route('admin.property-types.update', $type) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_drawer" value="edit-{{ $type->id }}">
                    <x-ui.select name="category" label="Main type" :id="'tc-'.$type->id">
                        @foreach($categories as $category => $categoryLabel)
                            <option value="{{ $category }}" @selected($type->category === $category)>{{ $categoryLabel }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input name="label" label="Sub-type name" :value="$type->label" required :id="'tl-'.$type->id" />
                    <x-ui.select name="field_profile" label="Fields shown" :id="'tp-'.$type->id">
                        @foreach($profiles as $profile)
                            <option value="{{ $profile }}" @selected($type->field_profile === $profile)>{{ ucfirst($profile) }}</option>
                        @endforeach
                    </x-ui.select>
                    <input type="hidden" name="is_active" value="0">
                    <label class="uh-check text-sm"><input type="checkbox" name="is_active" value="1" @checked($type->is_active)> <span>Active</span></label>
                    <button type="submit" class="uh-btn-primary uh-btn-block">Save sub-type</button>
                </form>
            </x-ui.admin-drawer>
        @endforeach
    </div>
@endsection
