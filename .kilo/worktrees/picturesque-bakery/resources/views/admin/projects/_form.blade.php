@php
    $model = $project ?? null;
    $selectedAmenities = array_map('intval', (array) old('amenity_ids', $model->amenity_ids ?? []));
@endphp

<x-ui.page-header compact :title="$model->name ?? 'New project'"
                  :description="$model ? 'Changes are saved as a draft until you publish.' : 'A project groups several listings in one development.'">
    <x-slot:eyebrow>{{ $model ? 'Edit project' : 'New project' }}</x-slot:eyebrow>
    <x-slot:actions>
        <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.projects.index') }}">
            <x-icon name="chevron-left" class="size-4" />
            All projects
        </a>
        @if($model && $model->isPublished())
            <a class="uh-btn-outline uh-btn-sm" href="{{ route('projects.show', $model->slug) }}">
                <x-icon name="external" class="size-4" />
                View live
            </a>
        @endif
    </x-slot:actions>
</x-ui.page-header>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ $model ? route('admin.projects.update', $model) : route('admin.projects.store') }}"
          class="space-y-6 lg:col-span-2" x-data="uhForm" @submit="submit">
        @csrf
        @if($model) @method('PUT') @endif

        <section class="uh-panel">
            <h2 class="uh-h4">Project details</h2>
            <div class="mt-4 space-y-4">
                <x-ui.input name="name" label="Project name" :value="$model->name ?? ''" required />
                <x-ui.textarea name="description" label="Description" rows="6" :value="$model->description ?? ''" />
            </div>
        </section>

        <section class="uh-panel">
            <h2 class="uh-h4">Location and stage</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-ui.select name="development_stage" label="Development stage" required>
                    @foreach(['upcoming' => 'Upcoming', 'ongoing' => 'Ongoing', 'completed' => 'Completed'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('development_stage', $model->development_stage ?? 'ongoing') === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="city" label="City" :value="$model->city ?? 'Dhaka'" required />
                <x-ui.select name="location_area_id" label="Area" optional>
                    <option value="">Not set</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected(old('location_area_id', $model->location_area_id ?? '') == $area->id)>{{ $area->name }} — {{ $area->city }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="completion_date" label="Completion date" type="date" optional
                            :value="$model?->completion_date?->format('Y-m-d')" />
                <x-ui.input name="lat" label="Latitude" type="number" step="0.0000001" dir="ltr" :value="$model->lat ?? ''" optional />
                <x-ui.input name="lng" label="Longitude" type="number" step="0.0000001" dir="ltr" :value="$model->lng ?? ''" optional />
            </div>
        </section>

        <section class="uh-panel">
            <h2 class="uh-h4">Developer and handover</h2>
            <div class="mt-4 space-y-4">
                <x-ui.input name="developer_name" label="Developer" :value="$model->developer_name ?? ''" optional />
                <x-ui.textarea name="handover_info" label="Handover information" rows="3" optional
                               :value="$model->handover_info ?? ''"
                               hint="Shown as a notice on the project page." />
                <x-ui.input name="trust_label" label="Trust label" :value="$model->trust_label ?? ''" optional
                            hint="A verifiable fact only, e.g. “RAJUK approved plan”." />
                <div>
                    <input type="hidden" name="is_featured" value="0">
                    <label class="uh-check">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $model->is_featured ?? false))>
                        <span>Feature on the homepage</span>
                    </label>
                </div>
            </div>

            @if($amenities->isNotEmpty())
                <fieldset class="mt-5 border-t border-line pt-5">
                    <legend class="uh-legend">Facilities in the development</legend>
                    <div class="grid gap-x-6 gap-y-1 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($amenities as $amenity)
                            <label class="uh-check">
                                <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"
                                       @checked(in_array((int) $amenity->id, $selectedAmenities, true))>
                                <span>{{ $amenity->label }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                <span x-text="submitting ? 'Saving…' : '{{ $model ? 'Save changes' : 'Create project' }}'">{{ $model ? 'Save changes' : 'Create project' }}</span>
            </button>
            <a class="uh-btn-ghost" href="{{ route('admin.projects.index') }}">Cancel</a>
        </div>
    </form>

    @if($model)
        <div class="space-y-6">
            <section class="uh-panel">
                <h2 class="uh-h4">Publication</h2>
                <p class="mt-2"><x-ui.status :status="$model->editorialStatus()" /></p>
                <p class="mt-3 text-xs leading-relaxed text-[var(--color-muted)]">
                    Only published projects appear on the public site.
                </p>
                <div class="mt-4 space-y-2">
                    <form method="POST" action="{{ route('admin.projects.submit', $model) }}">
                        @csrf
                        <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Submit for review</button>
                    </form>
                    <form method="POST" action="{{ route('admin.projects.approve', $model) }}">
                        @csrf
                        <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('admin.projects.publish', $model) }}">
                        @csrf
                        <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block">Publish</button>
                    </form>
                </div>

                <form method="POST" action="{{ route('admin.projects.unpublish', $model) }}"
                      class="mt-4 space-y-2 border-t border-line pt-4"
                      x-data="uhConfirm('Unpublish this project? It will disappear from the public site immediately.')">
                    @csrf
                    <x-ui.input name="unpublish_reason" label="Reason for unpublishing" required
                                hint="Recorded in the audit log." />
                    <button type="submit" class="uh-btn-danger uh-btn-sm uh-btn-block" @click="confirm($event)">Unpublish</button>
                </form>
            </section>

            <section class="uh-panel">
                <h2 class="uh-h4">Listings in this project</h2>
                <p class="mt-2 uh-numeric text-3xl font-semibold leading-none">{{ $model->properties()->count() }}</p>
                <a class="uh-link mt-3 inline-block text-xs" href="{{ route('admin.properties.index') }}">Manage properties</a>
            </section>
        </div>
    @endif
</div>
