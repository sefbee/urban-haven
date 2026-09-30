<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $locale = $request->validate(['locale' => ['required', 'in:en,bn']])['locale'];
        $request->session()->put('locale', $locale);

        return back();
    }
}
