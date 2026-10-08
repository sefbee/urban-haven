<?php

use App\Http\Middleware\ApplySiteSettings;
use App\Http\Middleware\CaptureFirstTouchAttribution;
use App\Http\Middleware\EnsureMfaIsSatisfied;
use App\Http\Middleware\EnsureStaffIsActive;
use App\Http\Middleware\ResolveRedirects;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Support\JsonError;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $middleware->web(append: [
            ApplySiteSettings::class,
            SetLocale::class,
            CaptureFirstTouchAttribution::class,
            ResolveRedirects::class,
        ]);

        $middleware->alias([
            'staff.active' => EnsureStaffIsActive::class,
            'staff.mfa' => EnsureMfaIsSatisfied::class,
        ]);

        $middleware->encryptCookies(except: ['uh_consent']);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->dontFlash(['password', 'password_confirmation', 'current_password', 'code']);

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return JsonError::fromThrowable($e);
            }
        });
    })->create();
