@extends('layouts.admin')
@section('title', $post->exists ? $post->title : 'New article')

@php
    $exists = $post->exists;
    $draft = fn (string $field) => $exists ? ($post->pending_changes[$field] ?? $post->{$field}) : null;
    $related = array_map('intval', (array) old('related_post_ids', $draft('related_post_ids') ?? []));
    $cover = $exists ? $post->media->firstWhere('collection', 'gallery') : null;
@endphp

@section('content')
    <x-ui.page-header compact :title="$exists ? $post->title : 'New article'">
        <x-slot:eyebrow>Content library · {{ $exists ? 'Edit article' : 'New article' }}</x-slot:eyebrow>
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
        <form method="POST" action="{{ $exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}" enctype="multipart/form-data" class="uh-admin-compose-main" x-data="{{ $exists ? 'uhForm' : 'uhPostCreateForm' }}" @submit="submit" data-unsaved-guard>
            @csrf
            @if($exists) @method('PUT') @endif

            <div class="uh-admin-stack dd-form-steps">
            <section class="uh-panel">
                <h2 class="uh-h4">Topic</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="title" label="Title" :value="$draft('title')" :autofocus="! $exists" required maxlength="255" class="sm:col-span-2"
                                hint="A clear promise, e.g. “How to check a flat’s RAJUK approval”. The web address is generated from it." />
                    <x-ui.select name="post_category_id" label="Category" optional quick-add="category">
                        <option value="">No category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('post_category_id', $draft('post_category_id')) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input name="author_label" label="Author" :value="$draft('author_label')" optional maxlength="120"
                                :placeholder="\App\Models\Setting::get('company_name', config('app.name')).' team'" />
                </div>
            </section>

            <section class="uh-panel">
                <h2 class="uh-h4">Content</h2>
                <div class="mt-4 space-y-4">
                    <x-ui.textarea name="excerpt" label="Summary" rows="2" maxlength="300" optional :value="$draft('excerpt')" hint="Shown on article cards. Two sentences is plenty." />
                    <x-ui.rich-text name="body" label="Article" :value="$draft('body')" min-height="22rem"
                                    hint="Use Heading for sections and lists for steps. Pasting from Word or Google Docs keeps the formatting." />
                </div>
            </section>

            @unless($exists)
                <section class="uh-panel">
                    <section class="uh-admin-media" aria-labelledby="article-photos-heading">
                        <div class="uh-admin-media-heading">
                            <span class="uh-admin-media-heading-icon" aria-hidden="true"><x-icon name="image" class="size-4" /></span>
                            <h2 id="article-photos-heading" class="uh-h4">Photographs</h2>
                            <span class="text-xs text-[var(--admin-muted)]" x-text="`(${photos.length})`">(0)</span>
                        </div>

                        <p class="uh-admin-media-hint">
                            <x-icon name="info" class="size-4" />
                            <span>The maximum photograph size is {{ max(1, (int) ceil(((int) config('urbanhaven.media.max_image_kb')) / 1024)) }} MB. Formats: JPEG, PNG, WebP. The first photograph is used as the cover. Add up to 20 photos.</span>
                        </p>

                        <div class="uh-admin-media-drop mt-3" :class="{ 'is-dragging': dragging }"
                             @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dropPhotos($event)">
                            <input id="article-photos" x-ref="photoInput" class="uh-admin-media-file" type="file" name="cover_images[]" accept="image/jpeg,image/png,image/webp" multiple @change="selectPhotos($event)">
                            <div class="grid justify-items-center gap-1">
                                <button type="button" class="uh-admin-media-trigger" @click="$refs.photoInput.click()">
                                    <x-icon name="upload" class="size-4" />
                                    <span>Upload photographs</span>
                                </button>
                                <span class="uh-admin-media-drop-hint">or drag photos here (up to 20 at once)</span>
                            </div>
                        </div>
                        <p class="uh-error" x-show="photoLimitExceeded" x-cloak>Choose no more than 20 photographs at once.</p>

                        <ul class="uh-admin-media-grid grid-cols-2 sm:grid-cols-4" x-show="photos.length > 0" x-cloak>
                            <template x-for="(photo, index) in photos" :key="photo.id">
                                <li class="uh-admin-media-card">
                                    <div class="uh-admin-media-card-bar" :class="index === 0 ? 'is-cover' : 'is-ready'">
                                        <div class="uh-admin-media-card-copy">
                                            <p class="uh-admin-media-name" x-text="photo.name" :title="photo.name"></p>
                                            <p class="uh-admin-media-size" x-text="formatPhotoSize(photo.size)"></p>
                                        </div>
                                        <button type="button" class="uh-icon-btn size-6 text-white/80 hover:text-white disabled:opacity-25"
                                                :disabled="index === 0" @click="movePhoto(index, -1)" aria-label="Move photograph earlier" title="Move earlier">
                                            <x-icon name="chevron-left" class="size-3.5" />
                                        </button>
                                        <button type="button" class="uh-icon-btn size-6 text-white/80 hover:text-white disabled:opacity-25"
                                                :disabled="index === photos.length - 1" @click="movePhoto(index, 1)" aria-label="Move photograph later" title="Move later">
                                            <x-icon name="chevron-right" class="size-3.5" />
                                        </button>
                                        <button type="button" class="uh-admin-media-remove" @click="removePhoto(index)" aria-label="Remove photograph" title="Remove">
                                            <x-icon name="close" class="size-3.5" />
                                        </button>
                                    </div>
                                    <div class="uh-admin-media-preview"><img :src="photo.previewUrl" :alt="photo.altText || ''"></div>
                                    <div class="uh-admin-media-details">
                                        <span class="text-[10px] font-semibold text-[#2f4ac7]" x-show="index === 0">Cover image</span>
                                        <input type="hidden" :name="`cover_images_meta[${index}][alt_text]`" :value="photo.altText">
                                    </div>
                                </li>
                            </template>
                        </ul>
                        @error('cover_images')<p class="uh-error"><x-icon name="alert" class="size-3.5 shrink-0" /> <span>{{ $message }}</span></p>@enderror
                        @foreach($errors->getMessages() as $field => $messages)
                            @if(str_starts_with($field, 'cover_images.'))
                                <p class="uh-error"><x-icon name="alert" class="size-3.5 shrink-0" /> <span>{{ $messages[0] }}</span></p>
                            @endif
                        @endforeach
                    </section>
                </section>
            @endunless

            @if($otherPosts->isNotEmpty())
                <section class="uh-panel">
                    <h2 class="uh-h4">Related articles</h2>
                    <div class="mt-3 grid gap-x-6 gap-y-1 sm:grid-cols-2">
                        @foreach($otherPosts as $other)
                            <label class="uh-check"><input type="checkbox" name="related_post_ids[]" value="{{ $other->id }}" @checked(in_array($other->id, $related, true))> <span>{{ $other->title }}</span></label>
                        @endforeach
                    </div>
                    @error('related_post_ids')<p class="uh-error">{{ $message }}</p>@enderror
                </section>
            @endif

            @include('admin.partials.seo-panel', [
                'seo' => $exists ? $post->seoOverride : null,
                'slugName' => 'slug',
                'slug' => $draft('slug'),
                'baseUrl' => url('/articles'),
                'titleSource' => 'title',
                'contentSource' => 'excerpt',
                'idPrefix' => 'post-seo',
            ])

            </div>
            <div class="uh-admin-dock">
                @if(! $exists && $canPublish)
                    <button type="submit" name="submit_action" value="draft" class="uh-btn-outline" :disabled="submitting">
                        <span>{{ __('Save as draft') }}</span>
                    </button>
                    <button type="submit" name="submit_action" value="publish" class="uh-btn-primary" :disabled="submitting">
                        <span class="uh-spinner" x-show="submitting" x-cloak></span>
                        <span>{{ __('Save & publish') }}</span>
                    </button>
                @else
                    <button type="submit" @unless($exists) name="submit_action" value="draft" @endunless class="uh-btn-primary" :disabled="submitting">
                        <span class="uh-spinner" x-show="submitting" x-cloak></span>
                        <span>{{ $exists ? 'Save changes' : 'Save draft' }}</span>
                    </button>
                @endif
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
                        <div class="uh-admin-media-drop" :class="{ 'is-dragging': dragging }" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dropped($event, true)">
                            <button type="button" class="uh-admin-media-trigger" :disabled="submitting" @click="pick()">
                                <x-icon name="upload" class="size-4" />
                                <span x-text="submitting ? 'Uploading…' : 'Upload photograph'">Upload photograph</span>
                            </button>
                            <span class="uh-admin-media-drop-hint">or drag a photo here</span>
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
                            <div class="uh-admin-media-preview" :class="{ 'is-multi': previews.length > 1 }">
                    <template x-for="src in previews" :key="src"><img :src="src" alt=""></template>
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
                                    <button type="submit" class="uh-btn-outline uh-btn-sm" style="display: none;">Save</button>
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
