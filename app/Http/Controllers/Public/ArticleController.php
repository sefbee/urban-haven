<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\ArticlesPageSections;
use App\Support\SeoMeta;
use App\Support\StructuredData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articlesSections = ArticlesPageSections::visible();
        $gridLimit = (int) ($articlesSections['grid']['limit'] ?? 12);
        $categoriesLimit = (int) ($articlesSections['categories']['limit'] ?? 6);
        $featuredLimit = (int) ($articlesSections['featured']['limit'] ?? 5);

        $category = $request->filled('category')
            ? PostCategory::query()->with('seoOverride')->where('slug', $request->string('category'))->first()
            : null;

        $posts = Post::query()
            ->published()
            ->with(['category', 'media', 'publicationState'])
            ->when($category, fn ($query) => $query->where('post_category_id', $category->id))
            ->latest('id')
            ->paginate($gridLimit > 0 ? $gridLimit : 12)
            ->withQueryString();

        $categories = PostCategory::query()
            ->whereHas('posts', fn ($query) => $query->published())
            ->orderBy('name')
            ->get();

        $featuredPosts = collect();
        if (isset($articlesSections['featured']) && ! $category) {
            $featuredPosts = Post::query()
                ->published()
                ->with(['category', 'media', 'publicationState'])
                ->latest('id')
                ->limit($featuredLimit)
                ->get();
        }

        $showcasePosts = collect();

        if (isset($articlesSections['categories']) && ! $category && $posts->onFirstPage()) {
            $showcasePosts = $categories->take($categoriesLimit)
                ->mapWithKeys(fn (PostCategory $showcaseCategory) => [
                    $showcaseCategory->id => $showcaseCategory->posts()
                        ->published()
                        ->with(['category', 'media', 'publicationState'])
                        ->latest('id')
                        ->limit(4)
                        ->get(),
                ]);
        }

        return view('public.articles.index', [
            'posts' => $posts,
            'featuredPosts' => $featuredPosts,
            'categories' => $categories,
            'showcasePosts' => $showcasePosts,
            'activeCategory' => $category,
            'articlesSections' => $articlesSections,
            'seo' => SeoMeta::for($category, $category ? $category->name.' articles' : 'Guides and articles', 'Practical guides on buying, renting and investing in property in Dhaka from the Urban Haven team.', [
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
