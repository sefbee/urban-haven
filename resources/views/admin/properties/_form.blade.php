@php
    $model = $property ?? null;
    $selectedAmenities = array_map('intval', (array) old('amenity_ids', $model->amenity_ids ?? []));
@endphp

<x-ui.page-header compact :title="$model->title ?? 'New property'"
                  :description="$model ? 'Reference '.($model->reference ?? '—').'. Changes are saved as a draft until you publish.' : 'Fill in what you know now. You can add photographs and units after saving.'">
    <x-slot:eyebrow>{{ $model ? 'Edit property' : 'New property' }}</x-slot:eyebrow>
    <x-slot:actions>
        <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.properties.index') }}">
            <x-icon name="chevron-left" class="size-4" />
            All properties
        </a>
        @if($model && $model->isPublished())
            <a class="uh-btn-outline uh-btn-sm" href="{{ route('properties.show', $model->slug) }}">
                <x-icon name="external" class="size-4" />
                View live
            </a>
        @endif
    </x-slot:actions>
</x-ui.page-header>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ $model ? route('admin.properties.update', $model) : route('admin.properties.store') }}"
          class="space-y-6 lg:col-span-2" x-data="uhForm" @submit="submit">
        @csrf
        @if($model) @method('PUT') @endif

        {{-- Basics --}}
        <section class="uh-panel">
            <h2 class="uh-h4">Listing basics</h2>
            <div class="mt-4 space-y-4">
                <x-ui.input name="title" label="Title" :value="$model->title ?? ''" required
                            hint="What a buyer sees first. Be specific: beds, type and area." />
                <x-ui.textarea name="description" label="Description" rows="7" :value="$model->description ?? ''"
                               hint="Plain text. Line breaks are preserved on the public page." />
            </div>
        </section>

        {{-- Classification --}}
        <section class="uh-panel">
            <h2 class="uh-h4">Classification</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-ui.select name="property_type_id" label="Property type" required>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" @selected(old('property_type_id', $model->property_type_id ?? '') == $type->id)>{{ $type->label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="location_area_id" label="Area" required>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected(old('location_area_id', $model->location_area_id ?? '') == $area->id)>{{ $area->name }} — {{ $area->city }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="listing_type" label="Listing type" required>
                    <option value="sale" @selected(old('listing_type', $model->listing_type ?? 'sale') === 'sale')>For sale</option>
                    <option value="rent" @selected(old('listing_type', $model->listing_type ?? '') === 'rent')>For rent</option>
                </x-ui.select>
                <x-ui.select name="availability" label="Availability" required>
                    @foreach(['available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold', 'rented' => 'Rented', 'off-market' => 'Off market'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('availability', $model->availability ?? 'available') === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="project_id" label="Part of a project" optional class="sm:col-span-2">
                    <option value="">Standalone listing</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id', $model->project_id ?? '') == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </section>

        {{-- Pricing --}}
        <section class="uh-panel">
            <h2 class="uh-h4">Pricing</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-ui.input name="price" label="Price" type="number" step="0.01" min="0" inputmode="decimal"
                            :value="$model->price ?? ''" hint="In BDT. Leave blank for “price on request”." />
                <x-ui.select name="price_basis" label="Price basis" required>
                    <option value="total_sale" @selected(old('price_basis', $model->price_basis ?? 'total_sale') === 'total_sale')>Total sale price</option>
                    <option value="monthly_rent" @selected(old('price_basis', $model->price_basis ?? '') === 'monthly_rent')>Monthly rent</option>
                </x-ui.select>
            </div>
        </section>

        {{-- Size and layout --}}
        <section class="uh-panel">
            <h2 class="uh-h4">Size and layout</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-ui.input name="area_value" label="Area" type="number" step="0.01" min="0" inputmode="decimal"
                            :value="$model->area_value ?? ''" />
                <x-ui.select name="area_unit" label="Area unit" required>
                    @foreach(config('urbanhaven.area_units') as $unit => $meta)
                        <option value="{{ $unit }}" @selected(old('area_unit', $model->area_unit ?? 'sqft') === $unit)>{{ $meta['label'] }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="bedrooms" label="Bedrooms" type="number" min="0" inputmode="numeric" :value="$model->bedrooms ?? ''" />
                <x-ui.input name="bathrooms" label="Bathrooms" type="number" min="0" inputmode="numeric" :value="$model->bathrooms ?? ''" />
                <x-ui.input name="floor_number" label="Floor" type="number" inputmode="numeric" :value="$model->floor_number ?? ''" optional />
                <div class="uh-field justify-end">
                    <input type="hidden" name="is_furnished" value="0">
                    <label class="uh-check">
                        <input type="checkbox" name="is_furnished" value="1" @checked(old('is_furnished', $model->is_furnished ?? false))>
                        <span>Furnished</span>
                    </label>
                    <input type="hidden" name="is_featured" value="0">
                    <label class="uh-check">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $model->is_featured ?? false))>
                        <span>Feature on the homepage</span>
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

        {{-- Location and media links --}}
        <section class="uh-panel">
            <h2 class="uh-h4">Location and links</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-ui.input name="lat" label="Latitude" type="number" step="0.0000001" dir="ltr"
                            :value="$model->lat ?? ''" optional hint="Example: 23.7806" />
                <x-ui.input name="lng" label="Longitude" type="number" step="0.0000001" dir="ltr"
                            :value="$model->lng ?? ''" optional hint="Example: 90.4074" />
                <x-ui.input name="video_url" label="Video URL" type="url" dir="ltr" :value="$model->video_url ?? ''" optional />
                <x-ui.input name="virtual_tour_url" label="Virtual tour URL" type="url" dir="ltr" :value="$model->virtual_tour_url ?? ''" optional />
                <x-ui.input name="trust_label" label="Trust label" :value="$model->trust_label ?? ''" optional
                            class="sm:col-span-2" hint="A verifiable fact only, e.g. “Registered deed available”." />
            </div>
        </section>

        {{-- SEO --}}
        <section class="uh-panel">
            <h2 class="uh-h4">Search engine listing</h2>
            <p class="mt-1 text-sm text-[var(--color-muted)]">Leave these blank to use the title and description above.</p>
            <div class="mt-4 space-y-4">
                <x-ui.input name="meta_title" label="Meta title" maxlength="70" optional
                            :value="$model?->seoOverride?->meta_title" hint="Up to 70 characters." />
                <x-ui.textarea name="meta_description" label="Meta description" rows="2" maxlength="160" optional
                               :value="$model?->seoOverride?->meta_description" hint="Up to 160 characters." />
                <input type="hidden" name="noindex" value="0">
                <label class="uh-check">
                    <input type="checkbox" name="noindex" value="1" @checked(old('noindex', $model?->seoOverride?->noindex))>
                    <span>Hide this page from search engines</span>
                </label>
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                <span x-text="submitting ? 'Saving…' : '{{ $model ? 'Save changes' : 'Create property' }}'">{{ $model ? 'Save changes' : 'Create property' }}</span>
            </button>
            <a class="uh-btn-ghost" href="{{ route('admin.properties.index') }}">Cancel</a>
        </div>
    </form>

    @if($model)
        <div class="space-y-6">
            {{-- Publication --}}
            <section class="uh-panel">
                <h2 class="uh-h4">Publication</h2>
                <p class="mt-2 flex items-center gap-2 text-sm">
                    <x-ui.status :status="$model->editorialStatus()" />
                </p>
                <p class="mt-3 text-xs leading-relaxed text-[var(--color-muted)]">
                    A listing moves from draft to review, then approval, then publishing. Only published listings appear on the public site.
                </p>

                <div class="mt-4 space-y-2">
                    <form method="POST" action="{{ route('admin.properties.submit', $model) }}">
                        @csrf
                        <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Submit for review</button>
                    </form>
                    <form method="POST" action="{{ route('admin.properties.approve', $model) }}">
                        @csrf
                        <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('admin.properties.publish', $model) }}">
                        @csrf
                        <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block">Publish</button>
                    </form>
                </div>

                <form method="POST" action="{{ route('admin.properties.unpublish', $model) }}"
                      class="mt-4 space-y-2 border-t border-line pt-4"
                      x-data="uhConfirm('Unpublish this listing? It will disappear from the public site immediately.')">
                    @csrf
                    <x-ui.input name="unpublish_reason" label="Reason for unpublishing" required
                                hint="Recorded in the audit log." />
                    <button type="submit" class="uh-btn-danger uh-btn-sm uh-btn-block" @click="confirm($event)">Unpublish</button>
                </form>
            </section>

            {{-- Gallery --}}
            <section class="uh-panel" x-data="{
                uploading: false,
                async upload(event) {
                    const file = event.target.files[0];
                    if (! file) return;
                    this.uploading = true;
                    const body = new FormData();
                    body.append('file', file);
                    body.append('owner_type', 'property');
                    body.append('owner_id', '{{ $model->id }}');
                    body.append('collection', 'gallery');
                    body.append('_token', '{{ csrf_token() }}');
                    await fetch('{{ route('admin.media.store') }}', { method: 'POST', body, headers: { 'Accept': 'application/json' } });
                    window.location.reload();
                }
            }">
                <h2 class="uh-h4">Photographs</h2>
                <p class="mt-1 text-xs text-[var(--color-muted)]">The first image is used as the cover on cards and social previews.</p>

                @if($model->media->isNotEmpty())
                    <ul class="mt-4 grid grid-cols-2 gap-2">
                        @foreach($model->media as $image)
                            <li class="group relative overflow-hidden rounded-lg ring-1 ring-line">
                                <img src="{{ $image->thumbUrl() }}" alt="{{ $image->alt(app()->getLocale()) }}"
                                     class="h-24 w-full object-cover" loading="lazy" decoding="async">
                                <form method="POST" action="{{ route('admin.media.destroy', $image) }}" class="absolute right-1 top-1"
                                      x-data="uhConfirm('Remove this photograph?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" @click="confirm($event)"
                                            class="inline-flex size-7 items-center justify-center rounded-md bg-ink/80 text-cream transition hover:bg-ink"
                                            aria-label="Remove photograph">
                                        <x-icon name="trash" class="size-3.5" />
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 rounded-lg border border-dashed border-line-strong px-4 py-6 text-center text-xs text-[var(--color-muted)]">
                        No photographs yet.
                    </p>
                @endif

                <div class="uh-field mt-4">
                    <label class="uh-label" for="media-upload">
                        <span x-show="! uploading">Add a photograph</span>
                        <span x-show="uploading" x-cloak>Uploading…</span>
                    </label>
                    <input id="media-upload" class="uh-input py-2 text-xs" type="file"
                           accept="image/jpeg,image/png,image/webp,image/gif" @change="upload" :disabled="uploading">
                    <p class="uh-hint">JPEG, PNG, WebP or GIF up to {{ (int) (config('urbanhaven.media.max_image_kb') / 1024) }} MB.</p>
                </div>
            </section>

            {{-- Units --}}
            <section class="uh-panel">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="uh-h4">Units</h2>
                    <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.units.create', $model) }}">
                        <x-icon name="plus" class="size-3.5" />
                        Add
                    </a>
                </div>

                @if($model->units->isNotEmpty())
                    <ul class="mt-4 divide-y divide-[var(--color-line)] text-sm">
                        @foreach($model->units as $unit)
                            <li class="flex items-center justify-between gap-3 py-2.5">
                                <div class="min-w-0">
                                    <p class="font-medium">{{ $unit->unit_number }}</p>
                                    <p class="uh-numeric text-xs text-[var(--color-muted)]">
                                        {{ \App\Support\MoneyFormatter::formatBdt($unit->price, $model->price_basis) ?? '—' }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <x-ui.status :status="$unit->status" />
                                    <form method="POST" action="{{ route('admin.units.destroy', $unit) }}"
                                          x-data="uhConfirm('Delete unit {{ $unit->unit_number }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="uh-icon-btn size-8 text-[var(--color-danger)]"
                                                @click="confirm($event)" aria-label="Delete unit {{ $unit->unit_number }}">
                                            <x-icon name="trash" class="size-3.5" />
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-xs text-[var(--color-muted)]">
                        Add units when a single listing covers several apartments with their own prices.
                    </p>
                @endif
            </section>
        </div>
    @endif
</div>
