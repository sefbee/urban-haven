<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Support\SeoMeta;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function show(string $slug): View
    {
        $page = CmsPage::query()->published()->with('seoOverride')->where('slug', $slug)->firstOrFail();

        return view('public.cms.show', [
            'page' => $page,
            'seo' => SeoMeta::for($page, $page->title, $page->meta_description),
        ]);
    }
}
