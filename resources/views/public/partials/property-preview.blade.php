<div x-show="preview" x-cloak x-transition.opacity.duration.250ms
     class="uh-modal"
     role="dialog" aria-modal="true" aria-labelledby="preview-title"
     :aria-hidden="(!preview).toString()"
     @click.self="closePreview()">
    <div class="uh-dialog uh-preview" @click.stop>
        <div class="uh-preview-media">
            <template x-if="preview?.images?.length">
                <img :src="preview.images[slide].url" :alt="preview.images[slide].alt">
            </template>
            <div x-show="preview && !preview.images?.length" class="uh-media-placeholder">
                {{ __('Photo coming soon') }}
            </div>

            <template x-if="(preview?.images?.length || 0) > 1">
                <div>
                    <button type="button" class="uh-carousel-btn left-0 opacity-100"
                            @click="previousSlide()" aria-label="{{ __('Previous image') }}">
                        <x-icon name="chevron-left" class="size-4" />
                    </button>
                    <button type="button" class="uh-carousel-btn right-0 opacity-100"
                            @click="nextSlide()" aria-label="{{ __('Next image') }}">
                        <x-icon name="chevron-right" class="size-4" />
                    </button>
                    <p class="uh-carousel-count uh-numeric">
                        <span x-text="slide + 1"></span> / <span x-text="preview.images.length"></span>
                    </p>
                </div>
            </template>
        </div>

        <div class="uh-preview-body">
            <button type="button" x-ref="previewClose" class="uh-icon-btn uh-preview-close" @click="closePreview()" aria-label="{{ __('Close quick view') }}">
                <x-icon name="close" class="size-5" />
            </button>

            <p class="uh-preview-meta">
                <span x-text="preview?.listing"></span><template x-if="preview?.type"><span x-text="' · ' + preview.type"></span></template>
            </p>
            <h2 id="preview-title" class="uh-h3 mt-2" x-text="preview?.title"></h2>
            <p class="mt-1 text-[var(--uh-muted)]" x-show="preview?.location" x-text="preview?.location"></p>

            <p class="uh-preview-price uh-numeric" x-text="preview?.price"></p>

            <p class="uh-preview-specs uh-numeric" x-show="preview?.specs?.length" x-text="(preview?.specs || []).join(' · ')"></p>

            <p class="mt-6 leading-relaxed text-[var(--uh-ink-soft)]" x-show="preview?.excerpt" x-text="preview?.excerpt"></p>

            <div class="uh-preview-actions">
                <a class="uh-btn-primary" :href="preview?.url || '#'">{{ __('See the full story') }}</a>
                <a class="uh-btn-secondary" :href="(preview?.url || '#') + '#contact'">{{ __('Ask about it') }}</a>
                <div class="flex flex-wrap gap-x-5 gap-y-2 pt-2" x-show="preview?.id">
                    <button type="button" class="uh-btn-text"
                            @click="$store.saved.toggle('shortlist', preview.id)"
                            :aria-pressed="String(Boolean(preview?.id && $store.saved.has('shortlist', preview.id)))"
                            x-text="preview?.id && $store.saved.has('shortlist', preview.id) ? @js(__('Saved')) : @js(__('Save'))">{{ __('Save') }}</button>
                    <button type="button" class="uh-btn-text"
                            @click="$store.saved.toggle('compare', preview.id)"
                            :aria-pressed="String(Boolean(preview?.id && $store.saved.has('compare', preview.id)))"
                            x-text="preview?.id && $store.saved.has('compare', preview.id) ? @js(__('In compare')) : @js(__('Compare'))">{{ __('Compare') }}</button>
                    <a class="uh-btn-text" x-show="preview?.whatsapp" :href="preview?.whatsapp || '#'" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="quick_view">{{ __('WhatsApp') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
