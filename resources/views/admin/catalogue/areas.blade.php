@extends('layouts.admin')
@section('title', 'Areas')

@php
    $drawer = $errors->any() ? old('_drawer', 'create') : null;
@endphp

@section('content')
    <div x-data="uhAdminDrawers(@js($drawer))">
        <x-ui.page-header compact title="Areas">
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
            <form method="POST" action="{{ route('admin.areas.store') }}" class="space-y-4" enctype="multipart/form-data" 
                  x-data="{ 
                      country: '{{ \App\Models\LocationArea::DEFAULT_COUNTRY }}', 
                      city: '', 
                      allCountries: @js($countries),
                      placeTree: @js($placeTree),
                      showCountry: false,
                      showCity: false,
                      get filteredCountries() {
                          return this.allCountries.filter(c => c.toLowerCase().includes(this.country.toLowerCase()));
                      },
                      get filteredCities() {
                          const cities = this.placeTree[this.country] || [];
                          return cities.filter(c => c.toLowerCase().includes(this.city.toLowerCase()));
                      }
                  }"
                  x-init="$watch('country', () => city = '')"
            >
                @csrf
                <input type="hidden" name="_drawer" value="create">
                <div class="uh-field" x-data="{ preview: null, remove: false }">
                    <span class="uh-label" id="aimg-create-label">Background image</span>
                    <div class="dd-image-field">
                        <div class="dd-image-preview">
                            <template x-if="preview"><img :src="preview" alt=""></template>
                            <span class="dd-image-empty" x-show="! preview"><x-icon name="image" class="size-6" /></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <label class="uh-btn-outline uh-btn-sm cursor-pointer">
                                <x-icon name="upload" class="size-4" />
                                <span>Upload</span>
                                <input type="file" id="aimg-create" name="image" class="sr-only" aria-labelledby="aimg-create-label"
                                       accept="image/png,image/jpeg,image/webp"
                                       @change="const file = $event.target.files[0]; preview = file ? URL.createObjectURL(file) : null; if (file) remove = false">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="uh-field relative" @click.outside="showCountry = false">
                    <label class="uh-label" for="area-country">Country</label>
                    <input id="area-country" name="country" x-model="country" @focus="showCountry = true" required maxlength="80" class="uh-input" autocomplete="new-password" placeholder="e.g. Bangladesh">
                    <div x-show="showCountry && filteredCountries.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg max-h-60 overflow-y-auto" style="display: none;">
                        <template x-for="c in filteredCountries" :key="c">
                            <div class="px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm" @click="country = c; showCountry = false" x-text="c"></div>
                        </template>
                    </div>
                </div>
                <div class="uh-field relative" @click.outside="showCity = false">
                    <label class="uh-label" for="area-city">City</label>
                    <input id="area-city" name="city" x-model="city" @focus="showCity = true" required maxlength="120" class="uh-input" autocomplete="new-password" placeholder="e.g. Dhaka">
                    <div x-show="showCity && filteredCities.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg max-h-60 overflow-y-auto" style="display: none;">
                        <template x-for="c in filteredCities" :key="c">
                            <div class="px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm" @click="city = c; showCity = false" x-text="c"></div>
                        </template>
                    </div>
                    <p class="uh-hint" id="area-city-hint">Pick an existing city or type a new one.</p>
                </div>
                <x-ui.input name="name" id="area-name" label="Area name" required maxlength="120" placeholder="e.g. Gulshan" />
                <x-ui.rich-text name="intro" label="Area page introduction" value="" optional id="ai-create" min-height="8rem"
                                hint="Write a few useful sentences about the area. The public area page only goes live once this is filled in." />
                <div class="grid grid-cols-2 gap-3">
                    <x-ui.input name="lat" label="Map latitude" type="number" step="0.0000001" value="" optional id="alat-create" />
                    <x-ui.input name="lng" label="Map longitude" type="number" step="0.0000001" value="" optional id="alng-create" />
                </div>
                <input type="hidden" name="is_active" value="0">
                <label class="uh-check text-sm"><input type="checkbox" name="is_active" value="1" checked> <span>Active</span></label>

                @include('admin.partials.seo-panel', [
                    'seo' => null,
                    'slugName' => 'slug',
                    'slug' => null,
                    'autoSlug' => false,
                    'baseUrl' => url('/locations'),
                    'titleSource' => 'name',
                    'contentSource' => 'intro',
                    'fallbackTitle' => 'Property in area',
                    'idPrefix' => 'area-seo-create',
                ])

                <div class="sticky bottom-0 bg-white pt-4 pb-6 -mb-6 z-10 border-t border-gray-100 -mx-6 px-6">
                    <button type="submit" class="uh-btn-primary uh-btn-block">
                        <x-icon name="plus" class="size-3.5" />
                        Add area
                    </button>
                </div>
            </form>
        </x-ui.admin-drawer>

        @foreach($areas as $area)
            <x-ui.admin-drawer :name="'edit-'.$area->id" :title="'Edit '.$area->name">
                <form method="POST" action="{{ route('admin.areas.update', $area) }}" class="space-y-4" enctype="multipart/form-data" 
                      x-data="{ 
                          country: {{ \Illuminate\Support\Js::from($area->country) }}, 
                          city: {{ \Illuminate\Support\Js::from($area->city) }}, 
                          allCountries: @js($countries),
                          placeTree: @js($placeTree),
                          showCountry: false,
                          showCity: false,
                          get filteredCountries() {
                              return this.allCountries.filter(c => c.toLowerCase().includes(this.country.toLowerCase()));
                          },
                          get filteredCities() {
                              const cities = this.placeTree[this.country] || [];
                              return cities.filter(c => c.toLowerCase().includes(this.city.toLowerCase()));
                          }
                      }"
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_drawer" value="edit-{{ $area->id }}">
                    <div class="uh-field" x-data="{ preview: null, remove: false }">
                        <span class="uh-label" id="aimg-{{ $area->id }}-label">Background image</span>
                        <div class="dd-image-field">
                            <div class="dd-image-preview">
                                <template x-if="preview"><img :src="preview" alt=""></template>
                                @if($area->image_path)
                                    <img x-show="! preview && ! remove" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($area->image_path) }}" alt="">
                                @endif
                                <span class="dd-image-empty" x-show="! preview && {{ $area->image_path ? 'remove' : 'true' }}"><x-icon name="image" class="size-6" /></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <label class="uh-btn-outline uh-btn-sm cursor-pointer">
                                    <x-icon name="upload" class="size-4" />
                                    <span>{{ $area->image_path ? 'Replace' : 'Upload' }}</span>
                                    <input type="file" id="aimg-{{ $area->id }}" name="image" class="sr-only" aria-labelledby="aimg-{{ $area->id }}-label"
                                           accept="image/png,image/jpeg,image/webp"
                                           @change="const file = $event.target.files[0]; preview = file ? URL.createObjectURL(file) : null; if (file) remove = false">
                                </label>
                                @if($area->image_path)
                                    <label class="uh-check mt-2 text-xs">
                                        <input type="checkbox" name="remove_image" value="1" x-model="remove">
                                        <span>Remove</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="uh-field relative" @click.outside="showCountry = false">
                        <label class="uh-label" :for="'acountry-{{ $area->id }}'">Country</label>
                        <input :id="'acountry-{{ $area->id }}'" name="country" x-model="country" @focus="showCountry = true" @input="city = ''" required maxlength="80" class="uh-input" autocomplete="new-password">
                        <div x-show="showCountry && filteredCountries.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg max-h-60 overflow-y-auto" style="display: none;">
                            <template x-for="c in filteredCountries" :key="c">
                                <div class="px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm" @click="country = c; showCountry = false; city = ''" x-text="c"></div>
                            </template>
                        </div>
                    </div>
                    <div class="uh-field relative" @click.outside="showCity = false">
                        <label class="uh-label" :for="'ac-{{ $area->id }}'">City</label>
                        <input :id="'ac-{{ $area->id }}'" name="city" x-model="city" @focus="showCity = true" required maxlength="120" class="uh-input" autocomplete="new-password">
                        <div x-show="showCity && filteredCities.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg max-h-60 overflow-y-auto" style="display: none;">
                            <template x-for="c in filteredCities" :key="c">
                                <div class="px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm" @click="city = c; showCity = false" x-text="c"></div>
                            </template>
                        </div>
                    </div>
                    <x-ui.input name="name" label="Area name" :value="$area->name" required maxlength="120" :id="'an-'.$area->id" />
                    <x-ui.rich-text name="intro" label="Area page introduction" :value="$area->intro" optional :id="'ai-'.$area->id" min-height="8rem"
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

                    <div class="sticky bottom-0 bg-white pt-4 pb-6 -mb-6 z-10 border-t border-gray-100 -mx-6 px-6">
                        <button type="submit" class="uh-btn-primary uh-btn-block">Save area</button>
                    </div>
                </form>
            </x-ui.admin-drawer>
        @endforeach
    </div>
@endsection
