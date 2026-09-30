<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Seo\SitemapGenerator;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SitemapGenerator $sitemap): Response
    {
        return response($sitemap->xml(), 200, ['Content-Type' => 'application/xml']);
    }
}
