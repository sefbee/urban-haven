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
        return $next($request);
    }
}
