<?php

namespace App\Services\Seo;

use App\Http\Controllers\Public\SitemapController;
use App\Models\CmsPage;
use App\Models\LocationArea;
use App\Models\Post;
use App\Models\Project;
use App\Models\Property;
use App\Models\Setting;
use App\Support\TaggedCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Split sitemaps of published, indexable URLs only. Records marked noindex and archived listings are excluded.
 */
class SitemapGenerator
{
    public function index(): string
    {
        return TaggedCache::remember(['sitemap'], 'sitemap.index', 3600, function (): string {
            $entries = collect(SitemapController::SECTIONS)
                ->map(fn (string $section): string => '<sitemap><loc>'.e(route('sitemap.section', $section)).'</loc><lastmod>'.now()->toAtomString().'</lastmod></sitemap>')
                ->implode('');

            return '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$entries.'</sitemapindex>';
        });
    }

    public function section(string $section): string
    {
        return TaggedCache::remember(['sitemap'], 'sitemap.'.$section, 3600, fn (): string => $this->urlset(match ($section) {
            'pages' => $this->pages(),
            'properties' => $this->properties(),
            'projects' => $this->projects(),
            'locations' => $this->locations(),
            'articles' => $this->articles(),
            default => [],
        }));
    }

    /**
     * @return list<array{loc: string, lastmod: mixed}>
     */
    private function pages(): array
    {
        $urls = [
            ['loc' => route('home'), 'lastmod' => now()],
            ['loc' => route('properties.index'), 'lastmod' => now()],
            ['loc' => route('projects.index'), 'lastmod' => now()],
        ];

        foreach (Setting::enabledPurposes() as $purpose) {
            $urls[] = ['loc' => route('properties.index', ['listing_type' => $purpose]), 'lastmod' => now()];
        }

        foreach ($this->indexable(CmsPage::query()->published())->orderBy('id')->get(['id', 'slug', 'updated_at']) as $page) {
            $urls[] = ['loc' => route('cms.show', $page->slug), 'lastmod' => $page->updated_at];
        }

        $urls[] = ['loc' => route('faq'), 'lastmod' => now()];

        return $urls;
    }

    /**
     * @return list<array{loc: string, lastmod: mixed}>
     */
    private function properties(): array
    {
        return $this->indexable(Property::query()->published())
            ->whereIn('listing_type', Setting::enabledPurposes() ?: ['__none__'])
            ->where('availability', '!=', 'off-market')
            ->orderBy('id')
            ->get(['id', 'slug', 'last_updated_at', 'updated_at'])
            ->map(fn (Property $property): array => ['loc' => route('properties.show', $property->slug), 'lastmod' => $property->last_updated_at ?? $property->updated_at])
            ->all();
    }

    /**
     * @return list<array{loc: string, lastmod: mixed}>
     */
    private function projects(): array
    {
        return $this->indexable(Project::query()->published())
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (Project $project): array => ['loc' => route('projects.show', $project->slug), 'lastmod' => $project->updated_at])
            ->all();
    }

    /**
     * @return list<array{loc: string, lastmod: mixed}>
     */
    private function locations(): array
    {
        return LocationArea::query()
            ->active()
            ->whereNotNull('intro')
            ->where('intro', '!=', '')
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (LocationArea $area): array => ['loc' => route('locations.show', $area->slug), 'lastmod' => $area->updated_at])
            ->all();
    }

    /**
     * @return list<array{loc: string, lastmod: mixed}>
     */
    private function articles(): array
    {
        $urls = $this->indexable(Post::query()->published())
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (Post $post): array => ['loc' => route('articles.show', $post->slug), 'lastmod' => $post->updated_at])
            ->all();

        return $urls === [] ? [] : [['loc' => route('articles.index'), 'lastmod' => now()], ...$urls];
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function indexable(Builder $query): Builder
    {
        return $query->whereDoesntHave('seoOverride', fn (Builder $seo) => $seo->where('noindex', true));
    }

    /**
     * @param  list<array{loc: string, lastmod: mixed}>  $urls
     */
    private function urlset(array $urls): string
    {
        $body = collect($urls)->map(function (array $url): string {
            $lastmod = $url['lastmod'] ? Carbon::parse($url['lastmod'])->toAtomString() : now()->toAtomString();

            return '<url><loc>'.e($url['loc']).'</loc><lastmod>'.$lastmod.'</lastmod></url>';
        })->implode('');

        return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$body.'</urlset>';
    }
}
