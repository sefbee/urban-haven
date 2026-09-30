@php
    $shortlistCount = count(session('shortlist', []));
    $contactPhone = \App\Models\Setting::get('phone');
    $contactEmail = \App\Models\Setting::get('email');
    $analyticsScript = \App\Models\Setting::get('analytics_script');
    $whatsappNumber = preg_replace('/\D+/', '', (string) config('urbanhaven.whatsapp.number'));
    $navLinks = [
        ['label' => __('Buy & rent'), 'url' => route('properties.index'), 'active' => request()->routeIs('properties.*')],
        ['label' => __('Projects'), 'url' => route('projects.index'), 'active' => request()->routeIs('projects.*')],
        ['label' => __('About'), 'url' => route('cms.show', 'about'), 'active' => request()->is('about')],
        ['label' => __('Contact'), 'url' => route('cms.show', 'contact'), 'active' => request()->is('contact')],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#12261c">
        <title>{{ ($seo['title'] ?? config('app.name')) === config('app.name') ? config('app.name') : ($seo['title'].' — '.config('app.name')) }}</title>
        @include('partials.seo-meta')
        @if(filled($analyticsScript))
            {!! $analyticsScript !!}
        @endif
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="min-h-screen bg-cream text-ink antialiased">
        <a class="uh-skip" href="#main">{{ __('Skip to content') }}</a>

        <header class="sticky top-0 z-50 border-b border-gold/25 bg-ink text-cream"
                x-data="{ open: false }" @keydown.escape="open = false">
            <div class="uh-container flex h-16 items-center justify-between gap-6 lg:h-18">
                <a href="{{ route('home') }}" class="flex shrink-0 flex-col leading-none">
                    <span class="text-base font-semibold uppercase tracking-[0.2em] text-cream">Urban Haven</span>
                    <span class="mt-1 hidden text-[0.625rem] uppercase tracking-[0.16em] text-gold sm:block">{{ __('Properties Ltd.') }}</span>
                </a>

                <nav class="hidden items-center gap-1 lg:flex" aria-label="{{ __('Main navigation') }}">
                    @foreach($navLinks as $link)
                        <a href="{{ $link['url'] }}"
                           @if($link['active']) aria-current="page" @endif
                           @class([
                               'rounded-lg px-3 py-2 text-sm font-medium transition',
                               'text-gold' => $link['active'],
                               'text-cream/85 hover:bg-white/10 hover:text-cream' => ! $link['active'],
                           ])>{{ $link['label'] }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-1 sm:gap-2">
                    <form method="POST" action="{{ route('locale.switch') }}" class="hidden sm:block">
                        @csrf
                        <input type="hidden" name="locale" value="{{ app()->getLocale() === 'en' ? 'bn' : 'en' }}">
                        <button type="submit"
                                class="inline-flex min-h-9 items-center gap-1.5 rounded-lg px-2.5 text-xs font-semibold text-cream/85 transition hover:bg-white/10 hover:text-cream"
                                lang="{{ app()->getLocale() === 'en' ? 'bn' : 'en' }}">
                            <x-icon name="globe" class="size-4" />
                            {{ app()->getLocale() === 'en' ? 'বাংলা' : 'English' }}
                        </button>
                    </form>

                    <a href="{{ route('compare') }}"
                       class="relative inline-flex min-h-9 items-center gap-1.5 rounded-lg px-2.5 text-xs font-semibold text-cream/85 transition hover:bg-white/10 hover:text-cream"
                       aria-label="{{ __('Shortlist') }} ({{ $shortlistCount }})">
                        <x-icon name="heart" class="size-4" />
                        <span class="hidden sm:inline">{{ __('Shortlist') }}</span>
                        @if($shortlistCount)
                            <span class="inline-flex size-5 items-center justify-center rounded-full bg-gold text-[0.6875rem] font-bold text-ink">{{ $shortlistCount }}</span>
                        @endif
                    </a>

                    @if(filled($whatsappNumber))
                        <a class="uh-btn-gold uh-btn-sm hidden lg:inline-flex" href="https://wa.me/{{ $whatsappNumber }}" rel="noopener">
                            <x-icon name="whatsapp" class="size-4" />
                            {{ __('Talk to an advisor') }}
                        </a>
                    @endif

                    <button type="button" class="uh-icon-btn text-cream hover:bg-white/10 lg:hidden"
                            @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-nav">
                        <span class="sr-only">{{ __('Menu') }}</span>
                        <x-icon name="menu" class="size-5" x-show="!open" />
                        <x-icon name="close" class="size-5" x-show="open" x-cloak />
                    </button>
                </div>
            </div>

            <div id="mobile-nav" x-show="open" x-cloak x-transition.opacity.duration.150ms
                 class="border-t border-white/10 bg-ink lg:hidden">
                <nav class="uh-container flex flex-col py-3" aria-label="{{ __('Main navigation') }}">
                    @foreach($navLinks as $link)
                        <a href="{{ $link['url'] }}"
                           class="flex min-h-12 items-center rounded-lg px-3 text-[0.9375rem] font-medium text-cream/90 transition hover:bg-white/10"
                           @if($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                    @endforeach
                    <a href="{{ route('compare') }}" class="flex min-h-12 items-center justify-between rounded-lg px-3 text-[0.9375rem] font-medium text-cream/90 transition hover:bg-white/10">
                        {{ __('Shortlist') }}
                        @if($shortlistCount)
                            <span class="inline-flex size-5 items-center justify-center rounded-full bg-gold text-[0.6875rem] font-bold text-ink">{{ $shortlistCount }}</span>
                        @endif
                    </a>

                    <div class="mt-3 flex items-center gap-2 border-t border-white/10 pt-3">
                        <form method="POST" action="{{ route('locale.switch') }}" class="shrink-0">
                            @csrf
                            <input type="hidden" name="locale" value="{{ app()->getLocale() === 'en' ? 'bn' : 'en' }}">
                            <button type="submit" class="uh-btn-ondark uh-btn-sm" lang="{{ app()->getLocale() === 'en' ? 'bn' : 'en' }}">
                                <x-icon name="globe" class="size-4" />
                                {{ app()->getLocale() === 'en' ? 'বাংলা' : 'English' }}
                            </button>
                        </form>
                        @if(filled($whatsappNumber))
                            <a class="uh-btn-gold uh-btn-sm flex-1" href="https://wa.me/{{ $whatsappNumber }}" rel="noopener">
                                <x-icon name="whatsapp" class="size-4" />
                                {{ __('WhatsApp') }}
                            </a>
                        @endif
                    </div>
                </nav>
            </div>
        </header>

        <main id="main">
            @if(session('status') || $errors->any())
                <div class="uh-container pt-6">
                    <x-ui.flash />
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="mt-20 border-t border-line bg-sand">
            <div class="uh-container grid gap-10 py-14 md:grid-cols-12">
                <div class="md:col-span-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-ink">Urban Haven</p>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-[var(--color-muted)]">
                        {{ __('Company-owned homes in Dhaka. Every listing on this site is published by our own team, not a marketplace of unknown sellers.') }}
                    </p>
                    @if(filled($whatsappNumber))
                        <a class="uh-btn-outline uh-btn-sm mt-6" href="https://wa.me/{{ $whatsappNumber }}" rel="noopener">
                            <x-icon name="whatsapp" class="size-4" />
                            {{ __('Talk to an advisor') }}
                        </a>
                    @endif
                </div>

                <nav class="md:col-span-3" aria-label="{{ __('Explore') }}">
                    <p class="uh-eyebrow">{{ __('Explore') }}</p>
                    <ul class="mt-4 space-y-1 text-sm">
                        <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('properties.index', ['listing_type' => 'sale']) }}">{{ __('Homes for sale') }}</a></li>
                        <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('properties.index', ['listing_type' => 'rent']) }}">{{ __('Homes for rent') }}</a></li>
                        <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('projects.index') }}">{{ __('Projects') }}</a></li>
                        <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('compare') }}">{{ __('Compare') }}</a></li>
                    </ul>
                </nav>

                <div class="md:col-span-4">
                    <p class="uh-eyebrow">{{ __('Contact') }}</p>
                    <ul class="mt-4 space-y-1 text-sm">
                        @if(filled($contactPhone))
                            <li>
                                <a class="uh-link-quiet inline-flex min-h-9 items-center gap-2" href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}">
                                    <x-icon name="phone" class="size-4 text-[var(--color-gold-ink)]" />
                                    <span dir="ltr" class="uh-numeric">{{ $contactPhone }}</span>
                                </a>
                            </li>
                        @endif
                        @if(filled($contactEmail))
                            <li>
                                <a class="uh-link-quiet inline-flex min-h-9 items-center gap-2 break-all" href="mailto:{{ $contactEmail }}">
                                    <x-icon name="mail" class="size-4 text-[var(--color-gold-ink)]" />
                                    {{ $contactEmail }}
                                </a>
                            </li>
                        @endif
                        <li><a class="uh-link-quiet inline-flex min-h-9 items-center gap-2" href="{{ route('cms.show', 'contact') }}">{{ __('Contact page') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-line">
                <div class="uh-container flex flex-wrap items-center justify-between gap-3 py-5 text-xs text-[var(--color-muted)]">
                    <p>© {{ date('Y') }} Urban Haven Properties Ltd.</p>
                    <a class="transition hover:text-forest" href="{{ route('admin.login') }}">{{ __('Staff sign in') }}</a>
                </div>
            </div>
        </footer>
    </body>
</html>
