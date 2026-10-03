<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">
        <title>@yield('title', 'Staff access') — Urban Haven</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="uh-auth flex min-h-screen flex-col antialiased">
        <div class="flex flex-1 items-center justify-center px-4 py-10">
            <div class="w-full max-w-md">
                <div class="text-center">
                    <span class="uh-auth-mark" aria-hidden="true">UH</span>
                    <p class="mt-4 text-[0.6875rem] font-medium uppercase tracking-[0.16em] text-[var(--color-gold-ink)]">Urban Haven</p>
                    <p class="mt-1 text-xs text-[var(--color-muted)]">Properties Ltd. · Operations</p>
                </div>

                <div class="uh-auth-card">
                    @yield('card')
                </div>

                <p class="mt-6 text-center text-xs text-[var(--color-muted)]">
                    <a class="transition hover:text-ink" href="{{ url('/') }}">Return to the public site</a>
                </p>
            </div>
        </div>
    </body>
</html>
