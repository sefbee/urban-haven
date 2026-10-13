@php
    $collections ??= ['gallery' => 'Photographs', 'floor_plan' => 'Floor plans', 'brochure' => 'Brochures'];
    $hints ??= [];
    $owner->loadMissing('media');
    $supportsCover = in_array('featured_media_id', $owner->getFillable(), true);
    $maxImageMb = max(1, (int) ceil(((int) config('urbanhaven.media.max_image_kb')) / 1024));
    $maxBrochureMb = max(1, (int) ceil(((int) config('urbanhaven.media.max_brochure_kb')) / 1024));
    $formatBytes = static function (int $bytes): string {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return max(1, (int) round($bytes / 1024)).' KB';
    };
@endphp

@foreach($collections as $collection => $heading)
    @php
        $items = $owner->media->where('collection', $collection)->sortBy('sort_order')->values();
        $isDocument = $collection === 'brochure';
        $uploadLabel = $isDocument ? 'Upload brochure' : 'Upload photographs';
        $itemsConfig = $items->map(fn ($m) => [
            'id' => $m->id,
            'alt_text' => $m->alt_texts['en'] ?? '',
            'is_public' => (bool) $m->is_public,
        ])->values();
    @endphp
    <section class="uh-panel uh-admin-media" aria-labelledby="media-{{ $collection }}-heading"
             @if($canEdit)
                 x-data="uhMediaUpload({
                     ownerType: @js($ownerType),
                     ownerId: {{ $owner->id }},
                     collection: @js($collection),
                     reorderUrl: @js(route('admin.media.reorder')),
                     batchUrl: @js(route('admin.media.batch-update')),
                     bulkDeleteUrl: @js(route('admin.media.bulk-destroy')),
                     coverId: {{ $supportsCover && $owner->featured_media_id ? $owner->featured_media_id : 'null' }},
                     itemIds: @js($items->pluck('id')->values()),
                     items: @js($itemsConfig),
                 })"
             @endif>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="uh-admin-media-heading">
                <span class="uh-admin-media-heading-icon" aria-hidden="true">
                    <x-icon :name="$isDocument ? 'document' : 'image'" class="size-4" />
                </span>
                <h2 id="media-{{ $collection }}-heading" class="uh-h4">{{ $heading }}</h2>
                <span class="text-xs text-[var(--admin-muted)]" x-text="'(' + itemIds.length + ')'">({{ $items->count() }})</span>
            </div>

            @if($canEdit && ! $isDocument)
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="uh-btn-ghost uh-btn-sm" @click="selectAllMedia()" :disabled="deletingSelected || itemIds.length === 0">
                        Select all
                    </button>
                    <button type="button" class="uh-btn-ghost uh-btn-sm" @click="selectedMediaIds = []"
                            x-show="selectedMediaIds.length > 0" x-cloak :disabled="deletingSelected">
                        Clear selection
                    </button>
                    <form method="POST" :action="bulkDeleteUrl" @submit="confirmBulkDelete($event)" class="inline-flex items-center gap-2">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="owner_type" value="{{ $ownerType }}">
                        <input type="hidden" name="owner_id" value="{{ $owner->id }}">
                        <input type="hidden" name="collection" value="{{ $collection }}">
                        <template x-for="mediaId in selectedMediaIds" :key="mediaId">
                            <input type="hidden" name="media_ids[]" :value="mediaId">
                        </template>
                        <button type="submit" class="uh-btn-outline uh-btn-sm text-[var(--dd-red)]"
                                x-show="selectedMediaIds.length > 0" x-cloak :disabled="deletingSelected">
                            <x-icon name="trash" class="size-4" />
                            <span x-text="`Remove selected (${selectedMediaIds.length})`"></span>
                        </button>
                    </form>
                </div>
            @endif

        </div>

        <p class="uh-admin-media-hint">
            <x-icon name="info" class="size-4" />
            <span>
                @if($isDocument)
                    The maximum brochure size is {{ $maxBrochureMb }} MB. Format: PDF.
                @else
                    The maximum photograph size is {{ $maxImageMb }} MB. Formats: JPEG, PNG, WebP. Add alt text before uploading so public photos can appear on the site.
                    @if($collection === 'gallery' && $supportsCover)
                        Use the cover button to designate the primary showcase image.
                    @endif
                @endif
                {{ $hints[$collection] ?? '' }}
            </span>
        </p>

        {{-- Status feedback notification --}}
        <div x-show="statusNotice" x-cloak x-transition.opacity
             class="mt-3 flex items-center justify-between gap-2 rounded-lg p-2.5 text-xs font-medium"
             :class="statusTone === 'success' ? 'bg-forest/10 text-forest border border-forest/20' : (statusTone === 'danger' ? 'bg-danger/10 text-danger border border-danger/20' : 'bg-gold/10 text-ink border border-gold/20')">
            <span x-text="statusNotice"></span>
            <button type="button" @click="statusNotice = ''" class="uh-icon-btn size-5 opacity-70 hover:opacity-100" aria-label="Dismiss notice">
                <x-icon name="close" class="size-3" />
            </button>
        </div>

        @if($canEdit)
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data"
                  class="uh-admin-media-upload" @submit="submit">
                @csrf
                <input type="hidden" name="owner_type" value="{{ $ownerType }}">
                <input type="hidden" name="owner_id" value="{{ $owner->id }}">
                <input type="hidden" name="collection" value="{{ $collection }}">
                <input id="upload-{{ $collection }}" x-ref="file" class="uh-admin-media-file" type="file" required
                       @if($isDocument) name="file" @else name="files[]" multiple @endif
                       accept="{{ $isDocument ? 'application/pdf' : 'image/jpeg,image/png,image/webp' }}"
                       aria-label="{{ $isDocument ? 'Brochure' : 'Photograph' }}"
                       @change="chosen(false)">
                <div class="uh-admin-media-drop" :class="{ 'is-dragging': dragging }"
                     @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
                     @drop.prevent="dropped($event, false)">
                    <button type="button" class="uh-admin-media-trigger" :disabled="submitting" @click="pick()">
                        <x-icon name="upload" class="size-4" />
                        <span>{{ $uploadLabel }}</span>
                    </button>
                    <span class="uh-admin-media-drop-hint">or drag {{ $isDocument ? 'the PDF' : 'photos' }} here</span>
                </div>
            </form>

            {{-- Multi-file selection review before committing --}}
            <div class="mt-3 rounded-lg border border-dashed border-[var(--color-line)] bg-[var(--color-surface)] p-3"
                 x-show="pendingFiles.length > 0" x-cloak x-transition>

                <div class="flex items-center justify-between gap-2 pb-2 mb-2 border-b border-black/10">
                    <p class="text-xs text-slate-700">
                        <strong x-text="pendingFiles.length"></strong>
                        <span x-text="pendingFiles.length === 1 ? 'photo' : 'photos'"></span> selected (saved automatically when clicking Save changes)
                        <span class="text-slate-400" x-text="'(' + filesize + ')'"></span>
                    </p>
                    <button type="button" class="text-xs text-slate-500 hover:text-red-600 transition-colors"
                            @click="clear()">
                        Clear all
                    </button>
                </div>

                <ul class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(8.5rem, 1fr));">
                    <template x-for="(pf, idx) in pendingFiles" :key="pf.id">
                        <li class="relative rounded-md border border-[var(--color-line)] bg-white p-1 text-center shadow-xs">
                            <template x-if="pf.previewUrl">
                                <img :src="pf.previewUrl" class="aspect-4/3 w-full rounded object-cover" alt="">
                            </template>
                            <template x-if="!pf.previewUrl">
                                <div class="aspect-4/3 flex items-center justify-center rounded bg-slate-100">
                                    <x-icon :name="$isDocument ? 'document' : 'image'" class="size-6 text-slate-400" />
                                </div>
                            </template>
                            <p class="mt-1 truncate text-[11px] font-medium text-slate-700" x-text="pf.name" :title="pf.name"></p>
                            <p class="text-[10px] text-slate-400" x-text="pf.sizeFormatted"></p>
                            <button type="button" @click="removePending(idx)"
                                    class="absolute -top-1.5 -right-1.5 flex size-5 items-center justify-center rounded-full bg-red-500 text-white shadow-sm hover:bg-red-600"
                                    aria-label="Remove this file" title="Remove">
                                <x-icon name="close" class="size-3" />
                            </button>
                        </li>
                    </template>
                </ul>
            </div>
        @endif

        @if($canEdit || $items->isNotEmpty())
            <ul class="uh-admin-media-grid" id="media-grid-{{ $collection }}">
                @foreach($items as $item)
                    @php
                        $isCover = $supportsCover && $owner->featured_media_id === $item->id;
                    @endphp
                    <li class="uh-admin-media-card relative" id="media-item-{{ $item->id }}"
                        :class="{ 'ring-2 ring-gold shadow-md': coverId === {{ $item->id }} }">
                        @if($canEdit && ! $isDocument)
                            <label class="absolute left-2 top-2 z-10 flex size-7 cursor-pointer items-center justify-center rounded-md bg-white/95 shadow" title="Select image">
                                <input type="checkbox" class="size-4 accent-[var(--color-accent)]"
                                       :checked="selectedMediaIds.includes({{ $item->id }})"
                                       @change="toggleMediaSelection({{ $item->id }}, $event.target.checked)"
                                       aria-label="Select {{ $item->original_filename }}">
                            </label>
                        @endif
                        <div class="uh-admin-media-card-bar"
                             :class="{ 'is-ready': itemsData[{{ $item->id }}]?.is_public, 'is-cover': coverId === {{ $item->id }} }">
                            <div class="uh-admin-media-card-copy">
                                <p class="uh-admin-media-name" title="{{ $item->original_filename }}">{{ $item->original_filename }}</p>
                                <p class="uh-admin-media-size">{{ $formatBytes((int) $item->size_bytes) }}</p>
                            </div>

                            <div class="flex items-center gap-1.5">
                            </div>

                            @if($canEdit)
                                <div class="flex items-center gap-0.5 border-l border-white/20 pl-1">
                                    <button type="button" @click="moveItem({{ $item->id }}, -1)"
                                            :disabled="isFirst({{ $item->id }})"
                                            class="uh-icon-btn size-6 text-white/80 hover:text-white disabled:opacity-25"
                                            aria-label="Move earlier" title="Move earlier">
                                        <x-icon name="chevron-left" class="size-3.5" />
                                    </button>
                                    <button type="button" @click="moveItem({{ $item->id }}, 1)"
                                            :disabled="isLast({{ $item->id }})"
                                            class="uh-icon-btn size-6 text-white/80 hover:text-white disabled:opacity-25"
                                            aria-label="Move later" title="Move later">
                                        <x-icon name="chevron-right" class="size-3.5" />
                                    </button>

                                    <form method="POST" action="{{ route('admin.media.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="uh-admin-media-remove ml-1"
                                                aria-label="Remove {{ $item->original_filename }}" title="Remove">
                                            <x-icon name="close" class="size-3.5" />
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div class="uh-admin-media-preview">
                            @if($item->isDocument())
                                <span class="uh-admin-media-slot"><x-icon name="document" class="size-8" /></span>
                            @else
                                <img src="{{ $item->thumbUrl() }}" alt="{{ $item->alt() ?? '' }}" loading="lazy" decoding="async">
                            @endif
                        </div>

                        @if($canEdit && $collection === 'gallery' && $supportsCover)
                            <div class="uh-admin-media-details">
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>

        @elseif(! $canEdit)
            <p class="uh-admin-media-empty">None yet.</p>
        @endif
    </section>
@endforeach
