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

        </div>

        <p class="uh-admin-media-hint">
            <x-icon name="info" class="size-4" />
            <span>
                @if($isDocument)
                    The maximum brochure size is {{ $maxBrochureMb }} MB. Format: PDF.
                @else
                    The maximum photograph size is {{ $maxImageMb }} MB. Formats: JPEG, PNG, WebP.
                    @if($collection === 'gallery')
                        Images stay private until they have alt text.
                        @if($supportsCover) Use the cover button to designate the primary showcase image. @endif
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
                    <span class="uh-admin-media-drop-hint">or drag {{ $isDocument ? 'the PDF' : 'photos' }} here (up to 20 at once)</span>
                </div>
            </form>

            {{-- Multi-file selection review before committing --}}
            <div class="mt-3 rounded-lg border-2 border-dashed border-gold/40 bg-gold/5 p-3.5"
                 x-show="pendingFiles.length > 0" x-cloak x-transition>
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gold/20 pb-2.5">
                    <div>
                        <p class="text-xs font-semibold text-ink">
                            <span x-text="pendingFiles.filter(file => !file.uploaded).length"></span>
                            <span x-text="pendingFiles.filter(file => !file.uploaded).length === 1 ? 'file' : 'photos'"></span> ready to upload
                            <span class="text-xs font-normal text-muted" x-text="'(' + filesize + ' total)'"></span>
                        </p>
                        <p class="text-[11px] text-muted">Set image details before uploading. Uploaded images are saved with these settings.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="uh-btn-primary uh-btn-sm" x-show="pendingFiles.some(file => !file.uploaded)" :disabled="submitting" @click="uploadPending()">
                            <span class="uh-spinner" x-show="submitting" x-cloak></span>
                            <x-icon name="upload" class="size-3.5" x-show="!submitting" />
                            <span x-text="submitting ? 'Uploading…' : ('Upload ' + pendingFiles.filter(file => !file.uploaded).length + (pendingFiles.filter(file => !file.uploaded).length === 1 ? ' file' : ' photos'))">Upload</span>
                        </button>
                        <button type="button" class="uh-btn-ghost uh-btn-sm" x-show="pendingFiles.some(file => !file.uploaded)" :disabled="submitting" @click="clear()">
                            Cancel
                        </button>
                    </div>
                </div>

                <ul class="mt-2.5 grid grid-cols-2 gap-2 sm:grid-cols-4 md:grid-cols-6">
                    <template x-for="(pf, idx) in pendingFiles" :key="pf.id">
                        <li class="relative rounded-md border border-line bg-white p-1 text-center shadow-xs">
                            <template x-if="pf.previewUrl">
                                <img :src="pf.previewUrl" class="aspect-4/3 w-full rounded object-cover" alt="">
                            </template>
                            <template x-if="!pf.previewUrl">
                                <div class="aspect-4/3 flex items-center justify-center rounded bg-stone/20">
                                    <x-icon :name="$isDocument ? 'document' : 'image'" class="size-6 text-muted" />
                                </div>
                            </template>
                            <p class="mt-1 truncate text-[11px] font-medium text-ink" x-text="pf.name" :title="pf.name"></p>
                            <p class="text-[10px] text-muted" x-text="pf.sizeFormatted"></p>
                            <p class="text-[10px] font-semibold text-forest" x-show="pf.uploaded">Uploaded with settings</p>
                            @if(! $isDocument)
                                <div class="mt-2 space-y-2 px-1 text-left" x-show="pf.isImage">
                                    <label class="uh-label text-[11px]">
                                        Alt text <span class="text-danger" x-show="pf.is_public">*</span>
                                        <input type="text" class="uh-input mt-1 text-xs" maxlength="200"
                                               placeholder="Describe the photograph" x-model="pf.alt_text" :disabled="pf.uploaded">
                                    </label>
                                    <div class="flex flex-wrap items-center justify-between gap-1">
                                        <label class="uh-check text-[11px]">
                                            <input type="checkbox" x-model="pf.is_public" :disabled="pf.uploaded">
                                            <span>Public</span>
                                        </label>
                                        @if($collection === 'gallery' && $supportsCover)
                                            <button type="button" class="text-[11px] font-medium text-gold hover:underline"
                                                    @click="setPendingCover(idx)" :disabled="pf.uploaded"
                                                    x-text="pf.make_cover ? '★ Cover photo' : 'Set as cover'">
                                                Set as cover
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <button type="button" @click="removePending(idx)" x-show="!pf.uploaded"
                                    class="absolute -top-1.5 -right-1.5 flex size-4.5 items-center justify-center rounded-full bg-danger text-white shadow-xs hover:bg-danger/80"
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
                    <li class="uh-admin-media-card" id="media-item-{{ $item->id }}"
                        :class="{ 'ring-2 ring-gold shadow-md': coverId === {{ $item->id }} }">
                        <div class="uh-admin-media-card-bar"
                             :class="{ 'is-ready': itemsData[{{ $item->id }}]?.is_public, 'is-cover': coverId === {{ $item->id }} }">
                            <div class="uh-admin-media-card-copy">
                                <p class="uh-admin-media-name" title="{{ $item->original_filename }}">{{ $item->original_filename }}</p>
                                <p class="uh-admin-media-size">{{ $formatBytes((int) $item->size_bytes) }}</p>
                            </div>

                            <div class="flex items-center gap-1.5">
                                @if($collection === 'gallery' && $supportsCover)
                                    <template x-if="coverId === {{ $item->id }}">
                                        <span class="rounded bg-gold px-1.5 py-0.5 text-[10px] font-bold text-night">Cover</span>
                                    </template>
                                @endif
                                <span class="uh-admin-media-status-text"
                                      x-text="coverId === {{ $item->id }} ? '' : (itemsData[{{ $item->id }}]?.is_public ? 'Public' : 'Private')">
                                    {{ $isCover ? 'Cover' : ($item->is_public ? 'Public' : 'Private') }}
                                </span>
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

                                    <form method="POST" action="{{ route('admin.media.destroy', $item) }}"
                                          x-data="uhConfirm('Remove this {{ $isDocument ? 'file' : 'photograph' }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="uh-admin-media-remove ml-1"
                                                aria-label="Remove {{ $item->original_filename }}" @click="confirm($event)">
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

                        @if($canEdit)
                            <div class="uh-admin-media-details">
                                <div class="uh-field">
                                    <label for="alt-{{ $item->id }}" class="uh-label text-xs">
                                        {{ $item->isDocument() ? 'Title' : 'Alt text' }}
                                        <span class="text-danger" x-show="itemsData[{{ $item->id }}]?.is_public">*</span>
                                    </label>
                                    <input id="alt-{{ $item->id }}" type="text" name="alt_text"
                                           class="uh-input text-xs" maxlength="200"
                                           value="{{ $item->alt_texts['en'] ?? '' }}"
                                           :value="itemsData[{{ $item->id }}]?.alt_text ?? '{{ addslashes($item->alt_texts['en'] ?? '') }}'"
                                           @input="updateItemAlt({{ $item->id }}, $event.target.value)"
                                           placeholder="{{ $item->isDocument() ? 'Document title' : 'Describe the photograph' }}">
                                </div>

                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <input type="hidden" name="is_public" value="0">
                                    <label class="uh-check text-xs">
                                        <input type="checkbox" name="is_public" value="1" @checked($item->is_public)
                                               :checked="itemsData[{{ $item->id }}]?.is_public"
                                               @change="updateItemPublic({{ $item->id }}, $event.target.checked)">
                                        <span>Public</span>
                                    </label>

                                    @if($collection === 'gallery' && $supportsCover)
                                        <button type="button" @click="setCover({{ $item->id }})"
                                                class="text-xs font-medium text-gold hover:underline"
                                                x-show="coverId !== {{ $item->id }}">
                                            Set as cover
                                        </button>
                                        <span class="text-xs font-semibold text-gold" x-show="coverId === {{ $item->id }}">
                                            ★ Cover photo
                                        </span>
                                        <input type="hidden" name="make_cover" :value="coverId === {{ $item->id }} ? 1 : 0">
                                    @endif

                                </div>
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
