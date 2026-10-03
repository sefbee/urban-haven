@php
    $collections ??= ['gallery' => 'Photographs', 'floor_plan' => 'Floor plans', 'brochure' => 'Brochures'];
    $owner->loadMissing('media');
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
        $items = $owner->media->where('collection', $collection)->sortBy('sort_order');
        $isDocument = $collection === 'brochure';
        $uploadLabel = $isDocument ? 'Upload brochure' : 'Upload photographs';
    @endphp
    <section class="uh-panel uh-admin-media" aria-labelledby="media-{{ $collection }}-heading" @if($canEdit) x-data="uhMediaUpload" @endif>
        <div class="uh-admin-media-heading">
            <span class="uh-admin-media-heading-icon" aria-hidden="true">
                <x-icon :name="$isDocument ? 'document' : 'image'" class="size-4" />
            </span>
            <h2 id="media-{{ $collection }}-heading" class="uh-h4">{{ $heading }}</h2>
        </div>

        <p class="uh-admin-media-hint">
            <x-icon name="info" class="size-4" />
            <span>
                @if($isDocument)
                    The maximum brochure size is {{ $maxBrochureMb }} MB. Format: PDF.
                @else
                    The maximum photograph size is {{ $maxImageMb }} MB. Formats: JPEG, PNG, WebP.
                    @if($collection === 'gallery')
                        Images stay private until they have alt text. The cover is used on cards and social previews.
                    @endif
                @endif
            </span>
        </p>

        @if($canEdit)
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data"
                  class="uh-admin-media-upload" @submit="submit">
                @csrf
                <input type="hidden" name="owner_type" value="{{ $ownerType }}">
                <input type="hidden" name="owner_id" value="{{ $owner->id }}">
                <input type="hidden" name="collection" value="{{ $collection }}">
                <input id="upload-{{ $collection }}" x-ref="file" class="uh-admin-media-file" type="file" name="file" required
                       accept="{{ $isDocument ? 'application/pdf' : 'image/jpeg,image/png,image/webp' }}"
                       aria-label="{{ $isDocument ? 'Brochure' : 'Photograph' }}"
                       @change="chosen(true)">
                <div class="uh-admin-media-drop">
                    <button type="button" class="uh-admin-media-trigger" :disabled="submitting" @click="pick()">
                        <x-icon name="upload" class="size-4" />
                        <span x-text="submitting ? 'Uploading…' : {{ \Illuminate\Support\Js::from($uploadLabel) }}">{{ $uploadLabel }}</span>
                    </button>
                </div>
            </form>
        @endif

        @if($canEdit || $items->isNotEmpty())
            <ul class="uh-admin-media-grid" @if($canEdit && $items->isEmpty()) x-show="filename" x-cloak @endif>
                @if($canEdit)
                    <li class="uh-admin-media-card is-pending" x-show="filename" x-cloak>
                        <div class="uh-admin-media-card-bar" :class="submitting ? 'is-busy' : ''">
                            <div class="uh-admin-media-card-copy">
                                <p class="uh-admin-media-name" x-text="filename" x-bind:title="filename"></p>
                                <p class="uh-admin-media-size" x-text="filesize"></p>
                            </div>
                            <span class="uh-admin-media-status-text" x-text="submitting ? 'Uploading…' : 'Ready to upload'"></span>
                            <button type="button" class="uh-admin-media-remove" aria-label="Clear selected file" @click="clear()">
                                <x-icon name="close" class="size-3.5" />
                            </button>
                        </div>
                        <div class="uh-admin-media-preview">
                            <img x-show="preview" x-bind:src="preview" alt="" x-cloak>
                            <span class="uh-admin-media-slot" x-show="!preview"><x-icon :name="$isDocument ? 'document' : 'image'" class="size-8" /></span>
                        </div>
                    </li>
                @endif
                @foreach($items as $item)
                    @php
                        $isCover = $owner->featured_media_id === $item->id;
                        $status = $isCover ? 'Cover' : ($item->is_public ? 'Uploaded' : 'Private');
                    @endphp
                    <li class="uh-admin-media-card">
                        <div @class(['uh-admin-media-card-bar', 'is-ready' => $item->is_public, 'is-cover' => $isCover])>
                            <div class="uh-admin-media-card-copy">
                                <p class="uh-admin-media-name" title="{{ $item->original_filename }}">{{ $item->original_filename }}</p>
                                <p class="uh-admin-media-size">{{ $formatBytes((int) $item->size_bytes) }}</p>
                            </div>
                            <span class="uh-admin-media-status-text">{{ $status }}</span>
                            @if($canEdit)
                                <form method="POST" action="{{ route('admin.media.destroy', $item) }}"
                                      x-data="uhConfirm('Remove this file?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="uh-admin-media-remove" aria-label="Remove {{ $item->original_filename }}" @click="confirm($event)">
                                        <x-icon name="close" class="size-3.5" />
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="uh-admin-media-preview">
                            @if($item->isDocument())
                                <span class="uh-admin-media-slot"><x-icon name="document" class="size-8" /></span>
                            @else
                                <img src="{{ $item->thumbUrl() }}" alt="" loading="lazy" decoding="async">
                            @endif
                        </div>
                        @if($canEdit)
                            <form method="POST" action="{{ route('admin.media.update', $item) }}" class="uh-admin-media-details">
                                @csrf
                                @method('PATCH')
                                <x-ui.input name="alt_text" :label="$item->isDocument() ? 'Title' : 'Alt text'" :value="$item->alt_texts['en'] ?? ''" maxlength="200" :id="'alt-'.$item->id" />
                                <input type="hidden" name="is_public" value="0">
                                <label class="uh-check text-xs"><input type="checkbox" name="is_public" value="1" @checked($item->is_public)> <span>Public</span></label>
                                @if($collection === 'gallery')
                                    <label class="uh-check text-xs"><input type="checkbox" name="make_cover" value="1"> <span>Use as cover</span></label>
                                @endif
                                <button type="submit" class="uh-btn-outline uh-btn-sm">Save</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @elseif(! $canEdit)
            <p class="uh-admin-media-empty">None yet.</p>
        @endif
    </section>
@endforeach
