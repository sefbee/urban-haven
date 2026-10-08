<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\LocationArea;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Property;
use App\Support\PageSeo;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Every public address on the website in one list, for building menus, sharing links and
 * checking which pages have search engine settings.
 */
class SiteUrlController extends Controller
{
    private const LIMIT = 500;

    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->hasPermission('cms.update') || $request->user()->hasPermission('cms.create'), 403);

        $pageSeo = PageSeo::all();
        $hasSeo = fn (?object $seo): bool => filled($seo?->meta_title) || filled($seo?->meta_description);

        $groups = [
            'Fixed pages' => collect(PageSeo::PAGES)->map(fn (array $page, string $route): array => [
                'label' => $page['label'],
                'type' => 'Static page',
                'path' => $page['path'],
                'live' => true,
                'seo' => filled($pageSeo[$route]['meta_title']) || filled($pageSeo[$route]['meta_description']),
                'edit' => route('admin.page-seo.index', ['page' => $route]),
            ])->values()->all(),

            'Pages' => CmsPage::query()->with(['publicationState', 'seoOverride'])->orderBy('title')->limit(self::LIMIT)->get()
                ->map(fn (CmsPage $page): array => [
                    'label' => $page->title,
                    'type' => $page->template === 'default'
                        ? 'Custom CMS page'
                        : 'Dynamic module · '.str($page->template)->headline(),
                    'path' => '/'.$page->slug,
                    'live' => $page->isPublished(),
                    'status' => match ($page->editorialStatus()) {
                        'published' => 'Live',
                        'unpublished' => 'Hidden',
                        default => 'Draft',
                    },
                    'seo' => $hasSeo($page->seoOverride),
                    'edit' => route('admin.cms.edit', $page),
                    'delete' => request()->user()->can('delete', $page) ? route('admin.cms.destroy', $page) : null,
                ])->all(),

            'Properties' => Property::query()->with(['publicationState', 'seoOverride'])->latest('updated_at')->limit(self::LIMIT)->get(['id', 'title', 'slug', 'updated_at'])
                ->map(fn (Property $property): array => [
                    'label' => $property->title,
                    'type' => 'Property module',
                    'path' => '/properties/'.$property->slug,
                    'live' => $property->isPublished(),
                    'seo' => $hasSeo($property->seoOverride),
                    'edit' => route('admin.properties.edit', $property),
                ])->all(),

            'Areas' => LocationArea::query()->with('seoOverride')->orderBy('name')->limit(self::LIMIT)->get()
                ->map(fn (LocationArea $area): array => [
                    'label' => $area->label(),
                    'type' => 'Area module',
                    'path' => '/locations/'.$area->slug,
                    'live' => $area->hasLandingPage(),
                    'seo' => $hasSeo($area->seoOverride),
                    'edit' => route('admin.areas.index'),
                ])->all(),

            'Articles' => Post::query()->with(['publicationState', 'seoOverride'])->latest('updated_at')->limit(self::LIMIT)->get(['id', 'title', 'slug', 'updated_at'])
                ->map(fn (Post $post): array => [
                    'label' => $post->title,
                    'type' => 'Article module',
                    'path' => '/articles/'.$post->slug,
                    'live' => $post->isPublished(),
                    'seo' => $hasSeo($post->seoOverride),
                    'edit' => route('admin.posts.edit', $post),
                ])->all(),

            'Article categories' => PostCategory::query()->with('seoOverride')->orderBy('name')->get()
                ->map(fn (PostCategory $category): array => [
                    'label' => $category->name,
                    'type' => 'Article category',
                    'path' => '/articles?category='.$category->slug,
                    'live' => true,
                    'seo' => $hasSeo($category->seoOverride),
                    'edit' => route('admin.post-categories.index'),
                ])->all(),
        ];

        return view('admin.site-urls', [
            'groups' => array_filter($groups),
            'canCreatePages' => $request->user()->hasPermission('cms.create'),
            'canManageMenus' => $request->user()->hasPermission('cms.publish'),
            'menuLocations' => MenuItem::LOCATIONS,
            'menuParents' => MenuItem::query()->whereNull('parent_id')->orderBy('sort_order')->orderBy('label')->get(['id', 'location', 'label']),
        ]);
    }
}
