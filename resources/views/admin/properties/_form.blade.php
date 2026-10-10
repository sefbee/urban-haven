@php
    $exists = $property->exists;
    $canEdit ??= true;
    $saveLabel ??= 'Save changes';
    $checklist ??= [];
    $selectedAmenities = array_map('intval', (array) old('amenity_ids', $property->amenity_ids ?? []));
    $profiles = $types->mapWithKeys(fn ($type) => [$type->id => $type->field_profile])->all();
    $status = $exists ? $property->editorialStatus() : \App\Models\PublicationState::DRAFT;
    $user = auth()->user();
    $steps = ['what' => 'What', 'where' => 'Where', 'describe' => 'Describe', 'price' => 'Price', 'size' => 'Size', 'features' => 'Features', 'media' => 'Media', 'visibility' => 'Visibility', 'seo' => 'SEO'];
@endphp

<x-ui.page-header compact :title="$exists ? $property->title : 'New property'">
    <x-slot:eyebrow>Content library · {{ $exists ? 'Edit property' : 'New property' }}</x-slot:eyebrow>
    <x-slot:actions>
        <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.properties.index') }}">
            <x-icon name="chevron-left" class="size-4" />
            All properties
        </a>
        @if($exists && $property->isPublished())
            <a class="uh-btn-outline uh-btn-sm" href="{{ route('properties.show', $property->slug) }}" target="_blank" rel="noopener">
                <x-icon name="external" class="size-4" />
                View live
            </a>
        @endif
    </x-slot:actions>
</x-ui.page-header>

@if($exists && ! $canEdit)
    <x-ui.alert tone="info" class="mt-6">
        This listing is live. Only a publisher can change its content; you can still update availability below.
    </x-ui.alert>
@endif

<nav class="dd-steps" aria-label="Form sections">
    @foreach($steps as $anchor => $label)
        @continue($anchor === 'media' && ($exists || ! $canEdit))
        <a href="#step-{{ $anchor }}">{{ $label }}</a>
    @endforeach
</nav>

