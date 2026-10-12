@php
    $model = $page ?? null;
    $canPublish ??= auth()->user()->hasPermission('cms.publish');
    $draft = fn (string $field) => $model ? ($model->pending_changes[$field] ?? $model->{$field}) : null;
    $templateLabels = ['default' => 'Custom CMS page', 'about' => 'Dynamic module · About page', 'contact' => 'Dynamic module · Contact page', 'campaign' => 'Dynamic module · Campaign landing page'];
    $aboutValues = \App\Support\AboutPageContent::resolve((array) ($draft('layout_content') ?? []));
    $aboutFields = [
                'hero_eyebrow' => ['Small label', 'text', 80], 'hero_title' => ['Main headline', 'textarea', 200], 'hero_intro' => ['Intro line', 'textarea', 300],
        'hero_quote' => ['Quote', 'textarea', 240], 'hero_quote_kicker' => ['Quote eyebrow', 'text', 80], 'hero_quote_label' => ['Quote attribution', 'text', 80], 'hero_badge' => ['Corner label', 'text', 100],
        'hero_primary_cta' => ['Primary button text', 'text', 40], 'hero_primary_url' => ['Primary button link', 'text', 255],
        'hero_secondary_cta' => ['Secondary button text', 'text', 40], 'hero_secondary_url' => ['Secondary button link', 'text', 255],
        'story_eyebrow' => ['Story label', 'text', 80], 'story_title' => ['Story heading', 'text', 140],
        
        'stat_one_value' => ['Stat 1 Value', 'text', 40], 'stat_one_label' => ['Stat 1 Label', 'text', 100],
        'stat_two_value' => ['Stat 2 Value', 'text', 40], 'stat_two_label' => ['Stat 2 Label', 'text', 100],
        'stat_three_value' => ['Stat 3 Value', 'text', 40], 'stat_three_label' => ['Stat 3 Label', 'text', 100],
        'stat_four_value' => ['Stat 4 Value', 'text', 40], 'stat_four_label' => ['Stat 4 Label', 'text', 100],

        'purpose_eyebrow' => ['Purpose label', 'text', 80], 'purpose_title' => ['Purpose heading', 'text', 140],
        'mission_title' => ['Mission title', 'text', 80], 'mission_text' => ['Mission text', 'textarea', 300],
        'vision_title' => ['Vision title', 'text', 80], 'vision_text' => ['Vision text', 'textarea', 300],

        'values_eyebrow' => ['Values label', 'text', 80], 'values_title' => ['Values heading', 'text', 140], 'values_intro' => ['Values introduction', 'textarea', 300],
        'value_one_title' => ['First value heading', 'text', 80], 'value_one_text' => ['First value text', 'textarea', 240],
        'value_two_title' => ['Second value heading', 'text', 80], 'value_two_text' => ['Second value text', 'textarea', 240],
        'value_three_title' => ['Third value heading', 'text', 80], 'value_three_text' => ['Third value text', 'textarea', 240],
        'value_four_title' => ['Fourth value heading', 'text', 80], 'value_four_text' => ['Fourth value text', 'textarea', 240],

        'choose_eyebrow' => ['Why Choose Us label', 'text', 80], 'choose_title' => ['Why Choose Us heading', 'text', 140],
        'choose_list' => ['Why Choose Us list (one per line)', 'textarea', 500],
        'choose_cta_label' => ['Why Choose button text', 'text', 40], 'choose_cta_url' => ['Why Choose button link', 'text', 255],

        'approach_eyebrow' => ['Approach label', 'text', 80], 'approach_title' => ['Approach heading', 'text', 140],
        'approach_one_title' => ['Step 1 title', 'text', 80], 'approach_one_text' => ['Step 1 text', 'textarea', 240],
        'approach_two_title' => ['Step 2 title', 'text', 80], 'approach_two_text' => ['Step 2 text', 'textarea', 240],
        'approach_three_title' => ['Step 3 title', 'text', 80], 'approach_three_text' => ['Step 3 text', 'textarea', 240],
        'approach_four_title' => ['Step 4 title', 'text', 80], 'approach_four_text' => ['Step 4 text', 'textarea', 240],

        'team_eyebrow' => ['Team label', 'text', 80], 'team_title' => ['Team heading', 'text', 140],

        'cta_eyebrow' => ['Closing label', 'text', 80], 'cta_title' => ['Closing heading', 'text', 140],
        'cta_body' => ['Closing text', 'textarea', 300], 'cta_label' => ['Closing button text', 'text', 40], 'cta_url' => ['Closing button link', 'text', 255],
    ];
@endphp

<x-ui.page-header compact :title="$model->title ?? 'New page'">
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
          id="cms-main-form" class="uh-admin-compose-main" x-data="uhForm" @submit="submit" data-unsaved-guard data-upload-pending-media>
        @csrf
        @if($model) @method('PUT') @endif

        <div class="uh-admin-stack dd-form-steps">
        <section class="uh-panel">
            <h2 class="uh-h4">Page</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-ui.input name="title" label="Title" :value="$draft('title')" :autofocus="! $model" required maxlength="255"
                            hint="The page title and search result title. The web address is generated from it." />
                <x-ui.select name="template" label="Page type" x-on:change="$dispatch('cms-template-change', $event.target.value)">
                    @foreach($templates as $template)
                        <option value="{{ $template }}" @selected(old('template', $draft('template') ?? 'default') === $template)>{{ $templateLabels[$template] ?? ucfirst($template) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="menu_icon" label="Sidebar icon" hint="Choose the icon shown beside this page in the admin sidebar." show-hint>
                    @foreach(\App\Models\CmsPage::MENU_ICONS as $icon => $label)
                        <option value="{{ $icon }}" @selected(old('menu_icon', $draft('menu_icon') ?? 'document') === $icon)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </section>

            <fieldset class="uh-panel" x-data="{ show: @js(old('template', $draft('template') ?? 'default') === 'about') }"
                      x-on:cms-template-change.window="show = $event.detail === 'about'" x-show="show" x-cloak :disabled="!show">
                <h2 class="uh-h4">About page content</h2>
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
                @endif
            </section>

            @can('delete', $model)
                <section class="uh-panel">
                    <h2 class="uh-h4">Delete</h2>
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
