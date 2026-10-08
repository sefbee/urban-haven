<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Noindex pages stay crawlable so search engines can read their noindex directive.
     */
    public function __invoke(): Response
    {
        $lines = app()->isProduction() && Setting::get('seo_allow_indexing', true)
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /thank-you', 'Disallow: /saved/', 'Disallow: /track', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
