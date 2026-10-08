@extends('layouts.admin')
@section('title', 'Areas')

@php
    $drawer = $errors->any() ? old('_drawer', 'create') : null;
@endphp

@section('content')
    <div x-data="uhAdminDrawers(@js($drawer))">
        <x-ui.page-header compact title="Areas"
                          description="Country, city and neighbourhood used on listings, search filters and area pages. Deactivating one hides it from filters without touching existing listings.">
            <x-slot:eyebrow>Content library</x-slot:eyebrow>
            <x-slot:actions>
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">
                    <x-icon name="plus" class="size-4" />
                    Add area
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('admin.catalogue._related')

        <datalist id="area-countries">
            @foreach($countries as $country)
                <option value="{{ $country }}"></option>
            @endforeach
        </datalist>
        <datalist id="area-cities">
            @foreach($cities as $city)
                <option value="{{ $city }}"></option>
            @endforeach
        </datalist>

        @if($areas->isNotEmpty())
            <div class="uh-panel-flush overflow-hidden">
                <div class="uh-table-scroll">
                    <table class="uh-table">
                        <caption class="sr-only">Areas</caption>
                        <thead>
                            <tr>
                                <th scope="col">Country</th>
                                <th scope="col">City</th>
                                <th scope="col">Area</th>
                                <th scope="col">Listings</th>
                                <th scope="col">Area page</th>
                                <th scope="col">Status</th>
                                <th scope="col"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($areas as $area)
                                <tr>
                                    <td class="text-[var(--color-muted)]">{{ $area->country }}</td>
                                    <td>{{ $area->city }}</td>
                                    <td class="min-w-44 font-medium">{{ $area->name }}</td>
                                    <td class="uh-numeric">{{ $area->properties_count }}</td>
                                    <td>
                                        @if($area->hasLandingPage())
                                            <a class="uh-link text-xs" href="{{ route('locations.show', $area->slug) }}" target="_blank" rel="noopener" dir="ltr">/locations/{{ $area->slug }}</a>
                                        @else
                                            <span class="text-xs text-[var(--color-muted)]">Add an introduction to publish</span>
                                        @endif
                                    </td>
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
            <x-ui.empty icon="pin" title="No areas yet" description="Add the neighbourhoods you list in. Start with the country and city, then the area name.">
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">Add area</button>
            </x-ui.empty>
        @endif

        <x-ui.admin-drawer name="create" title="Add an area">
            <form method="POST" action="{{ route('admin.areas.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_drawer" value="create">
                <x-ui.input name="country" id="area-country" label="Country" list="area-countries" :value="\App\Models\LocationArea::DEFAULT_COUNTRY" maxlength="80" required />
                <x-ui.input name="city" id="area-city" label="City" list="area-cities" required maxlength="120" :value="$cities->first()"
                            hint="Pick an existing city or type a new one." />
                <x-ui.input name="name" id="area-name" label="Area name" required maxlength="120" hint="The neighbourhood, e.g. the name buyers search for." />
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
                    <x-ui.input name="country" label="Country" list="area-countries" :value="$area->country" required maxlength="80" :id="'acountry-'.$area->id" />
                    <x-ui.input name="city" label="City" list="area-cities" :value="$area->city" required maxlength="120" :id="'ac-'.$area->id" />
                    <x-ui.input name="name" label="Area name" :value="$area->name" required maxlength="120" :id="'an-'.$area->id" />
                    <x-ui.textarea name="intro" label="Area page introduction" rows="4" :value="$area->intro" optional :id="'ai-'.$area->id"
                                   hint="Write a few useful sentences about the area. The public area page only goes live once this is filled in." />
                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.input name="lat" label="Map latitude" type="number" step="0.0000001" :value="$area->lat" optional :id="'alat-'.$area->id" />
                        <x-ui.input name="lng" label="Map longitude" type="number" step="0.0000001" :value="$area->lng" optional :id="'alng-'.$area->id" />
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="uh-check text-sm"><input type="checkbox" name="is_active" value="1" @checked($area->is_active)> <span>Active</span></label>

                    @include('admin.partials.seo-panel', [
                        'seo' => $area->seoOverride,
                        'slugName' => 'slug',
                        'slug' => $area->slug,
                        'autoSlug' => false,
                        'baseUrl' => url('/locations'),
                        'titleSource' => 'name',
                        'contentSource' => 'intro',
                        'fallbackTitle' => 'Property in '.$area->name.', '.$area->city,
                        'idPrefix' => 'area-seo-'.$area->id,
                    ])

                    <button type="submit" class="uh-btn-primary uh-btn-block">Save area</button>
                </form>
            </x-ui.admin-drawer>
        @endforeach
    </div>
@endsection
