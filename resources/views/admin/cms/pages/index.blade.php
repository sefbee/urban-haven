@extends('layouts.admin')
@section('title', 'Pages')

@section('content')
    <x-ui.page-header compact title="Pages"
                      description="Standalone pages and the editable blocks used across the public site.">
        <x-slot:eyebrow>Website</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.cms.create') }}">
                <x-icon name="plus" class="size-4" />
                New page
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.admin-related label="Also on the site">
        <a href="{{ route('admin.posts.index') }}">Articles</a>
        <a href="{{ route('admin.faqs.index') }}">FAQs</a>
        @if($canPublish)
            <a href="{{ route('admin.menus.index') }}">Menus</a>
        @endif
        @can('redirect.manage')
            <a href="{{ route('admin.redirects.index') }}">Redirects</a>
        @endcan
    </x-ui.admin-related>

    <section aria-labelledby="pages-heading">
        <h2 id="pages-heading" class="sr-only">Pages</h2>

        @if($pages->isNotEmpty())
            <div class="uh-panel-flush overflow-hidden">
                <div class="uh-table-scroll">
                    <table class="uh-table">
                        <caption class="sr-only">Content pages</caption>
                        <thead>
                            <tr>
                                <th scope="col">Title</th>
                                <th scope="col">URL</th>
                                <th scope="col">Editorial</th>
                                <th scope="col"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pages as $page)
                                <tr class="uh-admin-clickrow">
                                    <td class="min-w-56 max-w-72">
                                        <a class="uh-admin-row-main uh-link-quiet font-medium" href="{{ route('admin.cms.edit', $page) }}">{{ $page->title }}</a>
                                    </td>
                                    <td class="text-xs text-[var(--color-muted)]" dir="ltr">/{{ $page->slug }}</td>
                                    <td>
                                        <x-ui.status :status="$page->editorialStatus()" />
                                        @if($page->hasPendingChanges())<x-ui.badge tone="warn" class="ml-1">Unpublished changes</x-ui.badge>@endif
                                    </td>
                                    <td class="uh-admin-row-actions">
                                        <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.cms.edit', $page) }}">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <x-ui.empty icon="document" title="No pages yet"
                        description="Create pages like “About us” or “Privacy policy” to publish alongside your listings.">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.cms.create') }}">New page</a>
            </x-ui.empty>
        @endif
    </section>

    @if($blocks->isNotEmpty())
        <section class="mt-10" aria-labelledby="blocks-heading">
            <h2 id="blocks-heading" class="uh-h3">Homepage and site blocks</h2>
            <p class="mt-2 max-w-2xl text-sm text-[var(--color-muted)]">
                {{ $canPublish ? 'Changes go live as soon as you save. Hidden blocks fall back to the built-in text.' : 'Your changes are saved as a draft until a publisher approves them.' }}
            </p>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @foreach($blocks as $block)
                    @php($fields = \Illuminate\Support\Arr::dot($block->draft_content ?? $block->content ?? []))
                    <div class="uh-panel">
                        <form method="POST" action="{{ route('admin.cms.blocks.update', $block) }}" x-data="uhForm" @submit="submit">
                            @csrf
                            @method('PUT')
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <h3 class="uh-h4">{{ $block->label }}</h3>
                                    <p class="mt-1 text-xs text-[var(--color-muted)]" dir="ltr">{{ $block->key }}</p>
                                </div>
                                <span class="flex gap-1.5">
                                    @if($block->hasDraft())<x-ui.badge tone="warn">Draft pending</x-ui.badge>@endif
                                    <x-ui.badge :tone="$block->is_visible ? 'success' : 'neutral'">{{ $block->is_visible ? 'Visible' : 'Hidden' }}</x-ui.badge>
                                </span>
                            </div>

                            @if($fields !== [])
                                <div class="mt-4 space-y-3">
                                    @foreach($fields as $path => $value)
                                        @php($fieldName = 'content['.implode('][', explode('.', $path)).']')
                                        @php($fieldId = 'block-'.$block->id.'-'.\Illuminate\Support\Str::slug($path))
                                        <div class="uh-field">
                                            <label class="uh-label" for="{{ $fieldId }}">
                                                {{ \Illuminate\Support\Str::of($path)->replace('.', ' → ')->replace('_', ' ')->ucfirst() }}
                                            </label>
                                            @if(is_string($value) && mb_strlen($value) > 90)
                                                <textarea id="{{ $fieldId }}" class="uh-textarea" name="{{ $fieldName }}" rows="3" maxlength="2000">{{ $value }}</textarea>
                                            @else
                                                <input id="{{ $fieldId }}" class="uh-input" name="{{ $fieldName }}" value="{{ $value }}" maxlength="2000">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($canPublish)
                                <div class="mt-4 flex flex-wrap items-end gap-4">
                                    <div class="uh-field">
                                        <input type="hidden" name="is_visible" value="0">
                                        <label class="uh-check"><input type="checkbox" name="is_visible" value="1" @checked($block->is_visible)> <span>Visible</span></label>
                                    </div>
                                    <div class="w-24">
                                        <x-ui.input name="sort_order" label="Order" type="number" min="0" max="999" :value="$block->sort_order" :id="'block-order-'.$block->id" />
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                <button type="submit" class="uh-btn-primary uh-btn-sm" :disabled="submitting">
                                    <span class="uh-spinner" x-show="submitting" x-cloak></span>
                                    {{ $canPublish ? 'Save and publish' : 'Save draft' }}
                                </button>
                                @if($block->updated_at)
                                    <p class="text-xs text-[var(--color-muted)]">Updated {{ \App\Support\DisplayTimezone::format($block->updated_at) }}</p>
                                @endif
                            </div>
                        </form>
                        @if($canPublish && $block->hasDraft())
                            <form method="POST" action="{{ route('admin.cms.blocks.publish', $block) }}" class="mt-3 border-t border-line pt-3">
                                @csrf
                                <button type="submit" class="uh-btn-outline uh-btn-sm">Publish the editor’s draft</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
