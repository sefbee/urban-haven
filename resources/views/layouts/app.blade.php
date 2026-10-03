<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex">
        <meta name="theme-color" content="#ffffff">
        <title>@yield('title', config('app.name'))</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="uh-home flex min-h-screen flex-col antialiased text-[#1d1d1f]">
        <header class="uh-site-bar uh-site-bar-home sticky top-0 z-50">
            <div class="uh-container flex h-12 items-center">
                <a href="{{ url('/') }}" class="text-sm font-bold tracking-wide text-[#1d1d1f]">
                    Urban Haven
                    <span class="sr-only">— {{ __('home page') }}</span>
                </a>
            </div>
        </header>

        @yield('content')

        <footer class="mt-auto bg-hero text-white">
            <div class="uh-container flex h-16 items-center text-xs text-white/45">
                &copy; {{ now()->year }} Urban Haven Properties Ltd.
            </div>
        </footer>
    </body>
</html>
