@php
    $model = $project ?? null;
    $canEdit ??= true;
    $selectedAmenities = array_map('intval', (array) old('amenity_ids', $model->amenity_ids ?? []));
    $steps = ['about' => 'About', 'where' => 'Where', 'describe' => 'Describe', 'facilities' => 'Facilities', 'media' => 'Media', 'seo' => 'SEO'];
@endphp

<x-ui.page-header compact :title="$model->name ?? 'New project'">
    <x-slot:eyebrow>Content library · {{ $model ? 'Edit project' : 'New project' }}</x-slot:eyebrow>
    <x-slot:actions>
        <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.projects.index') }}">
            <x-icon name="chevron-left" class="size-4" />
            All projects
        </a>
    </x-slot:actions>
</x-ui.page-header>

<nav class="dd-steps" aria-label="Form sections">
    @foreach($steps as $anchor => $label)
        <a href="#step-{{ $anchor }}">{{ $label }}</a>
    @endforeach
</nav>

<div @class(['uh-admin-compose', 'is-split' => (bool) $model])>
    <form method="POST" action="{{ $model ? route('admin.projects.update', $model) : route('admin.projects.store') }}"
          class="uh-admin-compose-main" enctype="multipart/form-data" x-data="uhForm" @submit="submit" data-unsaved-guard>
        @csrf
        @if($model) @method('PUT') @endif
        <fieldset class="grid gap-4" @disabled($model && ! $canEdit)>
        <div class="uh-admin-stack dd-form-steps">

        <section class="uh-panel" id="step-about">
            <h2 class="uh-h4">About the project</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-ui.input name="name" label="Project name" :value="$model->name ?? ''" :autofocus="! $model" required maxlength="255" class="sm:col-span-2" />
                <x-ui.select name="development_stage" label="Development stage" required>
                    @foreach(\App\Models\Project::STAGE_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(old('development_stage', $model->development_stage ?? 'ongoing') === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="developer_name" label="Developer" :value="$model->developer_name ?? ''" optional maxlength="255" />
            </div>
        </section>

        <section class="uh-panel" id="step-where">
            <h2 class="uh-h4">Where is it?</h2>
            <p class="dd-step-hint">Country, then city, then area.</p>
            <div class="mt-4 space-y-4">
                @include('admin.partials.place-picker', [
                    'areas' => $areas,
                    'selectedArea' => $model->location_area_id ?? '',
                    'cityName' => 'city',
                    'selectedCity' => $model->city ?? '',
                    'required' => false,
                    'idPrefix' => 'project-place',
                ])
                <x-ui.input name="address" label="Street address" :value="$model->address ?? ''" optional maxlength="255" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="lat" label="Map pin latitude" type="number" step="0.0000001" dir="ltr" :value="$model->lat ?? ''" optional placeholder="23.7806" />
                    <x-ui.input name="lng" label="Map pin longitude" type="number" step="0.0000001" dir="ltr" :value="$model->lng ?? ''" optional placeholder="90.4074" />
                </div>
            </div>
        </section>

        <section class="uh-panel" id="step-describe">
            <h2 class="uh-h4">Describe it</h2>
            <div class="mt-4 space-y-4">
                <x-ui.rich-text name="description" label="Description" :value="$model->description ?? ''" min-height="14rem" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="completion_date" label="Completion date" type="date" optional
                                :value="$model?->completion_date?->format('Y-m-d')" />
                    <x-ui.input name="trust_label" label="Trust label" :value="$model->trust_label ?? ''" optional maxlength="255"
                                hint="A verifiable fact only, e.g. “RAJUK approved plan”." />
                </div>
                <x-ui.rich-text name="handover_info" label="Handover information" optional
                                :value="$model->handover_info ?? ''" min-height="8rem"
                                hint="Shown as a notice on the project page." />
            </div>
        </section>

        <section class="uh-panel" id="step-facilities">
            <div class="flex items-center justify-between gap-3">
                <h2 class="uh-h4">Facilities in the development</h2>
                @can('reference.manage')
                    <button type="button" class="uh-admin-quick-add" aria-label="Add an amenity"
                            @click.prevent="$dispatch('uh-quick-add', { kind: 'amenity', target: 'amenity-list' })">
                        <x-icon name="plus" class="size-4" />
                    </button>
                @endcan
            </div>
            <div id="amenity-list" class="mt-4 grid gap-x-6 gap-y-1 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($amenities as $amenity)
                    <label class="uh-check">
                        <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"
                               @checked(in_array((int) $amenity->id, $selectedAmenities, true))>
                        @if($amenity->iconUrl())
                            <img class="size-5 object-contain" src="{{ $amenity->iconUrl() }}" alt="" loading="lazy">
                        @else
                            <x-icon name="sparkle" class="size-4 text-[var(--color-muted)]" />
                        @endif
                        <span>{{ $amenity->label }}</span>
                    </label>
                @empty
                    <p class="text-sm text-[var(--color-muted)]">No amenities yet. Add them under Catalogue › Amenities.</p>
                @endforelse
            </div>
        </section>

        @if(! $model)
            <div id="step-media">
                @include('admin.partials.photograph-field')
            </div>
        @endif

        <section class="uh-panel" @if($model) id="step-media" @endif>
            <h2 class="uh-h4">Video and placement</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-ui.input name="video_url" label="Video URL" type="url" dir="ltr" :value="$model->video_url ?? ''" optional hint="YouTube or Vimeo link." />
                <div class="uh-field justify-end">
                    <input type="hidden" name="is_featured" value="0">
                    <label class="uh-check">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $model->is_featured ?? false))>
                        <span>Feature on the homepage</span>
                    </label>
                </div>
            </div>
            @if($model)
            @endif
        </section>

        <div id="step-seo">
            @include('admin.partials.seo-panel', [
                'seo' => $model?->seoOverride,
                'slugName' => 'slug',
                'slug' => $model->slug ?? null,
                'baseUrl' => url('/projects'),
                'titleSource' => 'name',
                'contentSource' => 'description',
                'idPrefix' => 'project-seo',
            ])
        </div>

        </div>

        <div class="uh-admin-dock">
            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                <span x-text="submitting ? 'Saving…' : '{{ $model ? 'Save changes' : 'Create project' }}'">{{ $model ? 'Save changes' : 'Create project' }}</span>
            </button>
            <a class="uh-btn-ghost" href="{{ route('admin.projects.index') }}">Cancel</a>
        </div>
        </fieldset>
    </form>

    @if($model)
        <div class="uh-admin-compose-side">
            @include('admin.partials.publication-panel', ['model' => $model, 'routePrefix' => 'admin.projects', 'noun' => 'project', 'status' => $model->editorialStatus(), 'checklist' => $checklist])

            <section class="uh-panel">
                <h2 class="uh-h4">Listings in this project</h2>
                <p class="mt-2 uh-numeric text-3xl font-semibold leading-none">{{ $model->properties()->count() }}</p>
                <a class="uh-link mt-3 inline-block text-xs" href="{{ route('admin.properties.index', ['project_id' => $model->id]) }}">Manage these listings</a>
            </section>
        </div>
    @endif
</div>

@if($model)
    @include('admin.partials.media-manager', ['owner' => $model, 'ownerType' => 'project', 'collections' => ['gallery' => 'Photographs', 'brochure' => 'Brochures'], 'canEdit' => $canEdit])
@endif
