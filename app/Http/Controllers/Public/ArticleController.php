<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsBlock;
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
        $latestLimit = (int) ($articlesSections['latest']['limit'] ?? 4);
        $search = trim((string) $request->string('q'));

        $category = $request->filled('category')
            ? PostCategory::query()->with('seoOverride')->where('slug', $request->string('category'))->first()
            : null;

        $posts = Post::query()
            ->published()
            ->with(['category', 'media', 'publicationState'])
            ->when($category, fn ($query) => $query->where('post_category_id', $category->id))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', '%'.addcslashes($search, '%_\\').'%')
                    ->orWhere('excerpt', 'like', '%'.addcslashes($search, '%_\\').'%');
            }))
            ->latest('id')
            ->paginate($gridLimit > 0 ? $gridLimit : 12)
            ->withQueryString();

        $categories = PostCategory::query()
            ->withCount(['posts as published_posts_count' => fn ($query) => $query->published()])
            ->with(['posts' => fn ($query) => $query->published()->with('media')->latest('id')->limit(1)])
            ->whereHas('posts', fn ($query) => $query->published())
            ->orderBy('name')
            ->get();

        $featuredPost = null;
        if (isset($articlesSections['featured']['post_id']) && ! $category && $search === '') {
            $featuredPost = Post::query()->published()->with(['category', 'media', 'publicationState'])
                ->find($articlesSections['featured']['post_id']);
        }

        $latestArticles = collect();
        if (isset($articlesSections['latest']) && ! $category && $search === '') {
            $latestArticles = Post::query()->published()
                ->when($featuredPost, fn ($query) => $query->whereKeyNot($featuredPost->id))
                ->with(['category', 'media', 'publicationState'])
                ->latest('id')->limit(max(1, $latestLimit))->get();
        }

        $heroImage = CmsBlock::imagesFor('articles_masthead')->first()
            ?? $featuredPost?->featuredImage()
            ?? $latestArticles->first()?->featuredImage();

        return view('public.articles.index', [
            'posts' => $posts,
            'featuredPost' => $featuredPost,
            'latestArticles' => $latestArticles,
            'categories' => $categories,
            'heroImage' => $heroImage,
            'activeCategory' => $category,
            'search' => $search,
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
