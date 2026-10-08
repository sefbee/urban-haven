@extends('layouts.admin')
@section('title', 'Article categories')

@php
    $drawer = $errors->any() ? old('_drawer', 'create') : null;
@endphp

@section('content')
    <div x-data="uhAdminDrawers(@js($drawer))">
        <x-ui.page-header compact title="Article categories"
                          description="Group articles by topic. Each category has its own address and search engine settings for its article list.">
            <x-slot:eyebrow>Content library</x-slot:eyebrow>
            <x-slot:actions>
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">
                    <x-icon name="plus" class="size-4" />
                    Add category
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.admin-related label="Also on the site">
            <a href="{{ route('admin.posts.index') }}">Articles</a>
            <a href="{{ route('admin.page-seo.index') }}">Page SEO</a>
        </x-ui.admin-related>

        @if($categories->isNotEmpty())
            <div class="uh-panel-flush overflow-hidden">
                <div class="uh-table-scroll">
                    <table class="uh-table">
                        <caption class="sr-only">Article categories</caption>
                        <thead>
                            <tr>
                                <th scope="col">Category</th>
                                <th scope="col">Address</th>
                                <th scope="col">Articles</th>
                                <th scope="col">SEO</th>
                                <th scope="col"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td class="min-w-44 font-medium">{{ $category->name }}</td>
                                    <td><a class="uh-link text-xs" dir="ltr" href="{{ route('articles.index', ['category' => $category->slug]) }}" target="_blank" rel="noopener">/articles?category={{ $category->slug }}</a></td>
                                    <td class="uh-numeric">{{ $category->posts_count }}</td>
                                    <td>
                                        @if(filled($category->seoOverride?->meta_title) || filled($category->seoOverride?->meta_description))
                                            <x-ui.badge tone="success">Set</x-ui.badge>
                                        @else
                                            <x-ui.badge tone="outline">Default</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="uh-admin-row-actions">
                                        <button type="button" class="uh-btn-ghost uh-btn-sm" @click="open('edit-{{ $category->id }}')">Edit</button>
                                        <form method="POST" action="{{ route('admin.post-categories.destroy', $category) }}"
                                              x-data="uhConfirm(@js('Delete '.$category->name.'? Its articles stay published without a category.'))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="uh-btn-ghost uh-btn-sm text-[var(--color-danger)]" @click="confirm($event)">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <x-ui.empty icon="tag" title="No categories yet" description="Add topics such as buying guides, area guides or market news.">
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">Add category</button>
            </x-ui.empty>
        @endif

        <x-ui.admin-drawer name="create" title="Add a category">
            <form method="POST" action="{{ route('admin.post-categories.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_drawer" value="create">
                <x-ui.input name="name" id="category-name" label="Category name" required maxlength="80"
                            hint="The address is generated from the name. You can change it and add SEO after saving." />
                <button type="submit" class="uh-btn-primary uh-btn-block">
                    <x-icon name="plus" class="size-3.5" />
                    Add category
                </button>
            </form>
        </x-ui.admin-drawer>

        @foreach($categories as $category)
            <x-ui.admin-drawer :name="'edit-'.$category->id" :title="'Edit '.$category->name">
                <form method="POST" action="{{ route('admin.post-categories.update', $category) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_drawer" value="edit-{{ $category->id }}">
                    <x-ui.input name="name" label="Category name" :value="$category->name" required maxlength="80" :id="'cn-'.$category->id" />

                    @include('admin.partials.seo-panel', [
                        'seo' => $category->seoOverride,
                        'slugName' => 'slug',
                        'slug' => $category->slug,
                        'autoSlug' => false,
                        'baseUrl' => url('/articles').'?category=',
                        'titleSource' => 'name',
                        'fallbackTitle' => $category->name.' articles',
                        'idPrefix' => 'category-seo-'.$category->id,
                    ])

                    <button type="submit" class="uh-btn-primary uh-btn-block">Save category</button>
                </form>
            </x-ui.admin-drawer>
        @endforeach
    </div>
@endsection
