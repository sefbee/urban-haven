<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Support\SeoMeta;
use App\Support\StructuredData;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::cachedVisible();

        return view('public.faq', [
            'groups' => $faqs->groupBy('group'),
            'seo' => SeoMeta::for(null, 'Frequently asked questions', 'Answers to common questions about buying, renting and visiting Urban Haven properties.', [
                'json_ld' => array_filter([StructuredData::faq($faqs)]),
            ]),
        ]);
    }
}
