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
        <x-slot:eyebrow>Website · {{ $exists ? 'Edit article' : 'New article' }}</x-slot:eyebrow>
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

    <div @class(['uh-admin-compose', 'is-split' => $exists])>
        <form method="POST" action="{{ $exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}" class="uh-admin-compose-main" x-data="uhForm" @submit="submit">
            @csrf
            @if($exists) @method('PUT') @endif

            <div class="uh-admin-stack">
            <section class="uh-panel space-y-4">
                <x-ui.input name="title" label="Title" :value="$draft('title')" required maxlength="255" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="slug" label="URL" :value="$draft('slug')" optional maxlength="120" dir="ltr" hint="Lowercase letters, numbers and dashes." />
                    <x-ui.select name="post_category_id" label="Category" optional quick-add="category">
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

            </div>
            <div class="uh-admin-dock">
                <button type="submit" class="uh-btn-primary" :disabled="submitting">
                    <span class="uh-spinner" x-show="submitting" x-cloak></span>
                    <span>{{ $exists ? 'Save changes' : 'Save draft' }}</span>
                </button>
                <a class="uh-btn-ghost" href="{{ route('admin.posts.index') }}">Cancel</a>
            </div>
        </form>

        @if($exists)
            <div class="uh-admin-compose-side">
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

                <section class="uh-panel uh-admin-media" x-data="uhMediaUpload">
                    <div class="uh-admin-media-heading">
                        <span class="uh-admin-media-heading-icon" aria-hidden="true"><x-icon name="image" class="size-4" /></span>
                        <h2 class="uh-h4">Cover image</h2>
                    </div>
                    <p class="uh-admin-media-hint">
                        <x-icon name="info" class="size-4" />
                        <span>The maximum photograph size is {{ max(1, (int) ceil(((int) config('urbanhaven.media.max_image_kb')) / 1024)) }} MB. Formats: JPEG, PNG, WebP.</span>
                    </p>
                    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="uh-admin-media-upload" @submit="submit">
                        @csrf
                        <input type="hidden" name="owner_type" value="post">
                        <input type="hidden" name="owner_id" value="{{ $post->id }}">
                        <input type="hidden" name="collection" value="gallery">
                        <input id="cover-image-file" x-ref="file" class="uh-admin-media-file" type="file" name="file" accept="image/jpeg,image/png,image/webp" required aria-label="Cover image file" @change="chosen(true)">
                        <div class="uh-admin-media-drop">
                            <button type="button" class="uh-admin-media-trigger" :disabled="submitting" @click="pick()">
                                <x-icon name="upload" class="size-4" />
                                <span x-text="submitting ? 'Uploading…' : 'Upload photograph'">Upload photograph</span>
                            </button>
                        </div>
                    </form>
                    <ul class="uh-admin-media-grid" @if(! $cover) x-show="filename" x-cloak @endif>
                        <li class="uh-admin-media-card is-pending" x-show="filename" x-cloak>
                            <div class="uh-admin-media-card-bar" :class="submitting ? 'is-busy' : ''">
                                <div class="uh-admin-media-card-copy">
                                    <p class="uh-admin-media-name" x-text="filename" x-bind:title="filename"></p>
                                    <p class="uh-admin-media-size" x-text="filesize"></p>
                                </div>
                                <span class="uh-admin-media-status-text" x-text="submitting ? 'Uploading…' : 'Ready to upload'"></span>
                                <button type="button" class="uh-admin-media-remove" aria-label="Clear selected file" @click="clear()">
                                    <x-icon name="close" class="size-3.5" />
                                </button>
                            </div>
                            <div class="uh-admin-media-preview">
                                <img x-show="preview" x-bind:src="preview" alt="" x-cloak>
                                <span class="uh-admin-media-slot" x-show="!preview"><x-icon name="image" class="size-8" /></span>
                            </div>
                        </li>
                        @if($cover)
                            <li class="uh-admin-media-card">
                                <div class="uh-admin-media-card-bar is-ready">
                                    <div class="uh-admin-media-card-copy">
                                        <p class="uh-admin-media-name" title="{{ $cover->original_filename }}">{{ $cover->original_filename }}</p>
                                        <p class="uh-admin-media-size">
                                            @if($cover->size_bytes >= 1048576)
                                                {{ number_format($cover->size_bytes / 1048576, 1) }} MB
                                            @else
                                                {{ max(1, (int) round($cover->size_bytes / 1024)) }} KB
                                            @endif
                                        </p>
                                    </div>
                                    <span class="uh-admin-media-status-text">Uploaded</span>
                                </div>
                                <div class="uh-admin-media-preview">
                                    <img src="{{ $cover->thumbUrl() }}" alt="" loading="lazy" decoding="async">
                                </div>
                                <form method="POST" action="{{ route('admin.media.update', $cover) }}" class="uh-admin-media-details">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.input name="alt_text" label="Alt text" :value="$cover->alt_texts['en'] ?? ''" maxlength="200" id="cover-alt" />
                                    <input type="hidden" name="is_public" value="1">
                                    <button type="submit" class="uh-btn-outline uh-btn-sm">Save</button>
                                </form>
                            </li>
                        @endif
                    </ul>
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
