<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\PageSeo;
use App\Support\TaggedCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageSeoController extends Controller
{
    public function index(): View
    {
        abort_unless(request()->user()->hasPermission('cms.publish'), 403);

        return view('admin.page-seo.index', [
            'pages' => PageSeo::PAGES,
            'values' => PageSeo::all(),
            'siteTitle' => Setting::get('seo_default_title') ?: Setting::get('company_name', config('app.name')),
            'siteDescription' => (string) Setting::get('seo_default_description', ''),
        ]);
    }

    public function update(Request $request, AuditLogger $audit): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('cms.publish'), 403);

        $validated = $request->validate(PageSeo::rules());
        PageSeo::save($validated['pages']);
        TaggedCache::flush(['homepage', 'cms', 'sitemap']);

        $routes = array_keys($validated['pages']);
        $audit->record($request->user()->id, 'seo.pages_updated', null, null, null, ['pages' => $routes], $request->ip());

        $label = count($routes) === 1 ? PageSeo::PAGES[$routes[0]]['label'] : 'Page';

        return redirect()->route('admin.page-seo.index', ['page' => $routes[0] ?? null])->with('status', $label.' SEO saved.');
    }
}
