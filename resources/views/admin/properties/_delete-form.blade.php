@can('delete', $property)
    @php($dialogId = 'delete-property-'.$property->id.'-'.($context ?? 'action'))
    <form method="POST" action="{{ route('admin.properties.destroy', $property) }}"
          x-data="uhDeleteConfirmation(@js($property->title))" @submit="guard($event)"
          class="{{ $class ?? '' }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="uh-btn-danger uh-btn-sm w-full lg:w-auto" :disabled="submitting" aria-haspopup="dialog" aria-controls="{{ $dialogId }}" aria-label="Delete {{ $property->title }}">
            <x-icon name="trash" class="size-4" />
            <span>Delete</span>
        </button>

        <dialog id="{{ $dialogId }}" x-ref="dialog" aria-labelledby="{{ $dialogId }}-title" aria-describedby="{{ $dialogId }}-description"
                @cancel.prevent="cancel()"
                @click.self="cancel()"
                class="fixed inset-0 m-0 h-dvh w-full max-h-none max-w-none overflow-y-auto border-0 bg-transparent p-0 backdrop:bg-slate-950/55">
            <div class="grid min-h-full w-full place-items-center p-4">
                <div class="my-auto max-h-[calc(100dvh-2rem)] w-[min(28rem,100%)] overflow-y-auto rounded-2xl border border-line bg-white p-5 text-ink shadow-2xl sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[var(--color-danger-bg)] text-[var(--color-danger)]">
                            <x-icon name="trash" class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <h2 id="{{ $dialogId }}-title" class="uh-h4">Delete property?</h2>
                            <p id="{{ $dialogId }}-description" class="mt-2 break-words text-sm leading-relaxed text-[var(--color-muted)]">
                                Delete <strong class="font-semibold text-ink" x-text="title"></strong> and its images and units? Inquiry and visit records will be kept without the property link. This action cannot be undone.
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button" class="uh-btn-outline uh-btn-sm" @click="cancel()" autofocus>Cancel</button>
                        <button type="button" class="uh-btn-danger uh-btn-sm" @click="confirm()" :disabled="submitting">
                            <span x-show="! submitting">Delete property</span>
                            <span x-show="submitting" x-cloak>Deleting…</span>
                        </button>
                    </div>
                </div>
            </div>
        </dialog>
    </form>
@endcan
