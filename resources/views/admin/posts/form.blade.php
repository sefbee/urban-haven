@extends('layouts.admin')
@section('title', $post->exists ? $post->title : 'New article')

@php
    $exists = $post->exists;
    $draft = fn (string $field) => $exists ? ($post->pending_changes[$field] ?? $post->{$field}) : null;
    $related = array_map('intval', (array) old('related_post_ids', $draft('related_post_ids') ?? []));
    $cover = $exists ? $post->media->firstWhere('collection', 'gallery') : null;
@endphp

@section('content')
    <x-ui.page-header compact :title="$exists ? $post->title : 'New article'"
                      :description="$canPublish ? 'Saved changes go live when the article is published.' : 'Changes to a live article wait for a publisher before they appear.'">
        <x-slot:eyebrow>{{ $exists ? 'Edit article' : 'New article' }}</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.posts.index') }}"><x-icon name="chevron-left" class="size-4" /> All articles</a>
            @if($exists)
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.posts.preview', $post) }}" target="_blank" rel="noopener">Preview</a>
            @endif
            @if($exists && $post->isPublished())
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('articles.show', $post->slug) }}" target="_blank" rel="noopener"><x-icon name="external" class="size-4" /> View live</a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if($exists && $post->hasPendingChanges())
        <x-ui.alert tone="warn" class="mt-6">This article has saved changes that are not live yet.</x-ui.alert>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ $exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}" class="space-y-6 lg:col-span-2" x-data="uhForm" @submit="submit">
            @csrf
            @if($exists) @method('PUT') @endif

            <section class="uh-panel space-y-4">
                <x-ui.input name="title" label="Title" :value="$draft('title')" required maxlength="255" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="slug" label="URL" :value="$draft('slug')" optional maxlength="120" dir="ltr" hint="Lowercase letters, numbers and dashes." />
                    <x-ui.select name="post_category_id" label="Category" optional>
                        <option value="">No category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('post_category_id', $draft('post_category_id')) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <x-ui.textarea name="excerpt" label="Summary" rows="2" maxlength="300" optional :value="$draft('excerpt')" hint="Shown on article cards and as the search description." />
                <x-ui.textarea name="body" label="Body" rows="18" required :value="$draft('body')" hint="Basic HTML is allowed and cleaned on save." />
                <x-ui.input name="author_label" label="Author" :value="$draft('author_label')" optional maxlength="120" placeholder="Urban Haven sales team" />
            </section>

            @if($otherPosts->isNotEmpty())
                <section class="uh-panel">
                    <h2 class="uh-h4">Related articles</h2>
                    <p class="mt-1 text-xs text-[var(--color-muted)]">Pick up to 6. Others from the same category fill remaining slots.</p>
                    <div class="mt-3 grid gap-x-6 gap-y-1 sm:grid-cols-2">
                        @foreach($otherPosts as $other)
                            <label class="uh-check"><input type="checkbox" name="related_post_ids[]" value="{{ $other->id }}" @checked(in_array($other->id, $related, true))> <span>{{ $other->title }}</span></label>
                        @endforeach
                    </div>
                    @error('related_post_ids')<p class="uh-error">{{ $message }}</p>@enderror
                </section>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="uh-btn-primary" :disabled="submitting">
                    <span class="uh-spinner" x-show="submitting" x-cloak></span>
                    <span>{{ $exists ? 'Save changes' : 'Save draft' }}</span>
                </button>
                <a class="uh-btn-ghost" href="{{ route('admin.posts.index') }}">Cancel</a>
            </div>
        </form>

        @if($exists)
            <div class="space-y-6">
                <section class="uh-panel">
                    <h2 class="uh-h4">Publication</h2>
                    <p class="mt-2"><x-ui.status :status="$post->editorialStatus()" /></p>
                    @if($canPublish)
                        @if(! $post->isPublished() || $post->hasPendingChanges())
                            <form method="POST" action="{{ route('admin.posts.publish', $post) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block">{{ $post->isPublished() ? 'Publish changes' : 'Publish' }}</button>
                            </form>
                        @endif
                        @if($post->isPublished())
                            <form method="POST" action="{{ route('admin.posts.unpublish', $post) }}" class="mt-2" x-data="uhConfirm('Unpublish this article?')">
                                @csrf
                                <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block" @click="confirm($event)">Unpublish</button>
                            </form>
                        @endif
                    @else
                        <p class="mt-3 text-xs text-[var(--color-muted)]">A publisher makes articles live.</p>
                    @endif
                </section>

                <section class="uh-panel">
                    <h2 class="uh-h4">Cover image</h2>
                    @if($cover)
                        <img src="{{ $cover->thumbUrl() }}" alt="" class="mt-3 aspect-video w-full rounded-lg object-cover">
                        <form method="POST" action="{{ route('admin.media.update', $cover) }}" class="mt-3 space-y-2">
                            @csrf
                            @method('PATCH')
                            <x-ui.input name="alt_text" label="Alt text" :value="$cover->alt_texts['en'] ?? ''" maxlength="200" id="cover-alt" />
                            <input type="hidden" name="is_public" value="1">
                            <button type="submit" class="uh-btn-outline uh-btn-sm">Save</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="mt-3 space-y-2">
                        @csrf
                        <input type="hidden" name="owner_type" value="post">
                        <input type="hidden" name="owner_id" value="{{ $post->id }}">
                        <input type="hidden" name="collection" value="gallery">
                        <input class="uh-input py-2 text-xs" type="file" name="file" accept="image/jpeg,image/png,image/webp" required aria-label="Cover image file">
                        <x-ui.input name="alt_text" label="Alt text" maxlength="200" id="cover-new-alt" />
                        <button type="submit" class="uh-btn-outline uh-btn-sm">Upload</button>
                    </form>
                </section>

                @can('delete', $post)
                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="uh-panel" x-data="uhConfirm('Delete this article? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="uh-btn-danger uh-btn-sm uh-btn-block" @click="confirm($event)">Delete article</button>
                    </form>
                @endcan
            </div>
        @endif
    </div>
@endsection
