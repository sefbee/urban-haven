<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaIsSatisfied
{
    public const SESSION_KEY = 'mfa_passed_at';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $request->routeIs('admin.mfa.*', 'admin.logout')) {
            return $next($request);
        }

        if ($user->hasMfaEnabled()) {
            if (! $request->session()->has(self::SESSION_KEY)) {
                return redirect()->guest(route('admin.mfa.challenge'));
            }

            return $next($request);
        }

        if ($user->requiresMfa()) {
            return redirect()->route('admin.mfa.setup')
                ->with('status', 'Set up two-factor authentication to continue. It is required for administrator accounts.');
        }

        return $next($request);
    }
}
