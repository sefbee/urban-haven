<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MfaController extends Controller
{
    public function setup(Request $request): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }

    public function confirm(Request $request): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }

    public function challenge(Request $request): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }

    public function verify(Request $request): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }
}
