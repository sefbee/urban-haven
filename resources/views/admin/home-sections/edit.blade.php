@extends('layouts.admin')
@section('title', $definition['label'].' · Home page')

@php
    $hasPhotos = ($definition['images'] ?? false) && $canPublish;
    $initialTab = $hasPhotos && request('tab') === 'photos' ? 'photos' : 'content';
@endphp

@section('content')
    <x-ui.page-header compact :title="$definition['label']" :description="$definition['summary']">
        <x-slot:eyebrow>Home page · Section {{ $position }} of {{ $total }}</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.home-sections.index') }}">
                <x-icon name="chevron-left" class="size-4" />
                All sections
            </a>
            <a class="uh-btn-outline uh-btn-sm" href="{{ route('home') }}" target="_blank" rel="noopener">
                <x-icon name="external" class="size-4" />
                Preview
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="dd-editor" x-data="{ tab: @js($initialTab) }">
        <div class="dd-editor-main">
            @if($hasPhotos)
                <div class="dd-tabs" role="tablist" aria-label="Section settings">
                    <button type="button" role="tab" class="dd-tab" :class="{ 'is-active': tab === 'content' }" :aria-selected="(tab === 'content').toString()" @click="tab = 'content'">
                        <x-icon name="document" class="size-4" /> Content
                    </button>
                    <button type="button" role="tab" class="dd-tab" :class="{ 'is-active': tab === 'photos' }" :aria-selected="(tab === 'photos').toString()" @click="tab = 'photos'">
                        <x-icon name="image" class="size-4" /> Background photos
                        <span class="dd-tab-count">{{ $block->images()->count() }}</span>
                    </button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.home-sections.update', $section) }}" class="uh-panel" x-show="tab === 'content'"
                  x-data="uhForm" @submit="submit" data-unsaved-guard>
                @csrf
                @method('PUT')

                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach($definition['fields'] as $name => $field)
                        @php
                            $fieldId = 'section-'.$name;
                            $current = old('content.'.$name, $values[$name] ?? null);
                            $error = $errors->first('content.'.$name) ?: $errors->first('content.'.$name.'.*');
                            $wide = in_array($field['type'], ['textarea', 'repeater'], true) || $name === 'title';
                        @endphp

                        @if($field['type'] === 'repeater')
                            @php
                                $items = collect(is_array($current) ? $current : [])->map(fn ($item) => array_merge(array_map(fn ($child) => $child['default'] ?? '', $field['fields']), (array) $item))->values()->all();
                                $blank = array_map(fn ($child) => $child['default'] ?? '', $field['fields']);
                            @endphp
                            <fieldset class="uh-field sm:col-span-2" x-data="{ items: @js($items), blank: @js($blank), max: {{ $field['max'] }} }">
                                <legend class="uh-label">{{ $field['label'] }} <span class="font-normal text-[var(--color-muted)]">(up to {{ $field['max'] }})</span></legend>
                                <input type="hidden" name="content[{{ $name }}]" value="" :disabled="items.length > 0">
                                <ol class="dd-repeater">
                                    <template x-for="(item, index) in items" :key="index">
                                        <li class="dd-repeater-item">
                                            <div class="dd-repeater-head">
                                                <span class="dd-repeater-index" x-text="index + 1"></span>
                                                <span class="flex-1 truncate text-sm font-medium" x-text="item.title || 'New {{ $field['item_label'] ?? 'item' }}'"></span>
                                                <button type="button" class="uh-admin-icon-btn" @click="if (index > 0) { [items[index - 1], items[index]] = [items[index], items[index - 1]] }" :disabled="index === 0" aria-label="Move up"><x-icon name="chevron-down" class="size-4 rotate-180" /></button>
                                                <button type="button" class="uh-admin-icon-btn" @click="if (index < items.length - 1) { [items[index + 1], items[index]] = [items[index], items[index + 1]] }" :disabled="index === items.length - 1" aria-label="Move down"><x-icon name="chevron-down" class="size-4" /></button>
                                                <button type="button" class="uh-admin-icon-btn text-[var(--dd-red)]" @click="items.splice(index, 1)" aria-label="Remove"><x-icon name="trash" class="size-4" /></button>
                                            </div>
                                            <div class="grid gap-3 sm:grid-cols-[10rem_1fr]">
                                                @foreach($field['fields'] as $childName => $child)
                                                    <div @class(['uh-field', 'sm:col-span-2' => $child['type'] === 'textarea'])>
                                                        <label class="uh-label" :for="'{{ $name }}-' + index + '-{{ $childName }}'">{{ $child['label'] }}</label>
                                                        @if($child['type'] === 'icon')
                                                            <select class="uh-select" data-native-select :id="'{{ $name }}-' + index + '-{{ $childName }}'" :name="'content[{{ $name }}][' + index + '][{{ $childName }}]'" x-model="item.{{ $childName }}">
                                                                @foreach($icons as $icon)
                                                                    <option value="{{ $icon }}">{{ \Illuminate\Support\Str::headline($icon) }}</option>
                                                                @endforeach
                                                            </select>
                                                        @elseif($child['type'] === 'textarea')
                                                            <textarea class="uh-textarea" rows="2" maxlength="{{ $child['max'] }}" :id="'{{ $name }}-' + index + '-{{ $childName }}'" :name="'content[{{ $name }}][' + index + '][{{ $childName }}]'" x-model="item.{{ $childName }}"></textarea>
                                                        @else
                                                            <input class="uh-input" maxlength="{{ $child['max'] }}" :id="'{{ $name }}-' + index + '-{{ $childName }}'" :name="'content[{{ $name }}][' + index + '][{{ $childName }}]'" x-model="item.{{ $childName }}" @if($child['required'] ?? false) required @endif>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </li>
                                    </template>
                                </ol>
                                <p class="text-sm text-[var(--color-muted)]" x-show="items.length === 0">No {{ \Illuminate\Support\Str::plural($field['item_label'] ?? 'item') }} yet.</p>
                                <button type="button" class="uh-btn-outline uh-btn-sm mt-3 self-start" @click="items.push({ ...blank })" x-show="items.length < max">
                                    <x-icon name="plus" class="size-4" /> Add {{ $field['item_label'] ?? 'item' }}
                                </button>
                                @if($error)<p class="uh-error">{{ $error }}</p>@endif
                            </fieldset>
                        @else
                            <div @class(['uh-field', 'sm:col-span-2' => $wide])>
                                <label class="uh-label" for="{{ $fieldId }}">
                                    {{ $field['label'] }}
                                    @if($field['required'] ?? false)<span class="text-[var(--dd-red)]" aria-hidden="true">*</span>@endif
                                </label>
                                @if($field['type'] === 'textarea')
                                    <textarea id="{{ $fieldId }}" class="uh-textarea" rows="3" name="content[{{ $name }}]" maxlength="{{ $field['max'] }}">{{ $current }}</textarea>
                                @elseif($field['type'] === 'number')
                                    <input id="{{ $fieldId }}" class="uh-input max-w-40" type="number" name="content[{{ $name }}]" value="{{ $current }}" min="{{ $field['min'] }}" max="{{ $field['max'] }}" step="1">
                                @else
                                    <input id="{{ $fieldId }}" class="uh-input" type="text" name="content[{{ $name }}]" value="{{ $current }}" maxlength="{{ $field['max'] }}"
                                           @if($field['type'] === 'path') dir="ltr" placeholder="/properties" @endif
                                           @if($field['required'] ?? false) required @endif>
                                @endif
                                @if(! empty($field['hint']))
                                    <p class="uh-hint">{{ $field['hint'] }}</p>
                                @elseif($field['type'] === 'number')
                                    <p class="uh-hint">Between {{ $field['min'] }} and {{ $field['max'] }}.</p>
                                @elseif(($field['required'] ?? false) || blank($field['default'] ?? null))
                                    <p class="uh-hint">Up to {{ $field['max'] }} characters.</p>
                                @else
                                    <p class="uh-hint">Up to {{ $field['max'] }} characters. Leave empty to use “{{ \Illuminate\Support\Str::limit((string) $field['default'], 60) }}”.</p>
                                @endif
                                @if($error)<p class="uh-error">{{ $error }}</p>@endif
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="dd-form-footer">
                    <button type="submit" class="uh-btn-primary" :disabled="submitting">
                        <span class="uh-spinner" x-show="submitting" x-cloak></span>
                        {{ $canPublish ? 'Save and publish' : 'Save draft' }}
                    </button>
                    @if($block->updated_at)
                        <p class="text-xs text-[var(--color-muted)]">Last changed {{ \App\Support\DisplayTimezone::format($block->updated_at) }}</p>
                    @endif
                </div>
            </form>

            @if($hasPhotos)
                <div x-show="tab === 'photos'" x-cloak>
                    @include('admin.partials.media-manager', [
                        'owner' => $block,
                        'ownerType' => 'cms_block',
                        'collections' => ['gallery' => 'Background photos'],
                        'hints' => ['gallery' => 'Use wide landscape photos, at least 1920 px across. Several public photos crossfade behind the homepage search; without any, a featured listing photo is used. Photos stay private until they have alt text.'],
                        'canEdit' => true,
                    ])
                </div>
            @endif
        </div>

        <aside class="dd-editor-side">
            <section class="uh-panel">
                <h2 class="uh-h4">Status</h2>
                <dl class="dd-meta-list mt-3">
                    <div><dt>On the homepage</dt><dd><x-ui.badge :tone="$block->is_visible ? 'success' : 'neutral'">{{ $block->is_visible ? 'Shown' : 'Hidden' }}</x-ui.badge></dd></div>
                    <div><dt>Position</dt><dd>{{ $position }} of {{ $total }}</dd></div>
                    <div><dt>Pending draft</dt><dd>{{ $block->hasDraft() ? 'Yes' : 'None' }}</dd></div>
                </dl>

                @if($canPublish)
                    <div class="mt-4 grid gap-2">
                        <form method="POST" action="{{ route('admin.home-sections.visibility', $section) }}">
                            @csrf
                            <button type="submit" class="uh-btn-outline uh-btn-sm w-full justify-center">
                                <x-icon :name="$block->is_visible ? 'close' : 'check'" class="size-4" />
                                {{ $block->is_visible ? 'Hide this section' : 'Show this section' }}
                            </button>
                        </form>
                        @if($block->hasDraft())
                            <form method="POST" action="{{ route('admin.home-sections.publish', $section) }}">
                                @csrf
                                <button type="submit" class="uh-btn-primary uh-btn-sm w-full justify-center">Publish the editor’s draft</button>
                            </form>
                            <form method="POST" action="{{ route('admin.home-sections.discard', $section) }}" x-data="uhConfirm('Discard the pending draft for this section?')" @submit="confirm">
                                @csrf
                                <button type="submit" class="uh-btn-ghost uh-btn-sm w-full justify-center">Discard draft</button>
                            </form>
                        @endif
                    </div>
                @elseif($block->hasDraft())
                    <x-ui.alert tone="info" class="mt-4">Your last changes are waiting for a publisher.</x-ui.alert>
                @endif
            </section>

            @isset($definition['source'])
                <section class="uh-panel">
                    <h2 class="uh-h4">What fills this section</h2>
                    <p class="mt-2 text-sm text-[var(--color-muted)]">{{ $definition['summary'] }}</p>
                    <a href="{{ route($definition['source']['route']) }}" class="uh-btn-outline uh-btn-sm mt-3">
                        {{ $definition['source']['label'] }} <x-icon name="arrow-right" class="size-4" />
                    </a>
                </section>
            @endisset

            <nav class="uh-panel" aria-label="Other sections">
                <h2 class="uh-h4">Other sections</h2>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    @if($previous)
                        <a class="uh-btn-ghost uh-btn-sm justify-start" href="{{ route('admin.home-sections.edit', $previous) }}"><x-icon name="chevron-left" class="size-4" /> Previous</a>
                    @else
                        <span></span>
                    @endif
                    @if($next)
                        <a class="uh-btn-ghost uh-btn-sm justify-end" href="{{ route('admin.home-sections.edit', $next) }}">Next <x-icon name="chevron-right" class="size-4" /></a>
                    @endif
                </div>
            </nav>

            @if($canPublish)
                <form method="POST" action="{{ route('admin.home-sections.reset', $section) }}" class="px-1"
                      x-data="uhConfirm('Replace this section’s text with the built-in wording?')" @submit="confirm">
                    @csrf
                    <button type="submit" class="uh-link text-xs text-[var(--color-muted)]">Restore the built-in text</button>
                </form>
            @endif
        </aside>
    </div>
@endsection
