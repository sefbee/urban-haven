@extends('layouts.admin')
@section('title', 'Pages')

@section('content')
    <x-ui.page-header compact title="Content"
                      description="Standalone pages and the editable blocks used across the public site.">
        <x-slot:actions>
            <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.cms.create') }}">
                <x-icon name="plus" class="size-4" />
                New page
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="mt-7" aria-labelledby="pages-heading">
        <h2 id="pages-heading" class="uh-h3">Pages</h2>

        @if($pages->isNotEmpty())
            <div class="uh-panel-flush mt-4 overflow-hidden">
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
                                <tr>
                                    <td class="min-w-56 max-w-72">
                                        <a class="uh-link-quiet font-medium" href="{{ route('admin.cms.edit', $page) }}">{{ $page->title }}</a>
                                    </td>
                                    <td class="text-xs text-[var(--color-muted)]" dir="ltr">/{{ $page->slug }}</td>
                                    <td><x-ui.status :status="$page->editorialStatus()" /></td>
                                    <td class="whitespace-nowrap text-right">
                                        <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.cms.edit', $page) }}">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <x-ui.empty class="mt-4" icon="document" title="No pages yet"
                        description="Create pages like “About us” or “Privacy policy” to publish alongside your listings.">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.cms.create') }}">New page</a>
            </x-ui.empty>
        @endif
    </section>

    @if($blocks->isNotEmpty())
        <section class="mt-10" aria-labelledby="blocks-heading">
            <h2 id="blocks-heading" class="uh-h3">Site blocks</h2>
            <p class="mt-2 max-w-2xl text-sm text-[var(--color-muted)]">
                These short pieces of text appear on the homepage and in the footer. Each field is saved exactly as typed.
            </p>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @foreach($blocks as $block)
                    @php($fields = \Illuminate\Support\Arr::dot($block->content ?? []))
                    <form method="POST" action="{{ route('admin.cms.blocks.update', $block) }}" class="uh-panel" x-data="uhForm" @submit="submit">
                        @csrf
                        @method('PUT')
                        <h3 class="uh-h4">{{ $block->label }}</h3>
                        <p class="mt-1 text-xs text-[var(--color-muted)]" dir="ltr">{{ $block->key }}</p>

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
                                            <textarea id="{{ $fieldId }}" class="uh-textarea" name="{{ $fieldName }}" rows="3">{{ $value }}</textarea>
                                        @else
                                            <input id="{{ $fieldId }}" class="uh-input" name="{{ $fieldName }}" value="{{ $value }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-4 text-sm text-[var(--color-muted)]">This block has no fields yet.</p>
                        @endif

                        <div class="mt-4 flex items-center gap-3">
                            <button type="submit" class="uh-btn-primary uh-btn-sm" :disabled="submitting">
                                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                                Save block
                            </button>
                            @if($block->updated_at)
                                <p class="text-xs text-[var(--color-muted)]">
                                    Updated {{ \App\Support\DisplayTimezone::format($block->updated_at) }}
                                </p>
                            @endif
                        </div>
                    </form>
                @endforeach
            </div>
        </section>
    @endif
@endsection
