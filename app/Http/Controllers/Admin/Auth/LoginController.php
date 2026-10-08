<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Services\Auth\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request, StaffService $staff): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'login:'.Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors(['email' => 'Too many sign-in attempts. Try again in '.RateLimiter::availableIn($throttleKey).' seconds.'])->onlyInput('email');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 300);
            $staff->recordFailedLogin($credentials['email'], $request->ip());

            return back()->withErrors(['email' => 'Those credentials do not match our records.'])->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);
        $user = $request->user();

        if ($user->is_active === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'This staff account is no longer active.']);
        }

        if ($user->roles()->doesntExist()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'Those credentials do not match our records.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $staff->markLogin($user);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            $auditLogger->record($user->id, 'auth.logout', $user::class, $user->id, null, null, $request->ip());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
