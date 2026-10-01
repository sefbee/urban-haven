<div x-show="preview" x-cloak x-transition.opacity.duration.150ms
     class="fixed inset-0 z-100 flex items-end justify-center bg-ink/60 sm:items-center sm:p-6"
     role="dialog" aria-modal="true" aria-labelledby="preview-title"
     :aria-hidden="(!preview).toString()"
     @click.self="closePreview()">
    <div class="flex max-h-[100dvh] w-full max-w-5xl flex-col overflow-hidden bg-paper shadow-[var(--shadow-uh-lg)] sm:max-h-[90vh] sm:rounded-2xl lg:flex-row"
         @click.stop>
        <div class="relative shrink-0 bg-ink lg:w-[56%]">
            <template x-if="preview?.images?.length">
                <img :src="preview.images[slide].url"
                     :alt="preview.images[slide].alt"
                     class="aspect-4/3 w-full object-cover sm:aspect-16/10 lg:aspect-auto lg:h-full lg:min-h-[28rem]">
            </template>
            <div x-show="preview && !preview.images?.length" class="flex aspect-4/3 items-center justify-center text-sm text-cream/70 lg:h-full">
                {{ __('Photo coming soon') }}
            </div>

            <template x-if="(preview?.images?.length || 0) > 1">
                <div>
                    <button type="button" class="absolute left-3 top-1/2 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-ink/70 text-cream"
                            @click="previousSlide()" aria-label="{{ __('Previous image') }}">
                        <x-icon name="chevron-left" class="size-5" />
                    </button>
                    <button type="button" class="absolute right-3 top-1/2 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-ink/70 text-cream"
                            @click="nextSlide()" aria-label="{{ __('Next image') }}">
                        <x-icon name="chevron-right" class="size-5" />
                    </button>
                    <p class="absolute bottom-3 left-3 rounded-md bg-ink/80 px-2.5 py-1 text-xs font-semibold text-cream">
                        <span class="uh-numeric" x-text="slide + 1"></span> / <span class="uh-numeric" x-text="preview.images.length"></span>
                    </p>
                </div>
            </template>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-y-auto p-5 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="uh-badge uh-badge-gold" x-text="preview?.listing"></span>
                    <span class="uh-badge" x-show="preview?.type" x-text="preview?.type"></span>
                </div>
                <button type="button" x-ref="previewClose" class="uh-icon-btn -mr-2 -mt-1 shrink-0" @click="closePreview()" aria-label="{{ __('Close quick view') }}">
                    <x-icon name="close" class="size-5" />
                </button>
            </div>

            <p class="mt-4 text-2xl font-semibold tracking-tight text-forest" x-text="preview?.price"></p>
            <p class="mt-0.5 text-xs text-[var(--color-muted)] uh-numeric" x-show="preview?.exact" x-text="preview?.exact"></p>
            <p class="mt-1 text-xs text-[var(--color-muted)]" x-show="preview?.reference" x-text="preview?.reference ? '# ' + preview.reference : ''"></p>

            <h2 id="preview-title" class="uh-h3 mt-3" x-text="preview?.title"></h2>
            <p class="mt-1.5 flex items-center gap-1.5 text-sm text-[var(--color-muted)]" x-show="preview?.location">
                <x-icon name="pin" class="size-3.5 shrink-0 text-[var(--color-gold-ink)]" />
                <span x-text="preview?.location"></span>
            </p>

            <ul class="mt-4 flex flex-wrap gap-2" x-show="preview?.specs?.length">
                <template x-for="spec in (preview?.specs || [])" :key="spec">
                    <li class="uh-chip pointer-events-none" x-text="spec"></li>
                </template>
            </ul>

            <p class="mt-4 text-sm leading-relaxed text-[var(--color-muted)]" x-show="preview?.excerpt" x-text="preview?.excerpt"></p>
            <p class="mt-3 text-xs text-[var(--color-muted)]" x-show="preview?.updated" x-text="preview?.updated"></p>

            <div class="mt-auto flex flex-wrap gap-2 border-t border-line pt-5">
                <a class="uh-btn-primary uh-btn-sm" :href="(preview?.url || '#') + '#contact'">{{ __('Enquire') }}</a>
                <a class="uh-btn-whatsapp uh-btn-sm" x-show="preview?.whatsapp" :href="preview?.whatsapp || '#'" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="quick_view">
                    <x-icon name="whatsapp" class="size-4" />
                    {{ __('WhatsApp Us') }}
                </a>
                <a class="uh-btn-outline uh-btn-sm" :href="preview?.url || '#'">{{ __('View details') }}</a>
<button type="button" class="uh-btn-outline uh-btn-sm" x-show="preview?.id"
                        @click="$store.saved.toggle('shortlist', preview.id)"
                        :aria-pressed="(preview?.id && $store.saved.has('shortlist', preview.id)).toString()">
                    <x-icon name="heart" class="size-4" x-show="!(preview?.id && $store.saved.has('shortlist', preview.id))" />
                    <x-icon name="heart-solid" class="size-4 text-[var(--color-danger)]" x-show="preview?.id && $store.saved.has('shortlist', preview.id)" x-cloak />
                    <span x-text="preview?.id && $store.saved.has('shortlist', preview.id) ? @js(__('Saved')) : @js(__('Save'))"></span>
                </button>
            </div>
        </div>
    </div>
</div>
