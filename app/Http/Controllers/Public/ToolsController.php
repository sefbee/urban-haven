<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LocationArea;
use App\Models\Setting;
use App\Support\SeoMeta;
use Illuminate\View\View;

class ToolsController extends Controller
{
    public function index(): View
    {
        return view('public.tools.index', [
            'areas' => LocationArea::query()->active()->orderBy('name')->get(),
            'contactPhone' => Setting::get('phone'),
            'contactEmail' => Setting::get('email'),
            'seo' => SeoMeta::for(null, __('Tools'), __('Request a valuation or estimate a home loan EMI in Bangladeshi Taka.')),
        ]);
    }

    public function legal(): View
    {
        return view('public.tools.legal', [
            'contactPhone' => Setting::get('phone'),
            'contactEmail' => Setting::get('email'),
            'seo' => SeoMeta::for(null, __('Legal Services'), __('Transaction support for Urban Haven sales, lettings and handover.')),
        ]);
    }
}
