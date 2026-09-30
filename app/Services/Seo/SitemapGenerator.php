<?php

namespace App\Services\Seo;

use App\Models\CmsPage;
use App\Models\Project;
use App\Models\Property;
use App\Support\TaggedCache;
use Illuminate\Support\Carbon;

class SitemapGenerator
{
    public function xml(): string
    {
        return TaggedCache::remember(['sitemap'], 'sitemap.xml', 3600, function () {
            $base = rtrim((string) config('app.url'), '/');
            $urls = [
                ['loc' => $base.'/', 'changefreq' => 'daily', 'priority' => '1.0', 'lastmod' => now()],
                ['loc' => $base.'/properties', 'changefreq' => 'hourly', 'priority' => '0.9', 'lastmod' => now()],
                ['loc' => $base.'/projects', 'changefreq' => 'daily', 'priority' => '0.8', 'lastmod' => now()],
            ];

            foreach (Property::query()->published()->orderBy('id')->get() as $property) {
                $urls[] = [
                    'loc' => $base.'/properties/'.$property->slug,
                    'lastmod' => $property->updated_at,
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }

            foreach (Project::query()->published()->orderBy('id')->get() as $project) {
                $urls[] = [
                    'loc' => $base.'/projects/'.$project->slug,
                    'lastmod' => $project->updated_at,
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }

            foreach (CmsPage::query()->published()->orderBy('id')->get() as $page) {
                $urls[] = [
                    'loc' => $base.'/'.$page->slug,
                    'lastmod' => $page->updated_at,
                    'changefreq' => 'monthly',
                    'priority' => '0.5',
                ];
            }

            $body = collect($urls)->map(function (array $url): string {
                $lastmod = $url['lastmod'] instanceof Carbon
                    ? $url['lastmod']->toAtomString()
                    : Carbon::parse($url['lastmod'])->toAtomString();

                return '<url><loc>'.e($url['loc']).'</loc><lastmod>'.$lastmod.'</lastmod><changefreq>'.$url['changefreq'].'</changefreq><priority>'.$url['priority'].'</priority></url>';
            })->implode('');

            return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$body.'</urlset>';
        });
    }
}