<div @class(['uh-admin-compose', 'is-split' => $exists])>
    <form method="POST" action="{{ $exists ? route('admin.properties.update', $property) : route('admin.properties.store') }}"
          class="uh-admin-compose-main" enctype="multipart/form-data" data-unsaved-guard
          x-data="uhPropertyForm({ profiles: @js($profiles), typeId: @js((string) old('property_type_id', $property->property_type_id ?? '')), priceMode: @js(old('price_mode', $property->price_mode ?? 'fixed')) })"
          @submit="submit">
        @csrf
        @if($exists)
            @method('PUT')
            <input type="hidden" name="version" value="{{ old('version', $property->version) }}">
        @endif

        <fieldset class="grid gap-4" @disabled(! $canEdit)>
            <div class="uh-admin-stack dd-form-steps">
            <section class="uh-panel" id="step-what">
                <h2 class="uh-h4">What are you listing?</h2>
                <p class="dd-step-hint">Purpose and type decide which fields appear below.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-ui.select name="listing_type" label="Purpose" required>
                        @foreach(\App\Models\Setting::enabledPurposes() ?: ['sale', 'rent'] as $purpose)
                            <option value="{{ $purpose }}" @selected(old('listing_type', $property->listing_type) === $purpose)>{{ $purpose === 'rent' ? 'For rent' : 'For sale' }}</option>
                        @endforeach
                        @if($exists && ! in_array($property->listing_type, \App\Models\Setting::enabledPurposes(), true))
                            <option value="{{ $property->listing_type }}" selected>{{ $property->listing_type === 'rent' ? 'For rent' : 'For sale' }} (switched off on the website)</option>
                        @endif
                    </x-ui.select>
                    <x-ui.select name="property_type_id" label="Property type" required x-model="typeId" quick-add="type"
                                 hint="Grouped by main type.">
                        <option value="">Choose a type</option>
                        @foreach(\App\Models\PropertyType::categoryLabels() as $category => $categoryLabel)
                            @if($types->where('category', $category)->isNotEmpty())
                                <optgroup label="{{ $categoryLabel }}">
                                    @foreach($types->where('category', $category) as $type)
                                        <option value="{{ $type->id }}" @selected(old('property_type_id', $property->property_type_id) == $type->id)>{{ $type->label }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="project_id" label="Part of a project" optional quick-add="project" class="sm:col-span-2"
                                 hint="Link it when the listing is a unit in one of your developments.">
                        <option value="">Standalone listing</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected(old('project_id', $property->project_id) == $project->id)>{{ $project->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </section>

            <section class="uh-panel" id="step-where">
                <h2 class="uh-h4">Where is it?</h2>
                <p class="dd-step-hint">Country, then city, then area. Only areas in the chosen city are offered.</p>
                <div class="mt-4 space-y-4">
                    @include('admin.partials.place-picker', ['areas' => $areas, 'selectedArea' => $property->location_area_id, 'idPrefix' => 'property-place'])
                    <x-ui.input name="address" label="Street address" :value="$property->address" optional maxlength="255"
                                hint="House, road and block. Shown publicly according to the address display setting." />
                    <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="lat" label="Map pin latitude" type="number" step="0.0000001" dir="ltr"
                                    :value="$property->lat" optional placeholder="23.7806" />
                    <x-ui.input name="lng" label="Map pin longitude" type="number" step="0.0000001" dir="ltr"
                                    :value="$property->lng" optional placeholder="90.4074" />
                    </div>
                </div>
            </section>

            <section class="uh-panel" id="step-describe">
                <h2 class="uh-h4">Describe it</h2>
                <p class="dd-step-hint">Lead with what a buyer searches for: bedrooms, type and area.</p>
                <div class="mt-4 space-y-4">
                    <x-ui.input name="title" label="Title" :value="$property->title" required maxlength="255"
                                placeholder="e.g. 3-bed apartment with lake view in Gulshan 2"
                                hint="What a buyer sees first. The web address is generated from it." />
                    <x-ui.rich-text name="description" label="Description" :value="$property->description" min-height="14rem"
                                    hint="Format the listing description with headings, emphasis, lists, quotes and links." />
                    @if($canEditReference)
                        <x-ui.input name="reference" label="Reference" :value="$property->reference" optional maxlength="30" dir="ltr"
                                    hint="Letters, numbers and dashes. Leave blank to generate one automatically." />
                    @endif
                </div>
            </section>

            <section class="uh-panel" id="step-price">
                <h2 class="uh-h4">Price</h2>
                <fieldset class="mt-4">
                    <legend class="uh-legend">Price display</legend>
                    <div class="flex flex-wrap gap-x-6 gap-y-2">
                        <label class="uh-check"><input type="radio" name="price_mode" value="fixed" x-model="priceMode"> <span>Show a price</span></label>
                        <label class="uh-check"><input type="radio" name="price_mode" value="on_request" x-model="priceMode"> <span>Price on request</span></label>
                    </div>
                    @error('price_mode')<p class="uh-error">{{ $message }}</p>@enderror
                </fieldset>
                <div class="mt-4 grid gap-4 sm:grid-cols-2" x-show="priceMode === 'fixed'">
                    <x-ui.input name="price" label="Price (BDT)" type="number" step="1" min="1" inputmode="numeric"
                                :value="$property->price !== null ? (int) $property->price : ''" hint="Total price for sale, monthly rent for rentals." />
                </div>
            </section>

            <section class="uh-panel" id="step-size">
                <h2 class="uh-h4">Size and layout</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="area_value" label="Size" type="number" step="0.01" min="1" inputmode="decimal" required
                                :value="$property->area_value" />
                    <x-ui.select name="area_unit" label="Size unit" required>
                        @foreach(config('urbanhaven.area_units') as $unit => $meta)
                            <option value="{{ $unit }}" @selected(old('area_unit', $property->area_unit) === $unit)>{{ $meta['label'] }}</option>
                        @endforeach
                    </x-ui.select>
                    <fieldset class="contents" x-show="! isPlot" :disabled="isPlot">
                            <x-ui.input name="bedrooms" label="Bedrooms" type="number" min="0" max="50" inputmode="numeric" :value="$property->bedrooms" optional />
                            <x-ui.input name="bathrooms" label="Bathrooms" type="number" min="0" max="50" inputmode="numeric" :value="$property->bathrooms" optional />
                            <x-ui.input name="balconies" label="Balconies" type="number" min="0" max="50" inputmode="numeric" :value="$property->balconies" optional />
                            <x-ui.input name="floor_number" label="Floor" type="number" min="-5" max="200" inputmode="numeric" :value="$property->floor_number" optional />
                    </fieldset>
                    <x-ui.input name="parking_spaces" label="Parking spaces" type="number" min="0" max="500" inputmode="numeric" :value="$property->parking_spaces" optional />
                    <x-ui.select name="facing" label="Facing" optional>
                        <option value="">Not specified</option>
                        @foreach(config('urbanhaven.facings') as $value => $label)
                            <option value="{{ $value }}" @selected(old('facing', $property->facing) === $value)>{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input name="road_width_ft" label="Road width (ft)" type="number" min="0" max="200" inputmode="numeric"
                                :value="$property->road_width_ft" optional />
                    <div class="uh-field justify-end" x-show="! isPlot">
                        <input type="hidden" name="is_furnished" value="0">
                        <label class="uh-check">
                            <input type="checkbox" name="is_furnished" value="1" @checked(old('is_furnished', $property->is_furnished))>
                            <span>Furnished</span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="uh-panel" id="step-features">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="uh-h4">Features and amenities</h2>
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
                @error('amenity_ids')<p class="uh-error">{{ $message }}</p>@enderror
            </section>

            @if(! $exists && $canEdit)
                <div id="step-media">
                    @include('admin.partials.photograph-field')
                </div>
            @endif

            <section class="uh-panel" @if($exists || ! $canEdit) id="step-media" @endif>
                <h2 class="uh-h4">Video, tour and trust</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="video_url" label="Video URL" type="url" dir="ltr" :value="$property->video_url" optional hint="YouTube or Vimeo link." />
                    <x-ui.input name="virtual_tour_url" label="Virtual tour URL" type="url" dir="ltr" :value="$property->virtual_tour_url" optional />
                    <x-ui.input name="trust_label" label="Trust label" :value="$property->trust_label" optional maxlength="120"
                                class="sm:col-span-2" hint="A verifiable fact only, e.g. “Registered deed available”." />
                </div>
                @if($exists)
                @endif
            </section>

            <section class="uh-panel" id="step-visibility">
                <h2 class="uh-h4">Availability, contact and placement</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @if($exists)
                        <input type="hidden" name="availability" value="{{ $property->availability }}">
                        <div class="uh-field">
                            <span class="uh-label">Availability</span>
                            <p class="flex min-h-10 items-center gap-2 text-sm"><x-ui.status :status="$property->availability" /> <span class="text-xs text-[var(--color-muted)]">Change it in the Availability panel.</span></p>
                        </div>
                    @else
                        <x-ui.select name="availability" label="Availability" required>
                            @foreach(\App\Models\Property::AVAILABILITY_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected(old('availability', $property->availability) === $value)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    @endif
                    <x-ui.select name="assigned_contact_id" label="Listing contact" optional hint="Enquiries for this listing go to this person.">
                        <option value="">Main sales line</option>
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}" @selected(old('assigned_contact_id', $property->assigned_contact_id) == $contact->id)>{{ $contact->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <div class="uh-field justify-end">
                        <input type="hidden" name="is_featured" value="0">
                        <label class="uh-check">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $property->is_featured))>
                            <span>Feature on the homepage</span>
                        </label>
                    </div>
                    <x-ui.input name="display_priority" label="Display priority" type="number" min="1" max="999" inputmode="numeric"
                                :value="$property->display_priority" optional hint="1 shows first in featured sections. Leave blank for newest-first." />
                </div>
            </section>

            <div id="step-seo">
                @include('admin.partials.seo-panel', [
                    'seo' => $property->seoOverride,
                    'slugName' => 'slug',
                    'slug' => $property->slug,
                    'baseUrl' => url('/properties'),
                    'titleSource' => 'title',
                    'contentSource' => 'description',
                    'idPrefix' => 'property-seo',
                ])
            </div>

            </div>
            <div class="uh-admin-dock">
                <button type="submit" class="uh-btn-primary" :disabled="submitting" @disabled(! $canEdit)>
                    <span class="uh-spinner" x-show="submitting" x-cloak></span>
                    <span>{{ $saveLabel }}</span>
                </button>
                <a class="uh-btn-ghost" href="{{ route('admin.properties.index') }}">Cancel</a>
            </div>
        </fieldset>
    </form>

    @if($exists)
        <div class="uh-admin-compose-side">
            @include('admin.partials.publication-panel', ['model' => $property, 'routePrefix' => 'admin.properties', 'noun' => 'listing', 'status' => $status, 'checklist' => $checklist])

            @can('updateAvailability', $property)
                <section class="uh-panel" aria-labelledby="availability-heading" x-data="{ value: @js($property->availability) }">
                    <h2 id="availability-heading" class="uh-h4">Availability</h2>
                    @if($property->availability === 'reserved' && $property->reservation_expires_at)
                    @endif
                    <form method="POST" action="{{ route('admin.properties.availability', $property) }}" class="mt-3 space-y-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="version" value="{{ $property->version }}">
                        <x-ui.select name="availability" label="Status" x-model="value" id="availability-status">
                            @foreach(\App\Models\Property::AVAILABILITY_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected($property->availability === $value)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                        <div class="uh-field" x-show="value === 'reserved'" x-cloak>
                            <label class="uh-label" for="f-reservation-expires-at">
                                {{ __('Reserved until') }}
                                <span class="uh-label-optional">{{ __('optional') }}</span>
                            </label>
                            <input id="f-reservation-expires-at" type="datetime-local"
                                   ::name="value === 'reserved' ? 'reservation_expires_at' : ''"
                                   class="uh-input">
                            <p class="uh-hint">Defaults to {{ config('urbanhaven.inventory.reservation_days') }} days from now.</p>
                        </div>
                        <x-ui.input name="note" label="Note" optional maxlength="255" id="availability-note" />
                        <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Update availability</button>
                    </form>
                </section>
            @endcan

            <section class="uh-panel">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="uh-h4">Units</h2>
                    @if($canEdit)
                        <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.units.create', $property) }}">
                            <x-icon name="plus" class="size-3.5" />
                            Add
                        </a>
                    @endif
                </div>

                @if($property->units->isNotEmpty())
                    <ul class="mt-4 divide-y divide-[var(--color-line)] text-sm">
                        @foreach($property->units as $unit)
                            <li class="flex items-center justify-between gap-3 py-2.5">
                                <div class="min-w-0">
                                    <p class="font-medium">{{ $unit->unit_number }}</p>
                                    <p class="uh-numeric text-xs text-[var(--color-muted)]">
                                        {{ \App\Support\MoneyFormatter::formatBdt($unit->price, $property->price_basis) ?? '—' }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <x-ui.status :status="$unit->status" />
                                    @if($canEdit)
                                        <form method="POST" action="{{ route('admin.units.destroy', $unit) }}"
                                              x-data="uhConfirm('Delete unit {{ $unit->unit_number }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="uh-icon-btn size-8 text-[var(--color-danger)]"
                                                    @click="confirm($event)" aria-label="Delete unit {{ $unit->unit_number }}">
                                                <x-icon name="trash" class="size-3.5" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                @endif
            </section>

            <section class="uh-panel" aria-labelledby="history-heading">
                <h2 id="history-heading" class="uh-h4">Status history</h2>
                @if($property->statusHistory->isNotEmpty())
                    <ol class="mt-3 space-y-2 text-xs">
                        @foreach($property->statusHistory->sortByDesc('created_at')->take(20) as $entry)
                            <li>
                                <span class="font-medium">{{ str_replace('_', ' ', ucfirst($entry->field)) }}</span>:
                                {{ $entry->from_value ?? '—' }} → {{ $entry->to_value ?? '—' }}
                                <span class="block text-[var(--color-muted)]">
                                    {{ $entry->actor?->name ?? 'System' }} · {{ $entry->created_at?->timezone(config('urbanhaven.display_timezone'))->format('j M Y, g:i a') }}
                                    @if($entry->note) · {{ $entry->note }} @endif
                                </span>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <p class="mt-3 text-xs text-[var(--color-muted)]">No status changes recorded yet.</p>
                @endif
            </section>
        </div>
    @endif
</div>

@if($exists)
    @include('admin.partials.media-manager', ['owner' => $property, 'ownerType' => 'property', 'canEdit' => $canEdit])
@endif
