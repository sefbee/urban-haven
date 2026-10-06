<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Seo\SitemapGenerator;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public const SECTIONS = ['pages', 'properties', 'locations', 'articles'];

    public function index(SitemapGenerator $sitemap): Response
    {
        return response($sitemap->index(), 200, ['Content-Type' => 'application/xml']);
    }

    public function section(string $section, SitemapGenerator $sitemap): Response
    {
        return response($sitemap->section($section), 200, ['Content-Type' => 'application/xml']);
    }
}
