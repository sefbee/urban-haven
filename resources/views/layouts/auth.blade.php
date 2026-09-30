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
    <body class="flex min-h-screen flex-col bg-ink text-cream antialiased">
        <div class="flex flex-1 items-center justify-center px-4 py-10">
            <div class="w-full max-w-md">
                <div class="text-center">
                    <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.22em] text-gold-soft">Urban Haven</p>
                    <p class="mt-1 text-xs text-cream/50">Properties Ltd. · Staff access</p>
                </div>

                <div class="mt-6 rounded-2xl bg-paper p-7 text-ink shadow-xl sm:p-8">
                    @yield('card')
                </div>

                <p class="mt-6 text-center text-xs text-cream/50">
                    <a class="transition hover:text-cream" href="{{ url('/') }}">Return to the public site</a>
                </p>
            </div>
        </div>
    </body>
</html>
