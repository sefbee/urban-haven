<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex">
        <title>@yield('title', config('app.name'))</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="flex min-h-screen flex-col bg-paper text-ink antialiased">
        <header class="border-b border-line">
            <div class="uh-container flex h-16 items-center">
                <a href="{{ url('/') }}" class="font-display text-xl tracking-tight text-forest">
                    Urban Haven
                    <span class="sr-only">— {{ __('home page') }}</span>
                </a>
            </div>
        </header>

        @yield('content')

        <footer class="mt-auto border-t border-line">
            <div class="uh-container flex h-16 items-center text-xs text-[var(--color-muted)]">
                &copy; {{ now()->year }} Urban Haven Properties Ltd.
            </div>
        </footer>
    </body>
</html>
