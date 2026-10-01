@php
    $shortlistCount = count(session('shortlist', []));
    $contactPhone = \App\Models\Setting::get('phone');
    $contactEmail = \App\Models\Setting::get('email');
    $analyticsScript = \App\Models\Setting::get('analytics_script');
    $whatsappNumber = preg_replace('/\D+/', '', (string) config('urbanhaven.whatsapp.number'));
    $locale = app()->getLocale();
    $navLinks = [
        ['label' => __('Buy'), 'url' => route('properties.index', ['listing_type' => 'sale']), 'active' => request()->routeIs('properties.index') && request('listing_type') === 'sale'],
        ['label' => __('Rent'), 'url' => route('properties.index', ['listing_type' => 'rent']), 'active' => request()->routeIs('properties.index') && request('listing_type') === 'rent'],
        ['label' => __('Home Loan'), 'url' => route('tools').'#emi', 'active' => request()->routeIs('tools')],
        ['label' => __('About Us'), 'url' => route('cms.show', 'about'), 'active' => request()->routeIs('cms.show') && request()->route('slug') === 'about'],
        ['label' => __('Blog'), 'url' => route('cms.show', 'blog'), 'active' => request()->routeIs('cms.show') && request()->route('slug') === 'blog'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#111827">
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

        <header class="sticky top-0 z-50 bg-hero text-white" x-data="{ open: false, account: false, lang: false }" @keydown.escape.window="open = false; account = false; lang = false">
            <div class="uh-container flex h-16 items-center justify-between gap-4 lg:h-[4.25rem]">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-emerald text-white">
                        <x-icon name="home" class="size-5" />
                    </span>
                    <span class="leading-none">
                        <span class="block text-sm font-bold tracking-wide text-white">Urban Haven</span>
                        <span class="mt-0.5 hidden text-[0.625rem] uppercase tracking-[0.16em] text-white/50 sm:block">{{ __('Properties Ltd.') }}</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-0.5 lg:flex" aria-label="{{ __('Main navigation') }}">
                    @foreach($navLinks as $link)
                        <a href="{{ $link['url'] }}"
                           @if($link['active']) aria-current="page" @endif
                           @class(['uh-nav-link-dark', 'uh-nav-link-dark-active' => $link['active']])>{{ $link['label'] }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-1 sm:gap-2">
                    <a class="hidden items-center gap-1.5 rounded-lg border border-white/20 px-3 py-2 text-xs font-semibold text-white transition hover:border-emerald-bright hover:bg-white/5 sm:inline-flex"
                       href="{{ route('tools') }}#valuation">
                        <x-icon name="calculator" class="size-3.5" />
                        {{ __('Valuation Tool') }}
                    </a>

                    <div class="relative hidden sm:block" @click.outside="lang = false">
                        <button type="button"
                                class="inline-flex min-h-9 items-center gap-1 rounded-lg border border-white/20 px-2.5 text-xs font-semibold text-white transition hover:bg-white/10"
                                @click="lang = !lang" :aria-expanded="lang.toString()" aria-haspopup="listbox"
                                aria-controls="locale-menu">
                            {{ $locale === 'bn' ? 'বাংলা' : 'ENG' }}
                            <x-icon name="chevron-down" class="size-3.5" />
                        </button>
                        <div id="locale-menu" x-cloak x-bind:class="lang ? 'block' : 'hidden'"
                             class="absolute right-0 z-20 mt-2 w-36 overflow-hidden rounded-lg bg-hero-muted p-1 ring-1 ring-white/15"
                             role="listbox" aria-label="{{ __('Language') }}">
                            <form method="POST" action="{{ route('locale.switch') }}">
                                @csrf
                                <input type="hidden" name="locale" value="en">
                                <button type="submit" lang="en" role="option"
                                        @class(['flex min-h-9 w-full items-center rounded-md px-3 text-left text-xs font-semibold text-white/80 hover:bg-white/10', 'bg-white/10 text-white' => $locale === 'en'])>
                                    ENG
                                </button>
                            </form>
                            <form method="POST" action="{{ route('locale.switch') }}">
                                @csrf
                                <input type="hidden" name="locale" value="bn">
                                <button type="submit" lang="bn" role="option"
                                        @class(['flex min-h-9 w-full items-center rounded-md px-3 text-left text-xs font-semibold text-white/80 hover:bg-white/10', 'bg-white/10 text-white' => $locale === 'bn'])>
                                    বাংলা
                                </button>
                            </form>
                        </div>
                    </div>

                    <a href="{{ route('compare') }}"
                       class="relative inline-flex size-10 items-center justify-center rounded-lg text-white/80 transition hover:bg-white/10"
                       aria-label="{{ __('Shortlist') }} ({{ $shortlistCount }})">
                        <x-icon name="heart" class="size-4" />
                        @if($shortlistCount)
                            <span class="absolute -right-0.5 -top-0.5 inline-flex size-4 items-center justify-center rounded-full bg-emerald text-[0.625rem] font-bold text-white">{{ $shortlistCount }}</span>
                        @endif
                    </a>

                    <button type="button" class="hidden items-center gap-1.5 rounded-lg border border-white/20 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/10 lg:inline-flex"
                            @click="account = true" :aria-expanded="account.toString()" aria-haspopup="dialog"
                            aria-controls="account-dialog">
                        <x-icon name="user" class="size-4" />
                        {{ __('Sign In / Sign Up') }}
                    </button>

                    <button type="button" class="inline-flex size-10 items-center justify-center rounded-lg text-white hover:bg-white/10 lg:hidden"
                            @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-nav">
                        <span class="sr-only">{{ __('Menu') }}</span>
                        <x-icon name="menu" class="size-5" x-show="!open" />
                        <x-icon name="close" class="size-5" x-show="open" x-cloak />
                    </button>
                </div>
            </div>

            <div id="mobile-nav" x-cloak x-bind:class="open ? 'block' : 'hidden'"
                 class="border-t border-white/10 bg-hero lg:hidden">
                <nav class="uh-container flex flex-col py-3" aria-label="{{ __('Main navigation') }}">
                    @foreach($navLinks as $link)
                        <a href="{{ $link['url'] }}"
                           class="flex min-h-12 items-center rounded-lg px-3 text-[0.9375rem] font-medium text-white/85 transition hover:bg-white/10"
                           @if($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                    @endforeach
                    <a href="{{ route('compare') }}" class="flex min-h-12 items-center justify-between rounded-lg px-3 text-[0.9375rem] font-medium text-white/85 transition hover:bg-white/10">
                        {{ __('Shortlist') }}
                        @if($shortlistCount)
                            <span class="inline-flex size-5 items-center justify-center rounded-full bg-emerald text-[0.6875rem] font-bold text-white">{{ $shortlistCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('tools') }}#valuation" class="flex min-h-12 items-center rounded-lg px-3 text-[0.9375rem] font-medium text-white/85 transition hover:bg-white/10">
                        {{ __('Valuation Tool') }}
                    </a>
                    <div class="flex items-center gap-2 px-3 py-2">
                        <form method="POST" action="{{ route('locale.switch') }}">
                            @csrf
                            <input type="hidden" name="locale" value="en">
                            <button type="submit" lang="en" @class(['uh-topbar-link', 'bg-white/10 text-white' => $locale === 'en'])>ENG</button>
                        </form>
                        <form method="POST" action="{{ route('locale.switch') }}">
                            @csrf
                            <input type="hidden" name="locale" value="bn">
                            <button type="submit" lang="bn" @class(['uh-topbar-link', 'bg-white/10 text-white' => $locale === 'bn'])>বাংলা</button>
                        </form>
                    </div>
                    <button type="button" class="flex min-h-12 items-center rounded-lg px-3 text-left text-[0.9375rem] font-medium text-white/85 transition hover:bg-white/10"
                            @click="account = true; open = false">
                        {{ __('Sign In / Sign Up') }}
                    </button>
                </nav>
            </div>

            <div id="account-dialog" x-cloak x-bind:class="account ? 'flex' : 'hidden'"
                 class="fixed inset-0 z-[80] items-center justify-center bg-ink/50 p-4"
                 role="dialog" aria-modal="true" aria-labelledby="account-title"
                 @click.self="account = false">
                <div class="uh-panel w-full max-w-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 id="account-title" class="uh-h3">{{ __('Sign In / Sign Up') }}</h2>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">{{ __('Buyers and renters register interest with the Urban Haven desk. Staff use the company login.') }}</p>
                        </div>
                        <button type="button" class="uh-icon-btn" @click="account = false" aria-label="{{ __('Close') }}">
                            <x-icon name="close" class="size-5" />
                        </button>
                    </div>
                    <div class="mt-5 grid gap-2">
                        <a class="uh-btn-primary uh-btn-block" href="{{ route('admin.login') }}">{{ __('Staff sign in') }}</a>
                        <a class="uh-btn-outline uh-btn-block" href="{{ route('cms.show', 'contact') }}">{{ __('Register interest') }}</a>
                    </div>
                </div>
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

        <footer class="mt-20 bg-hero text-white">
            <div class="uh-container grid gap-10 py-14 md:grid-cols-12">
                <div class="md:col-span-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-white">Urban Haven</p>
                    <p class="mt-3 text-sm text-white/70">{{ __('Dhaka, Bangladesh') }}</p>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/60">
                        {{ __('Company-owned homes in Dhaka. Every listing on this site is published by our own team, not a marketplace of unknown sellers.') }}
                    </p>
                    @if(filled($whatsappNumber))
                        <a class="mt-6 inline-flex min-h-10 items-center gap-2 rounded-lg border border-white/20 px-4 text-sm font-semibold text-white transition hover:bg-white/10"
                           href="https://wa.me/{{ $whatsappNumber }}" rel="noopener">
                            <x-icon name="whatsapp" class="size-4" />
                            {{ __('WhatsApp Us') }}
                        </a>
                    @endif
                </div>

                <nav class="md:col-span-2" aria-label="{{ __('Popular searches') }}">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">{{ __('Popular searches') }}</p>
                    <ul class="mt-4 space-y-1 text-sm">
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('properties.index', ['listing_type' => 'sale']) }}">{{ __('Homes for sale') }}</a></li>
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('properties.index', ['listing_type' => 'rent']) }}">{{ __('Homes for rent') }}</a></li>
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('projects.index') }}">{{ __('Invest') }}</a></li>
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('tools') }}#valuation">{{ __('Valuation Tool') }}</a></li>
                    </ul>
                </nav>

                <nav class="md:col-span-3" aria-label="{{ __('Explore') }}">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">{{ __('Market links') }}</p>
                    <ul class="mt-4 space-y-1 text-sm">
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('properties.index', ['listing_type' => 'sale']) }}">{{ __('Buy') }}</a></li>
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('properties.index', ['listing_type' => 'rent']) }}">{{ __('Rent') }}</a></li>
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('legal') }}">{{ __('Legal Services') }}</a></li>
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('tools') }}#emi">{{ __('Home Loan') }}</a></li>
                    </ul>
                </nav>

                <nav class="md:col-span-3" aria-label="{{ __('Quick links') }}">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">{{ __('Quick links') }}</p>
                    <ul class="mt-4 space-y-1 text-sm">
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('cms.show', 'about') }}">{{ __('About Us') }}</a></li>
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('cms.show', 'blog') }}">{{ __('Blog') }}</a></li>
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('tools') }}">{{ __('Tools') }}</a></li>
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('compare') }}">{{ __('Compare') }}</a></li>
                        @if(filled($contactPhone))
                            <li>
                                <a class="inline-flex min-h-9 items-center gap-2 text-white/75 transition hover:text-white" href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}">
                                    <span dir="ltr" class="uh-numeric">{{ $contactPhone }}</span>
                                </a>
                            </li>
                        @endif
                        @if(filled($contactEmail))
                            <li>
                                <a class="inline-flex min-h-9 items-center gap-2 break-all text-white/75 transition hover:text-white" href="mailto:{{ $contactEmail }}">
                                    {{ $contactEmail }}
                                </a>
                            </li>
                        @endif
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('cms.show', 'contact') }}">{{ __('Contact page') }}</a></li>
                    </ul>
                </nav>
            </div>

            <div class="border-t border-white/10">
                <div class="uh-container flex flex-wrap items-center justify-between gap-3 py-5 text-xs text-white/45">
                    <p>© {{ date('Y') }} Urban Haven Properties Ltd.</p>
                    <a class="transition hover:text-white" href="{{ route('admin.login') }}">{{ __('Staff sign in') }}</a>
                </div>
            </div>
        </footer>
    </body>
</html>
