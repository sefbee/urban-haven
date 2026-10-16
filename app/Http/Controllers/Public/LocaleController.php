<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $request->validate(['locale' => ['required', 'in:en']]);
        $request->session()->put('locale', 'en');

        return back();
    }
}
