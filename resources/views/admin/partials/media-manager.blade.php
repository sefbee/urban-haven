@php
    $collections ??= ['gallery' => 'Photographs', 'floor_plan' => 'Floor plans', 'brochure' => 'Brochures'];
    $owner->loadMissing('media');
@endphp

@foreach($collections as $collection => $heading)
    @php($items = $owner->media->where('collection', $collection)->sortBy('sort_order'))
    <section class="uh-panel" aria-labelledby="media-{{ $collection }}-heading">
        <h2 id="media-{{ $collection }}-heading" class="uh-h4">{{ $heading }}</h2>
        @if($collection === 'gallery')
            <p class="mt-1 text-xs text-[var(--color-muted)]">Images stay private until they have alt text. The cover is used on cards and social previews.</p>
        @endif

        @if($items->isNotEmpty())
            <ul class="mt-4 space-y-3">
                @foreach($items as $item)
                    <li class="rounded-lg border border-line p-2">
                        <div class="flex gap-3">
                            @if($item->isDocument())
                                <span class="flex size-16 shrink-0 items-center justify-center rounded-md bg-sand"><x-icon name="document" class="size-6" /></span>
                            @else
                                <img src="{{ $item->thumbUrl() }}" alt="" class="size-16 shrink-0 rounded-md object-cover" loading="lazy" decoding="async">
                            @endif
                            <div class="min-w-0 flex-1 text-xs">
                                <p class="truncate font-medium">{{ $item->original_filename }}</p>
                                <p class="mt-0.5 text-[var(--color-muted)]">
                                    {{ $item->is_public ? 'Public' : 'Private' }}
                                    @if($owner->featured_media_id === $item->id) · Cover @endif
                                </p>
                            </div>
                        </div>
                        @if($canEdit)
                            <form method="POST" action="{{ route('admin.media.update', $item) }}" class="mt-2 space-y-2">
                                @csrf
                                @method('PATCH')
                                <x-ui.input name="alt_text" :label="$item->isDocument() ? 'Title' : 'Alt text'" :value="$item->alt_texts['en'] ?? ''" maxlength="200" :id="'alt-'.$item->id" />
                                <input type="hidden" name="is_public" value="0">
                                <label class="uh-check text-xs"><input type="checkbox" name="is_public" value="1" @checked($item->is_public)> <span>Public</span></label>
                                @if($collection === 'gallery')
                                    <label class="uh-check text-xs"><input type="checkbox" name="make_cover" value="1"> <span>Use as cover</span></label>
                                @endif
                                <div class="flex gap-2">
                                    <button type="submit" class="uh-btn-outline uh-btn-sm">Save</button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('admin.media.destroy', $item) }}" class="mt-2" x-data="uhConfirm('Remove this file?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="uh-btn-ghost uh-btn-sm px-0 text-[var(--color-danger)]" @click="confirm($event)">Remove</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-4 rounded-lg border border-dashed border-line-strong px-4 py-5 text-center text-xs text-[var(--color-muted)]">None yet.</p>
        @endif

        @if($canEdit)
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="mt-4 space-y-2">
                @csrf
                <input type="hidden" name="owner_type" value="{{ $ownerType }}">
                <input type="hidden" name="owner_id" value="{{ $owner->id }}">
                <input type="hidden" name="collection" value="{{ $collection }}">
                <label class="uh-label" for="upload-{{ $collection }}">Add {{ strtolower($heading) === 'brochures' ? 'a brochure (PDF)' : 'an image' }}</label>
                <input id="upload-{{ $collection }}" class="uh-input py-2 text-xs" type="file" name="file" required
                       accept="{{ $collection === 'brochure' ? 'application/pdf' : 'image/jpeg,image/png,image/webp' }}">
                @if($collection !== 'brochure')
                    <x-ui.input name="alt_text" label="Alt text" maxlength="200" optional :id="'upload-alt-'.$collection" hint="Describe what the image shows." />
                @endif
                <button type="submit" class="uh-btn-outline uh-btn-sm">Upload</button>
            </form>
        @endif
    </section>
@endforeach
