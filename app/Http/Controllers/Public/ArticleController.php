<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\SeoMeta;
use App\Support\StructuredData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->filled('category')
            ? PostCategory::query()->where('slug', $request->string('category'))->first()
            : null;

        $posts = Post::query()
            ->published()
            ->with(['category', 'media', 'publicationState'])
            ->when($category, fn ($query) => $query->where('post_category_id', $category->id))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('public.articles.index', [
            'posts' => $posts,
            'categories' => PostCategory::query()->whereHas('posts', fn ($query) => $query->published())->orderBy('name')->get(),
            'activeCategory' => $category,
            'seo' => SeoMeta::for(null, $category ? $category->name.' articles' : 'Guides and articles', 'Practical guides on buying, renting and investing in property in Dhaka from the Urban Haven team.', [
                'canonical' => SeoMeta::canonical(['category', 'page']),
            ]),
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::query()->published()->with(['category', 'media', 'seoOverride', 'publicationState'])->where('slug', $slug)->firstOrFail();

        return view('public.articles.show', [
            'post' => $post,
            'related' => $post->relatedPosts(),
            'seo' => SeoMeta::for($post, $post->title, $post->excerpt, [
                'type' => 'article',
                'json_ld' => [
                    StructuredData::article($post),
                    StructuredData::breadcrumbs([[__('Home'), route('home')], [__('Articles'), route('articles.index')], [$post->title, route('articles.show', $post->slug)]]),
                ],
            ]),
        ]);
    }
}
