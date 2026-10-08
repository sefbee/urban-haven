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
                <form method="POST" action="{{ route('admin.areas.store') }}" class="space-y-4" @submit="submit">
                    @csrf
                    <p class="text-sm text-[var(--color-muted)]" x-show="error" x-text="error" x-cloak></p>
                    <x-ui.input name="country" id="quick-area-country" label="Country" list="quick-area-countries" required maxlength="80" :value="\App\Models\LocationArea::DEFAULT_COUNTRY" />
                    <x-ui.input name="city" id="quick-area-city" label="City" list="quick-area-cities" required maxlength="120" :value="$quickAddAreas->pluck('city')->countBy()->sortDesc()->keys()->first()"
                                hint="Pick an existing city or type a new one." />
                    <x-ui.input name="name" id="quick-area-name" label="Area name" required maxlength="120" />
                    <datalist id="quick-area-countries">
                        @foreach($quickAddAreas->pluck('country')->push(\App\Models\LocationArea::DEFAULT_COUNTRY)->filter()->unique()->sort() as $country)
                            <option value="{{ $country }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="quick-area-cities">
                        @foreach($quickAddAreas->pluck('city')->filter()->unique()->sort() as $city)
                            <option value="{{ $city }}"></option>
                        @endforeach
                    </datalist>
                    <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">
                        <x-icon name="plus" class="size-3.5" />
                        Add area
                    </button>
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
