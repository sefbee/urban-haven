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
        <span>The maximum photograph size is {{ $maxImageMb }} MB. Formats: JPEG, PNG, WebP. Add more photographs and floor plans after saving.</span>
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
                    <x-ui.input name="photograph_alt" label="Alt text" maxlength="200" optional id="photograph-alt" hint="Describe what the image shows." />
                </div>
            </li>
        </ul>
    </div>
</section>
