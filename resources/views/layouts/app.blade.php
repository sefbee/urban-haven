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
    <body class="uh-home flex min-h-screen flex-col antialiased">
        <header class="uh-site-bar">
            <div class="uh-container uh-bar-row">
                <a href="{{ url('/') }}" class="uh-brand">
                    <span class="uh-wordmark">Urban Haven</span>
                    <span class="sr-only">— {{ __('home page') }}</span>
                </a>
            </div>
        </header>

        @yield('content')

        <footer class="uh-site-foot mt-auto">
            <div class="uh-container uh-foot-legal">
                &copy; {{ now()->year }} Urban Haven Properties Ltd.
            </div>
        </footer>
    </body>
</html>
