@php
    $user = auth()->user();
    $canReference = (bool) $user?->can('reference.manage');
    $canProject = (bool) $user?->can('create', \App\Models\Project::class);
    $canCategory = (bool) $user?->can('create', \App\Models\Post::class);
@endphp

@if($canReference || $canProject || $canCategory)
    <div>
        @if($canReference)
            <x-ui.admin-drawer name="type" title="Add a type">
                <form method="POST" action="{{ route('admin.property-types.store') }}" class="space-y-4" @submit="submit" x-data="uhAutoSlug()">
                    @csrf
                    <p class="text-sm text-[var(--color-muted)]" x-show="error" x-text="error" x-cloak></p>
                    <x-ui.select name="category" id="quick-type-category" label="Main type">
                        @foreach(\App\Models\PropertyType::categoryLabels() as $category => $categoryLabel)
                            <option value="{{ $category }}">{{ $categoryLabel }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input name="label" id="quick-type-label" label="Sub-type name" required maxlength="80" @input="fill($event.target.value)" />
                    <x-ui.input name="key" id="quick-type-key" label="Slug" optional dir="ltr" maxlength="50" x-model="slug" @change="edited()"
                                hint="Generated from the name." />
                    <x-ui.select name="field_profile" id="quick-type-profile" label="Fields shown" hint="Plots hide bedrooms, bathrooms, balconies and floor.">
                        @foreach(\App\Models\PropertyType::PROFILES as $profile)
                            <option value="{{ $profile }}">{{ ucfirst($profile) }}</option>
                        @endforeach
                    </x-ui.select>
                    <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">
                        <x-icon name="plus" class="size-3.5" />
                        Add type
                    </button>
                </form>
            </x-ui.admin-drawer>

            <x-ui.admin-drawer name="area" title="Add an area">
                <form method="POST" action="{{ route('admin.areas.store') }}" class="space-y-4" enctype="multipart/form-data" @submit="submit"
                      x-data="{ 
                          country: '{{ \App\Models\LocationArea::DEFAULT_COUNTRY }}', 
                          city: '', 
                          allCountries: @js($quickCountries),
                          placeTree: @js($quickPlaceTree),
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
                    <p class="text-sm text-[var(--color-muted)]" x-show="error" x-text="error" x-cloak></p>
                    
                    <div class="uh-field" x-data="{ preview: null, remove: false }">
                        <span class="uh-label" id="quick-aimg-create-label">Background image</span>
                        <div class="dd-image-field">
                            <div class="dd-image-preview">
                                <template x-if="preview"><img :src="preview" alt=""></template>
                                <span class="dd-image-empty" x-show="! preview"><x-icon name="image" class="size-6" /></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <label class="uh-btn-outline uh-btn-sm cursor-pointer">
                                    <x-icon name="upload" class="size-4" />
                                    <span>Upload</span>
                                    <input type="file" id="quick-aimg-create" name="image" class="sr-only" aria-labelledby="quick-aimg-create-label"
                                           accept="image/png,image/jpeg,image/webp"
                                           @change="const file = $event.target.files[0]; preview = file ? URL.createObjectURL(file) : null; if (file) remove = false">
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="uh-field relative" @click.outside="showCountry = false">
                        <label class="uh-label" for="quick-area-country">Country</label>
                        <input id="quick-area-country" name="country" x-model="country" @focus="showCountry = true" required maxlength="80" class="uh-input" autocomplete="new-password" placeholder="e.g. Bangladesh">
                        <div x-show="showCountry && filteredCountries.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg max-h-60 overflow-y-auto" style="display: none;" x-cloak>
                            <template x-for="c in filteredCountries" :key="c">
                                <div class="px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm" @click="country = c; showCountry = false" x-text="c"></div>
                            </template>
                        </div>
                    </div>
                    <div class="uh-field relative" @click.outside="showCity = false">
                        <label class="uh-label" for="quick-area-city">City</label>
                        <input id="quick-area-city" name="city" x-model="city" @focus="showCity = true" required maxlength="120" class="uh-input" autocomplete="new-password" placeholder="e.g. Dhaka">
                        <div x-show="showCity && filteredCities.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg max-h-60 overflow-y-auto" style="display: none;" x-cloak>
                            <template x-for="c in filteredCities" :key="c">
                                <div class="px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm" @click="city = c; showCity = false" x-text="c"></div>
                            </template>
                        </div>
                        <p class="uh-hint" id="quick-area-city-hint">Pick an existing city or type a new one.</p>
                    </div>
                    <x-ui.input name="name" id="quick-area-name" label="Area name" required maxlength="120" placeholder="e.g. Gulshan" />
                    <x-ui.rich-text name="intro" label="Area page introduction" value="" optional id="quick-ai-create" min-height="8rem"
                                    hint="Write a few useful sentences about the area. The public area page only goes live once this is filled in." />
                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.input name="lat" label="Map latitude" type="number" step="0.0000001" value="" optional id="quick-alat-create" />
                        <x-ui.input name="lng" label="Map longitude" type="number" step="0.0000001" value="" optional id="quick-alng-create" />
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
                        'idPrefix' => 'quick-area-seo-create',
                    ])

                    <div class="sticky bottom-0 bg-white pt-4 pb-6 -mb-6 z-10 border-t border-gray-100 -mx-6 px-6">
                        <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">
                            <x-icon name="plus" class="size-3.5" />
                            Add area
                        </button>
                    </div>
                </form>
            </x-ui.admin-drawer>

            <x-ui.admin-drawer name="amenity" title="Add an amenity">
                <form method="POST" action="{{ route('admin.amenities.store') }}" class="space-y-4" enctype="multipart/form-data" @submit="submit" x-data="uhAutoSlug()">
                    @csrf
                    <p class="text-sm text-[var(--color-muted)]" x-show="error" x-text="error" x-cloak></p>
                    <x-ui.input name="label" id="quick-amenity-label" label="Amenity name" required maxlength="80" @input="fill($event.target.value)" />
                    <x-ui.input name="key" id="quick-amenity-key" label="Slug" optional dir="ltr" maxlength="50" x-model="slug" @change="edited()"
                                hint="Generated from the name." />
                    <div class="uh-field">
                        <label class="uh-label" for="quick-amenity-icon">Icon image <span class="uh-label-optional">optional</span></label>
                        <input class="uh-input" type="file" id="quick-amenity-icon" name="icon" accept="image/png,image/jpeg,image/webp">
                    </div>
                    <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">
                        <x-icon name="plus" class="size-3.5" />
                        Add amenity
                    </button>
                </form>
            </x-ui.admin-drawer>
        @endif

        @if($canProject)
            <x-ui.admin-drawer name="project" title="Add a project">
                <form method="POST" action="{{ route('admin.projects.store') }}" class="space-y-4" @submit="submit">
                    @csrf
                    <p class="text-sm text-[var(--color-muted)]" x-show="error" x-text="error" x-cloak></p>
                    <x-ui.input name="name" id="quick-project-name" label="Project name" required />
                    <x-ui.select name="development_stage" id="quick-project-stage" label="Development stage" required>
                        @foreach(\App\Models\Project::STAGE_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected($value === 'ongoing')>{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    @include('admin.partials.place-picker', ['areas' => $quickAddAreas, 'cityName' => 'city', 'required' => false, 'idPrefix' => 'quick-project', 'allowAdd' => false])
                    <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">
                        <x-icon name="plus" class="size-3.5" />
                        Add project
                    </button>
                </form>
            </x-ui.admin-drawer>
        @endif

        @if($canCategory)
            <x-ui.admin-drawer name="category" title="Add a category">
                <form method="POST" action="{{ route('admin.post-categories.store') }}" class="space-y-4" @submit="submit">
                    @csrf
                    <p class="text-sm text-[var(--color-muted)]" x-show="error" x-text="error" x-cloak></p>
                    <x-ui.input name="name" id="quick-category-name" label="Category name" required />
                    <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">
                        <x-icon name="plus" class="size-3.5" />
                        Add category
                    </button>
                </form>
            </x-ui.admin-drawer>
        @endif
    </div>
@endif
