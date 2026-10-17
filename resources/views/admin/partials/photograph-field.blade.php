@php
    $maxImageMb = max(1, (int) ceil(((int) config('urbanhaven.media.max_image_kb')) / 1024));
@endphp
<section class="uh-panel uh-admin-media" x-data="uhMediaUpload">
    <div class="uh-admin-media-heading">
        <span class="uh-admin-media-heading-icon" aria-hidden="true">
            <x-icon name="image" class="size-4" />
        </span>
        <h2 class="uh-h4">Cover photograph</h2>
    </div>
    <p class="uh-admin-media-hint">
        <x-icon name="info" class="size-4" />
        <span>The maximum photograph size is {{ $maxImageMb }} MB. Formats: JPEG, PNG, WebP.</span>
    </p>
    <div class="uh-admin-media-upload">
        <input id="photograph" x-ref="file" class="uh-admin-media-file" type="file" name="photograph"
               accept="image/jpeg,image/png,image/webp" aria-label="Photograph" @change="chosen()">
        <div class="uh-admin-media-drop" :class="{ 'is-dragging': dragging }" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dropped($event, false)">
            <button type="button" class="uh-admin-media-trigger" @click="pick()">
                <x-icon name="upload" class="size-4" />
                Upload the cover photograph
            </button>
            <span class="uh-admin-media-drop-hint">or drag a photo here</span>
        </div>
        <ul class="uh-admin-media-grid" x-show="filename" x-cloak>
            <li class="uh-admin-media-card is-pending">
                <div class="uh-admin-media-card-bar">
                    <div class="uh-admin-media-card-copy">
                        <p class="uh-admin-media-name" x-text="filename" x-bind:title="filename"></p>
                        <p class="uh-admin-media-size" x-text="filesize"></p>
                    </div>
                    <span class="uh-admin-media-status-text">Ready to save</span>
                    <button type="button" class="uh-admin-media-remove" aria-label="Clear selected file" @click="clear()">
                        <x-icon name="close" class="size-3.5" />
                    </button>
                </div>
                <div class="uh-admin-media-preview" :class="{ 'is-multi': previews.length > 1 }">
                    <template x-for="src in previews" :key="src"><img :src="src" alt=""></template>
                    <span class="uh-admin-media-slot" x-show="!preview"><x-icon name="image" class="size-8" /></span>
                </div>
                <div class="uh-admin-media-details">
                    <x-ui.input name="photograph_alt" label="Alt text" maxlength="200" optional id="photograph-alt" hint="Optional. If blank, this uses the property title, type and location." />
                </div>
            </li>
        </ul>
    </div>
</section>

<section class="uh-panel uh-admin-media mt-4" x-data="uhMediaUpload" aria-labelledby="property-gallery-heading">
    <div class="uh-admin-media-heading">
        <span class="uh-admin-media-heading-icon" aria-hidden="true">
            <x-icon name="image" class="size-4" />
        </span>
        <h2 id="property-gallery-heading" class="uh-h4">Property gallery</h2>
    </div>
    <p class="uh-admin-media-hint">
        <x-icon name="info" class="size-4" />
        <span>Upload additional photographs of the property. You can add multiple images and manage them before saving. Up to 100 images, {{ $maxImageMb }} MB each.</span>
    </p>
    <div class="uh-admin-media-upload">
        <input id="property-gallery-files" x-ref="file" class="uh-admin-media-file" type="file" name="photographs[]"
               accept="image/jpeg,image/png,image/webp" multiple aria-label="Property gallery photographs" @change="chosen()">
        <div class="uh-admin-media-drop" :class="{ 'is-dragging': dragging }" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dropped($event, false)">
            <button type="button" class="uh-admin-media-trigger" @click="pick()">
                <x-icon name="upload" class="size-4" />
                Add gallery photographs
            </button>
            <span class="uh-admin-media-drop-hint">or drag photos here</span>
        </div>
        <ul class="uh-admin-media-grid" x-show="filename" x-cloak>
            <li class="uh-admin-media-card is-pending">
                <div class="uh-admin-media-card-bar">
                    <div class="uh-admin-media-card-copy">
                        <p class="uh-admin-media-name" x-text="filename" x-bind:title="filename"></p>
                        <p class="uh-admin-media-size" x-text="filesize"></p>
                    </div>
                    <span class="uh-admin-media-status-text">Ready to save</span>
                    <button type="button" class="uh-admin-media-remove" aria-label="Clear selected gallery photographs" @click="clear()">
                        <x-icon name="close" class="size-3.5" />
                    </button>
                </div>
                <div class="uh-admin-media-preview is-multi">
                    <template x-for="(file, index) in pendingFiles.filter((item) => item.isImage)" :key="file.id">
                        <div class="relative">
                            <img :src="file.previewUrl" :alt="`Selected gallery photograph ${index + 1}`">
                            <button type="button" class="uh-admin-media-remove absolute right-1 top-1" :aria-label="`Remove ${file.name}`" @click="removePending(pendingFiles.indexOf(file))">
                                <x-icon name="close" class="size-3.5" />
                            </button>
                        </div>
                    </template>
                </div>
            </li>
        </ul>
    </div>
</section>
