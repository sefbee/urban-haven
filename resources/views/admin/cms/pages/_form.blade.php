@php
    $model = $page ?? null;
    $canPublish ??= auth()->user()->hasPermission('cms.publish');
    $draft = fn (string $field) => $model ? ($model->pending_changes[$field] ?? $model->{$field}) : null;
    $templateLabels = ['default' => 'Standard page', 'about' => 'About page', 'contact' => 'Contact page with enquiry form', 'campaign' => 'Campaign landing page with enquiry form'];
    $aboutValues = \App\Support\AboutPageContent::resolve((array) ($draft('layout_content') ?? []));
    $aboutFields = [
        'hero_eyebrow' => ['Small label', 'text', 80], 'hero_title' => ['Main headline', 'text', 120], 'hero_intro' => ['Intro line', 'textarea', 300],
        'hero_quote' => ['Quote', 'textarea', 240], 'hero_quote_kicker' => ['Quote eyebrow', 'text', 80], 'hero_quote_label' => ['Quote attribution', 'text', 80], 'hero_badge' => ['Corner label', 'text', 100],
        'hero_primary_cta' => ['Primary button text', 'text', 40], 'hero_primary_url' => ['Primary button link', 'text', 255],
        'hero_secondary_cta' => ['Secondary button text', 'text', 40], 'hero_secondary_url' => ['Secondary button link', 'text', 255],
        'story_eyebrow' => ['Story label', 'text', 80], 'story_title' => ['Story heading', 'text', 140],
        'values_eyebrow' => ['Values label', 'text', 80], 'values_title' => ['Values heading', 'text', 140], 'values_intro' => ['Values introduction', 'textarea', 300],
        'value_one_title' => ['First value heading', 'text', 80], 'value_one_text' => ['First value text', 'textarea', 240],
        'value_two_title' => ['Second value heading', 'text', 80], 'value_two_text' => ['Second value text', 'textarea', 240],
        'value_three_title' => ['Third value heading', 'text', 80], 'value_three_text' => ['Third value text', 'textarea', 240],
        'cta_eyebrow' => ['Closing label', 'text', 80], 'cta_title' => ['Closing heading', 'text', 140],
        'cta_body' => ['Closing text', 'textarea', 300], 'cta_label' => ['Closing button text', 'text', 40], 'cta_url' => ['Closing button link', 'text', 255],
    ];
@endphp

<x-ui.page-header compact :title="$model->title ?? 'New page'"
                  :description="$model ? ($canPublish ? 'Saved changes go live when the page is published.' : 'Changes to a live page wait for a publisher before they appear.') : 'Give the page a title and body. The web address is generated from the title unless you set one.'">
    <x-slot:eyebrow>Website pages · {{ $model ? 'Edit page' : 'New page' }}</x-slot:eyebrow>
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

<div @class(['uh-admin-compose', 'is-split' => (bool) $model])>
    <form method="POST" action="{{ $model ? route('admin.cms.update', $model) : route('admin.cms.store') }}"
          class="uh-admin-compose-main" x-data="uhForm" @submit="submit" data-unsaved-guard>
        @csrf
        @if($model) @method('PUT') @endif

        <div class="uh-admin-stack dd-form-steps">
        <section class="uh-panel">
            <h2 class="uh-h4">Page</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-ui.input name="title" label="Title" :value="$draft('title')" :autofocus="! $model" required maxlength="255"
                            hint="The page title and search result title. The web address is generated from it." />
                <x-ui.select name="template" label="Layout" x-on:change="$dispatch('cms-template-change', $event.target.value)">
                    @foreach($templates as $template)
                        <option value="{{ $template }}" @selected(old('template', $draft('template') ?? 'default') === $template)>{{ $templateLabels[$template] ?? ucfirst($template) }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </section>

            <fieldset class="uh-panel" x-data="{ show: @js(old('template', $draft('template') ?? 'default') === 'about') }"
                      x-on:cms-template-change.window="show = $event.detail === 'about'" x-show="show" x-cloak :disabled="!show">
                <h2 class="uh-h4">About page content</h2>
                <p class="mt-2 text-sm text-[var(--color-muted)]">Edit the wording in each part of the existing About page layout.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach($aboutFields as $field => [$label, $type, $max])
                        <div @class(['uh-field', 'sm:col-span-2' => $type === 'textarea'])>
                            <label class="uh-label" for="about-{{ $field }}">{{ $label }}</label>
                            @if($type === 'textarea')
                                <textarea id="about-{{ $field }}" name="layout_content[{{ $field }}]" class="uh-textarea" rows="3" maxlength="{{ $max }}">{{ old('layout_content.'.$field, $aboutValues[$field]) }}</textarea>
                            @else
                                <input id="about-{{ $field }}" name="layout_content[{{ $field }}]" class="uh-input" value="{{ old('layout_content.'.$field, $aboutValues[$field]) }}" maxlength="{{ $max }}">
                            @endif
                            @error('layout_content.'.$field)<p class="uh-error">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </fieldset>

        <section class="uh-panel">
            <h2 class="uh-h4">Content</h2>
            <div class="mt-4">
                <x-ui.rich-text name="body" label="Page content" :value="$draft('body')" min-height="20rem"
                                hint="Headings, lists and links are styled to match the website automatically." />
            </div>
        </section>

        @include('admin.partials.seo-panel', [
            'seo' => $model?->seoOverride,
            'slugName' => 'slug',
            'slug' => $draft('slug'),
            'baseUrl' => url('/'),
            'titleSource' => 'title',
            'contentSource' => 'body',
            'idPrefix' => 'page-seo',
        ])

        </div>
        <div class="uh-admin-dock">
            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                <span>{{ $model ? 'Save changes' : 'Create page' }}</span>
            </button>
            <a class="uh-btn-ghost" href="{{ route('admin.cms.index') }}">Cancel</a>
        </div>
    </form>

    @if($model)
        <div class="uh-admin-compose-side">
            @if(in_array($model->template, ['about', 'contact'], true))
                @include('admin.partials.media-manager', [
                    'owner' => $model,
                    'ownerType' => 'cms_page',
                    'collections' => ['gallery' => $model->template === 'about' ? 'About page photographs' : 'Contact page photographs'],
                    'canEdit' => auth()->user()->can('update', $model),
                ])
            @endif
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
