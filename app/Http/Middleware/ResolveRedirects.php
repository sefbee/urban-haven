<?php

namespace App\Http\Middleware;

use App\Services\Seo\RedirectResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveRedirects
{
    public function __construct(private readonly RedirectResolver $resolver) {}

    /**
     * Redirects are only consulted for requests that would otherwise 404, so live pages never pay for the lookup.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 404 || ! in_array($request->method(), ['GET', 'HEAD'], true) || $request->is('admin', 'admin/*')) {
            return $response;
        }

        $redirect = $this->resolver->resolve($request->getPathInfo());

        if ($redirect === null) {
            return $response;
        }

        $target = $redirect['to'];
        $query = $request->getQueryString();

        return redirect($target.($query && ! str_contains($target, '?') ? '?'.$query : ''), $redirect['code']);
    }
}
