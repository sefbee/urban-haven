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
                <form method="POST" action="{{ route('admin.property-types.store') }}" class="space-y-4" @submit="submit">
                    @csrf
                    <p class="text-sm text-[var(--color-muted)]" x-show="error" x-text="error" x-cloak></p>
                    <x-ui.input name="key" id="quick-type-key" label="Key" required placeholder="apartment" dir="ltr"
                                hint="Lowercase, no spaces. Used internally." />
                    <x-ui.input name="label" id="quick-type-label" label="Label" required placeholder="Apartment" />
                    <x-ui.select name="category" id="quick-type-category" label="Main type">
                        @foreach(\App\Models\PropertyType::categoryLabels() as $category => $categoryLabel)
                            <option value="{{ $category }}">{{ $categoryLabel }}</option>
                        @endforeach
                    </x-ui.select>
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
                    <x-ui.input name="name" id="quick-area-name" label="Area name" required placeholder="Gulshan 2" />
                    <x-ui.input name="city" id="quick-area-city" label="City" required value="Dhaka" />
                    <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">
                        <x-icon name="plus" class="size-3.5" />
                        Add area
                    </button>
                </form>
            </x-ui.admin-drawer>

            <x-ui.admin-drawer name="amenity" title="Add an amenity">
                <form method="POST" action="{{ route('admin.amenities.store') }}" class="space-y-4" @submit="submit">
                    @csrf
                    <p class="text-sm text-[var(--color-muted)]" x-show="error" x-text="error" x-cloak></p>
                    <x-ui.input name="key" id="quick-amenity-key" label="Key" required placeholder="lift" dir="ltr"
                                hint="Lowercase, no spaces. Used internally." />
                    <x-ui.input name="label" id="quick-amenity-label" label="Label" required placeholder="Lift" />
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
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing" selected>Ongoing</option>
                        <option value="completed">Completed</option>
                    </x-ui.select>
                    <x-ui.input name="city" id="quick-project-city" label="City" required value="Dhaka" />
                    <x-ui.select name="location_area_id" id="quick-project-area" label="Area" optional>
                        <option value="">Not set</option>
                        @foreach($quickAddAreas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }} — {{ $area->city }}</option>
                        @endforeach
                    </x-ui.select>
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
