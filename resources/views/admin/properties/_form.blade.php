@php
    $exists = $property->exists;
    $canEdit ??= true;
    $checklist ??= [];
    $selectedAmenities = array_map('intval', (array) old('amenity_ids', $property->amenity_ids ?? []));
    $profiles = $types->mapWithKeys(fn ($type) => [$type->id => $type->field_profile])->all();
    $status = $exists ? $property->editorialStatus() : \App\Models\PublicationState::DRAFT;
    $user = auth()->user();
@endphp

<x-ui.page-header compact :title="$exists ? $property->title : 'New property'"
                  :description="$exists ? 'Reference '.($property->reference ?? '—').'. Version '.$property->version.'.' : 'Save a draft first, then add photographs, floor plans and units.'">
    <x-slot:eyebrow>{{ $exists ? 'Edit property' : 'New property' }}</x-slot:eyebrow>
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

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ $exists ? route('admin.properties.update', $property) : route('admin.properties.store') }}"
          class="space-y-6 lg:col-span-2" x-data="uhPropertyForm({ profiles: @js($profiles), typeId: @js((string) old('property_type_id', $property->property_type_id ?? '')), priceMode: @js(old('price_mode', $property->price_mode ?? 'fixed')) })"
          @submit="submit">
        @csrf
        @if($exists)
            @method('PUT')
            <input type="hidden" name="version" value="{{ old('version', $property->version) }}">
        @endif

        <fieldset class="space-y-6" @disabled(! $canEdit)>
            <section class="uh-panel">
                <h2 class="uh-h4">Listing basics</h2>
                <div class="mt-4 space-y-4">
                    <x-ui.input name="title" label="Title" :value="$property->title" required maxlength="255"
                                hint="What a buyer sees first. Be specific: beds, type and area." />
                    @if($canEditReference)
                        <x-ui.input name="reference" label="Reference" :value="$property->reference" optional maxlength="30"
                                    hint="Letters, numbers and dashes. Leave blank to generate one automatically." />
                    @endif
                    <x-ui.textarea name="description" label="Description" rows="7" :value="$property->description"
                                   hint="Plain text. Line breaks are preserved on the public page." />
                </div>
            </section>

            <section class="uh-panel">
                <h2 class="uh-h4">Classification</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-ui.select name="property_type_id" label="Property type" required x-model="typeId">
                        <option value="">Choose a type</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected(old('property_type_id', $property->property_type_id) == $type->id)>{{ $type->label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="location_area_id" label="Area" required>
                        <option value="">Choose an area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" @selected(old('location_area_id', $property->location_area_id) == $area->id)>{{ $area->name }} — {{ $area->city }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="listing_type" label="Purpose" required>
                        <option value="sale" @selected(old('listing_type', $property->listing_type) === 'sale')>For sale</option>
                        <option value="rent" @selected(old('listing_type', $property->listing_type) === 'rent')>For rent</option>
                    </x-ui.select>
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
                    <x-ui.select name="project_id" label="Part of a project" optional>
                        <option value="">Standalone listing</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected(old('project_id', $property->project_id) == $project->id)>{{ $project->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="assigned_contact_id" label="Listing contact" optional>
                        <option value="">Main sales line</option>
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}" @selected(old('assigned_contact_id', $property->assigned_contact_id) == $contact->id)>{{ $contact->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </section>

            <section class="uh-panel">
                <h2 class="uh-h4">Pricing</h2>
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

            <section class="uh-panel">
                <h2 class="uh-h4">Size and layout</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="area_value" label="Area" type="number" step="0.01" min="1" inputmode="decimal" required
                                :value="$property->area_value" />
                    <x-ui.select name="area_unit" label="Area unit" required>
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

                @if($amenities->isNotEmpty())
                    <fieldset class="mt-5 border-t border-line pt-5">
                        <legend class="uh-legend">Amenities</legend>
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

            <section class="uh-panel">
                <h2 class="uh-h4">Location and links</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="address" label="Address" :value="$property->address" optional maxlength="255" class="sm:col-span-2"
                                hint="Shown publicly according to the address display setting." />
                    <x-ui.input name="lat" label="Latitude" type="number" step="0.0000001" dir="ltr"
                                :value="$property->lat" optional hint="Within Bangladesh, e.g. 23.7806" />
                    <x-ui.input name="lng" label="Longitude" type="number" step="0.0000001" dir="ltr"
                                :value="$property->lng" optional hint="Within Bangladesh, e.g. 90.4074" />
                    <x-ui.input name="video_url" label="Video URL" type="url" dir="ltr" :value="$property->video_url" optional hint="YouTube or Vimeo link." />
                    <x-ui.input name="virtual_tour_url" label="Virtual tour URL" type="url" dir="ltr" :value="$property->virtual_tour_url" optional />
                    <x-ui.input name="trust_label" label="Trust label" :value="$property->trust_label" optional maxlength="120"
                                class="sm:col-span-2" hint="A verifiable fact only, e.g. “Registered deed available”." />
                </div>
            </section>

            <section class="uh-panel">
                <h2 class="uh-h4">Placement</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="display_priority" label="Display priority" type="number" min="1" max="999" inputmode="numeric"
                                :value="$property->display_priority" optional hint="1 shows first in featured sections. Leave blank for newest-first." />
                    <div class="uh-field justify-end">
                        <input type="hidden" name="is_featured" value="0">
                        <label class="uh-check">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $property->is_featured))>
                            <span>Feature on the homepage</span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="uh-panel">
                <h2 class="uh-h4">Search engine listing</h2>
                <p class="mt-1 text-sm text-[var(--color-muted)]">Leave these blank to use the title and description above.</p>
                <div class="mt-4 space-y-4">
                    <x-ui.input name="meta_title" label="Meta title" maxlength="70" optional
                                :value="$property->seoOverride?->meta_title" hint="Up to 70 characters." />
                    <x-ui.textarea name="meta_description" label="Meta description" rows="2" maxlength="160" optional
                                   :value="$property->seoOverride?->meta_description" hint="Up to 160 characters." />
                    <input type="hidden" name="noindex" value="0">
                    <label class="uh-check">
                        <input type="checkbox" name="noindex" value="1" @checked(old('noindex', $property->seoOverride?->noindex))>
                        <span>Hide this page from search engines</span>
                    </label>
                </div>
            </section>

            @if($canEdit)
                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="uh-btn-primary" :disabled="submitting">
                        <span class="uh-spinner" x-show="submitting" x-cloak></span>
                        <span>{{ $exists ? 'Save changes' : 'Save draft' }}</span>
                    </button>
                    <a class="uh-btn-ghost" href="{{ route('admin.properties.index') }}">Cancel</a>
                </div>
            @endif
        </fieldset>
    </form>

    @if($exists)
        <div class="space-y-6">
            @include('admin.partials.publication-panel', ['model' => $property, 'routePrefix' => 'admin.properties', 'noun' => 'listing', 'status' => $status, 'checklist' => $checklist])

            @can('updateAvailability', $property)
                <section class="uh-panel" aria-labelledby="availability-heading" x-data="{ value: @js($property->availability) }">
                    <h2 id="availability-heading" class="uh-h4">Availability</h2>
                    @if($property->availability === 'reserved' && $property->reservation_expires_at)
                        <p class="mt-2 text-xs text-[var(--color-muted)]">Reserved until {{ $property->reservation_expires_at->timezone(config('urbanhaven.display_timezone'))->format('j M Y, g:i a') }}. It returns to available automatically.</p>
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
                        <div x-show="value === 'reserved'" x-cloak>
                            <x-ui.input name="reservation_expires_at" label="Reserved until" type="datetime-local" optional
                                        hint="Defaults to {{ config('urbanhaven.inventory.reservation_days') }} days from now." />
                        </div>
                        <x-ui.input name="note" label="Note" optional maxlength="255" id="availability-note" />
                        <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Update availability</button>
                    </form>
                </section>
            @endcan

            @include('admin.partials.media-manager', ['owner' => $property, 'ownerType' => 'property', 'canEdit' => $canEdit])

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
                    <p class="mt-4 text-xs text-[var(--color-muted)]">Add units when a single listing covers several apartments with their own prices.</p>
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
