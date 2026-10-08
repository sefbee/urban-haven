<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\SeoFields;
use App\Support\TaggedCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('create', Post::class);

        return view('admin.posts.categories', [
            'categories' => PostCategory::query()->with('seoOverride')->withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PostCategory $category, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('create', Post::class);

        if ($request->has('slug')) {
            $request->merge(['slug' => SeoFields::normaliseSlug($request->input('slug'))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('post_categories', 'name')->ignore($category->id)],
            'slug' => SeoFields::slugRules('post_categories', $category),
            ...SeoFields::rules(),
        ]);

        $old = $category->only(['name', 'slug']);
        $category->name = $validated['name'];

        if (filled($validated['slug'] ?? null)) {
            $category->slug = $validated['slug'];
        }

        $category->save();
        SeoFields::sync($category, $validated);
        $audit->record($request->user()->id, 'post_category.updated', PostCategory::class, $category->id, $old, $category->only(['name', 'slug']), $request->ip());
        TaggedCache::flush(['cms', 'sitemap']);

        return back()->with('status', $category->name.' updated.');
    }

    public function destroy(Request $request, PostCategory $category, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('create', Post::class);

        $count = $category->posts()->count();
        $category->posts()->update(['post_category_id' => null]);
        $category->seoOverride()->delete();
        $audit->record($request->user()->id, 'post_category.deleted', PostCategory::class, $category->id, $category->only(['name', 'slug']), null, $request->ip());
        $category->delete();
        TaggedCache::flush(['cms', 'sitemap']);

        return back()->with('status', $count > 0
            ? 'Category deleted. '.$count.' '.str('article')->plural($count).' now have no category.'
            : 'Category deleted.');
    }
}
