@extends('layouts.admin')
@section('title', 'Articles')

@section('content')
    <x-ui.page-header compact title="Articles" description="Guides and news published under /articles.">
        <x-slot:actions>
            <form method="GET" class="flex items-center gap-2">
                <label class="sr-only" for="post-search">Search articles</label>
                <input id="post-search" class="uh-input min-h-9 py-1.5 text-sm" type="search" name="q" value="{{ request('q') }}" placeholder="Title">
                <button type="submit" class="uh-btn-outline uh-btn-sm">Search</button>
            </form>
            @can('create', \App\Models\Post::class)
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.posts.create') }}"><x-icon name="plus" class="size-4" /> New article</a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if($posts->isNotEmpty())
                <div class="uh-panel-flush overflow-hidden">
                    <div class="uh-table-scroll">
                        <table class="uh-table">
                            <caption class="sr-only">Articles</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Title</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">State</th>
                                    <th scope="col">Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($posts as $post)
                                    <tr>
                                        <td class="min-w-56">
                                            <a class="uh-link-quiet font-medium" href="{{ route('admin.posts.edit', $post) }}">{{ $post->title }}</a>
                                            @if($post->hasPendingChanges())<x-ui.badge tone="warn" class="ml-1">Unpublished changes</x-ui.badge>@endif
                                        </td>
                                        <td class="text-sm">{{ $post->category?->name ?? '—' }}</td>
                                        <td><x-ui.status :status="$post->editorialStatus()" /></td>
                                        <td class="whitespace-nowrap text-xs text-[var(--color-muted)]">{{ \App\Support\DisplayTimezone::format($post->updated_at) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($posts->hasPages())
                    <div class="mt-6">{{ $posts->links() }}</div>
                @endif
            @else
                <x-ui.empty icon="document" title="No articles yet" description="Write buying and renting guides that answer real customer questions." />
            @endif
        </div>

        <section class="uh-panel h-fit">
            <h2 class="uh-h4">Categories</h2>
            @if($categories->isNotEmpty())
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach($categories as $category)
                        <li><x-ui.badge>{{ $category->name }}</x-ui.badge></li>
                    @endforeach
                </ul>
            @endif
            @can('create', \App\Models\Post::class)
                <form method="POST" action="{{ route('admin.post-categories.store') }}" class="mt-4 space-y-2">
                    @csrf
                    <x-ui.input name="name" label="New category" required maxlength="80" />
                    <button type="submit" class="uh-btn-outline uh-btn-sm">Add category</button>
                </form>
            @endcan
        </section>
    </div>
@endsection
