<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Support\SeoMeta;
use App\Support\StructuredData;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function show(string $slug): View|RedirectResponse
    {
        if ($slug === 'blog') {
            return redirect()->route('articles.index', status: 301);
        }

        $page = CmsPage::query()->published()->with('seoOverride')->where('slug', $slug)->firstOrFail();

        return view('public.cms.show', [
            'page' => $page,
            'seo' => SeoMeta::for($page, $page->meta_title ?: $page->title, $page->meta_description, [
                'json_ld' => [StructuredData::breadcrumbs([[__('Home'), route('home')], [$page->title, route('cms.show', $page->slug)]])],
            ]),
        ]);
    }
}
