<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureMfaIsSatisfied;
use App\Services\Auth\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class MfaController extends Controller
{
    private const PENDING_SECRET = 'mfa_pending_secret';

    public function setup(Request $request, MfaService $mfa): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasMfaEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        $secret = $request->session()->get(self::PENDING_SECRET) ?? $mfa->generateSecret();
        $request->session()->put(self::PENDING_SECRET, $secret);

        return view('admin.auth.mfa-setup', [
            'secret' => $secret,
            'qrSvg' => $mfa->qrCodeSvg($user, $secret),
        ]);
    }

    public function confirm(Request $request, MfaService $mfa): View|RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'string', 'max:10']]);
        $secret = $request->session()->get(self::PENDING_SECRET);

        if (! $secret || ! $mfa->verifyCode($secret, $validated['code'])) {
            return back()->withErrors(['code' => 'That code is not valid. Check the time on your phone and try again.']);
        }

        $codes = $mfa->enable($request->user(), $secret);
        $request->session()->forget(self::PENDING_SECRET);
        $request->session()->put(EnsureMfaIsSatisfied::SESSION_KEY, now()->timestamp);

        return view('admin.auth.mfa-recovery', ['codes' => $codes]);
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        if (! $request->user()->hasMfaEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.mfa-challenge');
    }

    public function verify(Request $request, MfaService $mfa): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'string', 'max:20']]);
        $key = 'mfa:'.$request->user()->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['code' => 'Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.']);
        }

        if (! $mfa->attempt($request->user(), $validated['code'])) {
            RateLimiter::hit($key, 300);

            return back()->withErrors(['code' => 'That code is not valid.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put(EnsureMfaIsSatisfied::SESSION_KEY, now()->timestamp);

        return redirect()->intended(route('admin.dashboard'));
    }
}
