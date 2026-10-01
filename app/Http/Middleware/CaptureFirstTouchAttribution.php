<?php

namespace App\Http\Middleware;

use App\Support\LeadAttribution;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureFirstTouchAttribution
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->is('admin', 'admin/*', 'up', 'build/*', 'storage/*') && ! $request->expectsJson()) {
            LeadAttribution::remember($request);
        }

        return $next($request);
    }
}
