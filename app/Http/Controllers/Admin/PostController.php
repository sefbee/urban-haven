<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\Cms\CmsService;
use App\Support\SeoMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Post::class);

        return view('admin.posts.index', [
            'posts' => Post::query()
                ->with(['publicationState', 'category'])
                ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.addcslashes((string) $request->string('q'), '%_\\').'%'))
                ->latest('updated_at')
                ->paginate(20)
                ->withQueryString(),
            'categories' => PostCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Post::class);

        return view('admin.posts.form', $this->formData(new Post));
    }

    public function store(StorePostRequest $request, CmsService $cms): RedirectResponse
    {
        $post = $cms->createPost($request->validated(), $request->user());

        return redirect()->route('admin.posts.edit', $post)->with('status', 'Article created as a draft.');
    }

    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        return view('admin.posts.form', $this->formData($post->load(['publicationState', 'media'])));
    }

    public function update(StorePostRequest $request, Post $post, CmsService $cms): RedirectResponse
    {
        $cms->updatePost($post, $request->validated(), $request->user());

        return redirect()->route('admin.posts.edit', $post->fresh())->with('status', $post->fresh()->hasPendingChanges()
            ? 'Changes saved and waiting for publish approval. The live article is unchanged.'
            : 'Article updated.');
    }

    public function preview(Post $post, CmsService $cms): Response
    {
        $this->authorize('update', $post);
        $preview = $cms->previewOf($post);

        return response()->view('public.articles.show', [
            'post' => $preview,
            'related' => collect(),
            'isPreview' => true,
            'seo' => SeoMeta::for(null, 'Preview: '.$preview->title, null, ['noindex' => true]),
            'breadcrumbs' => [],
        ])->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function publish(Post $post, CmsService $cms): RedirectResponse
    {
        $this->authorize('publish', $post);
        $cms->publishPost($post, request()->user());

        return back()->with('status', 'Article published.');
    }

    public function unpublish(Post $post, CmsService $cms): RedirectResponse
    {
        $this->authorize('publish', $post);
        $cms->unpublishPost($post, request()->user());

        return back()->with('status', 'Article unpublished.');
    }

    public function destroy(Post $post, CmsService $cms): RedirectResponse
    {
        $this->authorize('delete', $post);
        $cms->deletePost($post, request()->user());

        return redirect()->route('admin.posts.index')->with('status', 'Article deleted.');
    }

    public function storeCategory(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Post::class);
        $validated = $request->validate(['name' => ['required', 'string', 'max:80', 'unique:post_categories,name']]);
        $category = PostCategory::query()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $category->id,
                'label' => $category->name,
            ], 201);
        }

        return back()->with('status', 'Category added.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Post $post): array
    {
        return [
            'post' => $post,
            'categories' => PostCategory::query()->orderBy('name')->get(),
            'otherPosts' => Post::query()->whereKeyNot($post->id ?? 0)->orderBy('title')->get(['id', 'title']),
            'canPublish' => request()->user()->can('publish', $post),
        ];
    }
}
