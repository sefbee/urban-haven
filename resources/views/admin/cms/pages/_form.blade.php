@php
    $model = $page ?? null;
    $canPublish ??= auth()->user()->hasPermission('cms.publish');
    $draft = fn (string $field) => $model ? ($model->pending_changes[$field] ?? $model->{$field}) : null;
    $templateLabels = ['default' => 'Standard page', 'contact' => 'Contact page with enquiry form', 'campaign' => 'Campaign landing page with enquiry form'];
@endphp

<x-ui.page-header compact :title="$model->title ?? 'New page'"
                  :description="$model ? ($canPublish ? 'Saved changes go live when the page is published.' : 'Changes to a live page wait for a publisher before they appear.') : 'Give the page a title and body. The URL is generated from the title unless you set one.'">
    <x-slot:eyebrow>{{ $model ? 'Edit page' : 'New page' }}</x-slot:eyebrow>
    <x-slot:actions>
        <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.cms.index') }}">
            <x-icon name="chevron-left" class="size-4" />
            All content
        </a>
        @if($model)
            <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.cms.preview', $model) }}" target="_blank" rel="noopener">Preview</a>
        @endif
        @if($model && $model->isPublished())
            <a class="uh-btn-outline uh-btn-sm" href="{{ route('cms.show', $model->slug) }}" target="_blank" rel="noopener">
                <x-icon name="external" class="size-4" />
                View live
            </a>
        @endif
    </x-slot:actions>
</x-ui.page-header>

@if($model?->hasPendingChanges())
    <x-ui.alert tone="warn" class="mt-6">This page has saved changes that are not live yet. {{ $canPublish ? 'Publish to apply them.' : 'A publisher needs to publish them.' }}</x-ui.alert>
@endif

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ $model ? route('admin.cms.update', $model) : route('admin.cms.store') }}"
          class="space-y-6 lg:col-span-2" x-data="uhForm" @submit="submit">
        @csrf
        @if($model) @method('PUT') @endif

        <section class="uh-panel">
            <h2 class="uh-h4">Page content</h2>
            <div class="mt-4 space-y-4">
                <x-ui.input name="title" label="Title" :value="$draft('title')" required maxlength="255" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="slug" label="URL" :value="$draft('slug')" optional maxlength="120" dir="ltr"
                                hint="Lowercase letters, numbers and dashes. Changing it on a live page adds a redirect." />
                    <x-ui.select name="template" label="Template">
                        @foreach($templates as $template)
                            <option value="{{ $template }}" @selected(old('template', $draft('template') ?? 'default') === $template)>{{ $templateLabels[$template] ?? ucfirst($template) }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <x-ui.textarea name="body" label="Body" rows="16" :value="$draft('body')"
                               hint="Basic HTML is allowed and cleaned on save. Headings and paragraphs are styled automatically." />
            </div>
        </section>

        <section class="uh-panel">
            <h2 class="uh-h4">Search engine listing</h2>
            <div class="mt-4 space-y-4">
                <x-ui.input name="meta_title" label="Meta title" maxlength="70" optional :value="$draft('meta_title')" />
                <x-ui.textarea name="meta_description" label="Meta description" rows="2" maxlength="160" optional :value="$draft('meta_description')" />
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                <span>{{ $model ? 'Save changes' : 'Create page' }}</span>
            </button>
            <a class="uh-btn-ghost" href="{{ route('admin.cms.index') }}">Cancel</a>
        </div>
    </form>

    @if($model)
        <div class="space-y-6">
            <section class="uh-panel">
                <h2 class="uh-h4">Publication</h2>
                <p class="mt-2"><x-ui.status :status="$model->editorialStatus()" /></p>
                <p class="mt-3 text-xs leading-relaxed text-[var(--color-muted)]">Address: <span dir="ltr">/{{ $model->slug }}</span></p>
                @if($canPublish)
                    @if(! $model->isPublished() || $model->hasPendingChanges())
                        <form method="POST" action="{{ route('admin.cms.publish', $model) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block">{{ $model->isPublished() ? 'Publish changes' : 'Publish' }}</button>
                        </form>
                    @endif
                    @if($model->isPublished())
                        <form method="POST" action="{{ route('admin.cms.unpublish', $model) }}" class="mt-2" x-data="uhConfirm('Unpublish this page? Its URL will stop working.')">
                            @csrf
                            <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block" @click="confirm($event)">Unpublish</button>
                        </form>
                    @endif
                @else
                    <p class="mt-3 text-xs text-[var(--color-muted)]">A publisher makes pages live.</p>
                @endif
            </section>

            @can('delete', $model)
                <section class="uh-panel">
                    <h2 class="uh-h4">Delete</h2>
                    <p class="mt-2 text-xs leading-relaxed text-[var(--color-muted)]">Deleting removes the page and its URL. Add a redirect first if the page has been shared.</p>
                    <form method="POST" action="{{ route('admin.cms.destroy', $model) }}" class="mt-4"
                          x-data="uhConfirm('Delete “{{ $model->title }}”? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="uh-btn-danger uh-btn-sm uh-btn-block" @click="confirm($event)">Delete page</button>
                    </form>
                </section>
            @endcan
        </div>
    @endif
</div>
