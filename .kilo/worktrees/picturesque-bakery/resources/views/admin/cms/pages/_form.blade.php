@php($model = $page ?? null)

<x-ui.page-header compact :title="$model->title ?? 'New page'"
                  :description="$model ? 'Saved changes stay unpublished until you publish the page.' : 'Give the page a title and body. The URL is generated from the title.'">
    <x-slot:eyebrow>{{ $model ? 'Edit page' : 'New page' }}</x-slot:eyebrow>
    <x-slot:actions>
        <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.cms.index') }}">
            <x-icon name="chevron-left" class="size-4" />
            All content
        </a>
        @if($model && $model->isPublished())
            <a class="uh-btn-outline uh-btn-sm" href="{{ url('/'.$model->slug) }}">
                <x-icon name="external" class="size-4" />
                View live
            </a>
        @endif
    </x-slot:actions>
</x-ui.page-header>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ $model ? route('admin.cms.update', $model) : route('admin.cms.store') }}"
          class="space-y-6 lg:col-span-2" x-data="uhForm" @submit="submit">
        @csrf
        @if($model) @method('PUT') @endif

        <section class="uh-panel">
            <h2 class="uh-h4">Page content</h2>
            <div class="mt-4 space-y-4">
                <x-ui.input name="title" label="Title" :value="$model->title ?? ''" required />
                <x-ui.textarea name="body" label="Body" rows="16" :value="$model->body ?? ''"
                               hint="Basic HTML is allowed. Headings and paragraphs are styled automatically." />
            </div>
        </section>

        <section class="uh-panel">
            <h2 class="uh-h4">Search engine listing</h2>
            <div class="mt-4 space-y-4">
                <x-ui.input name="meta_title" label="Meta title" maxlength="70" optional :value="$model->meta_title ?? ''" />
                <x-ui.textarea name="meta_description" label="Meta description" rows="2" maxlength="160" optional
                               :value="$model->meta_description ?? ''" />
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                <span x-text="submitting ? 'Saving…' : '{{ $model ? 'Save changes' : 'Create page' }}'">{{ $model ? 'Save changes' : 'Create page' }}</span>
            </button>
            <a class="uh-btn-ghost" href="{{ route('admin.cms.index') }}">Cancel</a>
        </div>
    </form>

    @if($model)
        <div class="space-y-6">
            <section class="uh-panel">
                <h2 class="uh-h4">Publication</h2>
                <p class="mt-2"><x-ui.status :status="$model->editorialStatus()" /></p>
                <p class="mt-3 text-xs leading-relaxed text-[var(--color-muted)]">
                    Published at <span dir="ltr">/{{ $model->slug }}</span> once you publish.
                </p>
                <form method="POST" action="{{ route('admin.cms.publish', $model) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block">Publish</button>
                </form>
            </section>

            <section class="uh-panel">
                <h2 class="uh-h4">Delete</h2>
                <p class="mt-2 text-xs leading-relaxed text-[var(--color-muted)]">
                    Deleting removes the page and its URL. Set up a redirect first if the page has been shared.
                </p>
                <form method="POST" action="{{ route('admin.cms.destroy', $model) }}" class="mt-4"
                      x-data="uhConfirm('Delete “{{ $model->title }}”? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="uh-btn-danger uh-btn-sm uh-btn-block" @click="confirm($event)">Delete page</button>
                </form>
            </section>
        </div>
    @endif
</div>
