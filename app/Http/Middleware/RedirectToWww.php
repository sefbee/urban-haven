<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToWww
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (str_starts_with($request->getHost(), 'www.')) {
            $host = preg_replace('/^www\./', '', $request->getHost());
            return redirect()->to(
                $request->getScheme() . '://' . $host . $request->getRequestUri(),
                301
            );
        }

        return $next($request);
    }
}
