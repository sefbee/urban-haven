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
    <body class="uh-auth min-h-screen antialiased">
        <main class="uh-auth-shell">
            <section class="uh-auth-panel" aria-label="Staff sign in">
                <a class="uh-auth-brand" href="{{ url('/') }}" aria-label="Urban Haven home">
                    <span class="uh-auth-brand-mark" aria-hidden="true"><span></span><span></span><span></span></span>
                    <span class="uh-auth-brand-copy">
                        <strong>URBAN HAVEN</strong>
                        <small>PROPERTIES &amp; DEVELOPMENT</small>
                    </span>
                </a>

                <div class="uh-auth-content">
                    <div class="uh-auth-card">
                        @yield('card')
                    </div>
                </div>

                <footer class="uh-auth-footer">
                    <span>© {{ date('Y') }} Urban Haven. All rights reserved.</span>
                    <a href="{{ url('/#contact') }}">Contact</a>
                </footer>
            </section>
            <aside class="uh-auth-visual" aria-hidden="true">
                <div class="uh-auth-visual-building"></div>
            </aside>
        </main>
    </body>
</html>
