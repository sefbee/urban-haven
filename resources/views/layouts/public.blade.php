@php
    $contactPhone = \App\Models\Setting::get('phone');
    $contactEmail = \App\Models\Setting::get('email');
    $contactAddress = \App\Models\Setting::get('address');
    $companyName = \App\Models\Setting::get('company_name', config('app.name'));
    $whatsappHref = \App\Support\PhoneNumber::whatsappHref(\App\Models\Setting::get('whatsapp'));
    $socialLinks = array_values(array_filter((array) \App\Models\Setting::get('social_links', [])));
    $purposes = \App\Models\Setting::enabledPurposes();
    $locale = app()->getLocale();
    $analytics = [
        'gtm' => config('urbanhaven.analytics.gtm_id'),
        'ga4' => config('urbanhaven.analytics.ga4_id'),
        'pixel' => config('urbanhaven.analytics.meta_pixel_id'),
    ];
    $hasAnalytics = filled(array_filter($analytics));
    $consentCookie = config('urbanhaven.analytics.consent_cookie');
    $consent = request()->cookie($consentCookie);
    $privacyUrl = \App\Models\CmsPage::query()->where('slug', 'privacy')->published()->exists() ? route('cms.show', 'privacy') : null;

    $headerMenu = \App\Models\MenuItem::forLocation('header');
    $navLinks = $headerMenu->isNotEmpty()
        ? $headerMenu->map(fn ($item) => ['label' => $item->label, 'url' => $item->url, 'external' => ! $item->isInternal(), 'active' => $item->isInternal() && request()->getRequestUri() === $item->url])->all()
        : array_values(array_filter([
            in_array('sale', $purposes, true) ? ['label' => __('Buy'), 'url' => route('properties.index', ['listing_type' => 'sale']), 'external' => false, 'active' => request()->routeIs('properties.index') && request('listing_type') === 'sale'] : null,
            in_array('rent', $purposes, true) ? ['label' => __('Rent'), 'url' => route('properties.index', ['listing_type' => 'rent']), 'external' => false, 'active' => request()->routeIs('properties.index') && request('listing_type') === 'rent'] : null,
            ['label' => __('Projects'), 'url' => route('projects.index'), 'external' => false, 'active' => request()->routeIs('projects.*')],
            ['label' => __('Articles'), 'url' => route('articles.index'), 'external' => false, 'active' => request()->routeIs('articles.*')],
            ['label' => __('About Us'), 'url' => route('cms.show', 'about'), 'external' => false, 'active' => request()->routeIs('cms.show') && request()->route('slug') === 'about'],
            ['label' => __('Contact'), 'url' => route('cms.show', 'contact'), 'external' => false, 'active' => request()->routeIs('cms.show') && request()->route('slug') === 'contact'],
        ]));

    $footerMenu = \App\Models\MenuItem::forLocation('footer');
    $footerLinks = $footerMenu->isNotEmpty()
        ? $footerMenu->map(fn ($item) => ['label' => $item->label, 'url' => $item->url])->all()
        : array_values(array_filter([
            ['label' => __('About Us'), 'url' => route('cms.show', 'about')],
            ['label' => __('Articles'), 'url' => route('articles.index')],
            ['label' => __('FAQ'), 'url' => route('faq')],
            ['label' => __('Tools'), 'url' => route('tools')],
            ['label' => __('Contact'), 'url' => route('cms.show', 'contact')],
            $privacyUrl ? ['label' => __('Privacy'), 'url' => $privacyUrl] : null,
        ]));
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
        <script>
            window.dataLayer = window.dataLayer || [];
            window.uhAnalytics = @json(['ids' => $analytics, 'cookie' => $consentCookie, 'trackUrl' => route('track')]);
        </script>
        @if($hasAnalytics)
            <script>
                function gtag(){dataLayer.push(arguments);}
                gtag('consent', 'default', {ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'denied', wait_for_update: 500});
            </script>
        @endif
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="min-h-screen bg-cream text-ink antialiased">
        <a class="uh-skip" href="#main">{{ __('Skip to content') }}</a>

        <header class="sticky top-0 z-50 bg-hero text-white" x-data="{ open: false, lang: false }" @keydown.escape.window="open = false; lang = false">
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
                           @if($link['external']) rel="noopener" target="_blank" @endif
                           @if($link['active']) aria-current="page" @endif
                           @class(['uh-nav-link-dark', 'uh-nav-link-dark-active' => $link['active']])>{{ $link['label'] }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-1 sm:gap-2">
                    @if(filled($contactPhone))
                        <a class="hidden items-center gap-1.5 rounded-lg border border-white/20 px-3 py-2 text-xs font-semibold text-white transition hover:border-emerald-bright hover:bg-white/5 sm:inline-flex"
                           href="{{ \App\Support\PhoneNumber::telHref($contactPhone) }}" data-track="phone_click" data-track-location="header">
                            <x-icon name="phone" class="size-3.5" />
                            <span dir="ltr" class="uh-numeric">{{ $contactPhone }}</span>
                        </a>
                    @endif

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
                            @foreach(['en' => 'ENG', 'bn' => 'বাংলা'] as $code => $label)
                                <form method="POST" action="{{ route('locale.switch') }}">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ $code }}">
                                    <button type="submit" lang="{{ $code }}" role="option"
                                            @class(['flex min-h-9 w-full items-center rounded-md px-3 text-left text-xs font-semibold text-white/80 hover:bg-white/10', 'bg-white/10 text-white' => $locale === $code])>
                                        {{ $label }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>

                    <a href="{{ route('shortlist') }}" x-data
                       class="relative inline-flex size-10 items-center justify-center rounded-lg text-white/80 transition hover:bg-white/10"
                       :aria-label="'{{ __('Shortlist') }} (' + $store.saved.shortlist.length + ')'" aria-label="{{ __('Shortlist') }}">
                        <x-icon name="heart" class="size-4" />
                        <span x-cloak x-show="$store.saved.shortlist.length" x-text="$store.saved.shortlist.length"
                              class="absolute -right-0.5 -top-0.5 inline-flex size-4 items-center justify-center rounded-full bg-emerald text-[0.625rem] font-bold text-white"></span>
                    </a>

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
                <nav class="uh-container flex flex-col py-3" aria-label="{{ __('Mobile navigation') }}">
                    @foreach($navLinks as $link)
                        <a href="{{ $link['url'] }}"
                           class="flex min-h-12 items-center rounded-lg px-3 text-[0.9375rem] font-medium text-white/85 transition hover:bg-white/10"
                           @if($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                    @endforeach
                    <a href="{{ route('shortlist') }}" class="flex min-h-12 items-center justify-between rounded-lg px-3 text-[0.9375rem] font-medium text-white/85 transition hover:bg-white/10" x-data>
                        {{ __('Shortlist') }}
                        <span x-cloak x-show="$store.saved.shortlist.length" x-text="$store.saved.shortlist.length"
                              class="inline-flex size-5 items-center justify-center rounded-full bg-emerald text-[0.6875rem] font-bold text-white"></span>
                    </a>
                    @if(filled($contactPhone))
                        <a href="{{ \App\Support\PhoneNumber::telHref($contactPhone) }}" data-track="phone_click" data-track-location="mobile_menu"
                           class="flex min-h-12 items-center gap-2 rounded-lg px-3 text-[0.9375rem] font-medium text-white/85 transition hover:bg-white/10">
                            <x-icon name="phone" class="size-4" />
                            <span dir="ltr">{{ $contactPhone }}</span>
                        </a>
                    @endif
                    <div class="flex items-center gap-2 px-3 py-2">
                        @foreach(['en' => 'ENG', 'bn' => 'বাংলা'] as $code => $label)
                            <form method="POST" action="{{ route('locale.switch') }}">
                                @csrf
                                <input type="hidden" name="locale" value="{{ $code }}">
                                <button type="submit" lang="{{ $code }}" @class(['uh-topbar-link', 'bg-white/10 text-white' => $locale === $code])>{{ $label }}</button>
                            </form>
                        @endforeach
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

        <footer class="mt-20 bg-hero text-white">
            <div class="uh-container grid gap-10 py-14 md:grid-cols-12">
                <div class="md:col-span-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-white">{{ $companyName }}</p>
                    @if(filled($contactAddress))
                        <p class="mt-3 text-sm text-white/70">{{ $contactAddress }}</p>
                    @endif
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/60">
                        {{ __('Every listing on this site is published by our own team, not a marketplace of unknown sellers.') }}
                    </p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        @if($whatsappHref)
                            <a class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-white/20 px-4 text-sm font-semibold text-white transition hover:bg-white/10"
                               href="{{ $whatsappHref }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="footer">
                                <x-icon name="whatsapp" class="size-4" />
                                {{ __('WhatsApp Us') }}
                            </a>
                        @endif
                        @foreach($socialLinks as $social)
                            <a class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-white/20 px-3 text-xs text-white/75 transition hover:bg-white/10"
                               href="{{ $social }}" rel="noopener me" target="_blank">{{ \Illuminate\Support\Str::of(parse_url($social, PHP_URL_HOST))->replace('www.', '') }}</a>
                        @endforeach
                    </div>
                </div>

                <nav class="md:col-span-3" aria-label="{{ __('Explore') }}">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">{{ __('Explore') }}</p>
                    <ul class="mt-4 space-y-1 text-sm">
                        @if(in_array('sale', $purposes, true))
                            <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('properties.index', ['listing_type' => 'sale']) }}">{{ __('Properties for sale') }}</a></li>
                        @endif
                        @if(in_array('rent', $purposes, true))
                            <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('properties.index', ['listing_type' => 'rent']) }}">{{ __('Properties for rent') }}</a></li>
                        @endif
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('projects.index') }}">{{ __('Projects') }}</a></li>
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('shortlist') }}">{{ __('Shortlist') }}</a></li>
                        <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ route('compare') }}">{{ __('Compare') }}</a></li>
                    </ul>
                </nav>

                <nav class="md:col-span-4" aria-label="{{ __('Company') }}">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">{{ __('Company') }}</p>
                    <ul class="mt-4 space-y-1 text-sm">
                        @foreach($footerLinks as $link)
                            <li><a class="inline-flex min-h-9 items-center text-white/75 transition hover:text-white" href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                        @endforeach
                        @if(filled($contactPhone))
                            <li>
                                <a class="inline-flex min-h-9 items-center gap-2 text-white/75 transition hover:text-white" href="{{ \App\Support\PhoneNumber::telHref($contactPhone) }}" data-track="phone_click" data-track-location="footer">
                                    <span dir="ltr" class="uh-numeric">{{ $contactPhone }}</span>
                                </a>
                            </li>
                        @endif
                        @if(filled($contactEmail))
                            <li>
                                <a class="inline-flex min-h-9 items-center gap-2 break-all text-white/75 transition hover:text-white" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>

            <div class="border-t border-white/10">
                <div class="uh-container flex flex-wrap items-center justify-between gap-3 py-5 text-xs text-white/45">
                    <p>© {{ date('Y') }} {{ $companyName }}</p>
                    <div class="flex items-center gap-4">
                        @if($hasAnalytics)
                            <button type="button" class="transition hover:text-white" x-data @click="$dispatch('uh:consent-open')">{{ __('Cookie settings') }}</button>
                        @endif
                        <a class="transition hover:text-white" href="{{ route('admin.login') }}" rel="nofollow">{{ __('Staff sign in') }}</a>
                    </div>
                </div>
            </div>
        </footer>

        @if($hasAnalytics)
            <div x-data="uhConsent(@js($consent))" x-cloak x-show="open" @uh:consent-open.window="open = true"
                 class="fixed inset-x-3 bottom-3 z-[90] mx-auto max-w-xl rounded-xl bg-white p-5 text-sm shadow-2xl ring-1 ring-line sm:inset-x-auto sm:right-4"
                 role="dialog" aria-modal="false" aria-labelledby="consent-title">
                <h2 id="consent-title" class="font-semibold text-ink">{{ __('Analytics cookies') }}</h2>
                <p class="mt-1.5 text-[var(--color-muted)]">
                    {{ __('We would like to measure visits and enquiries to improve this site. Nothing is loaded until you agree.') }}
                    @if($privacyUrl)
                        <a class="font-semibold text-emerald underline" href="{{ $privacyUrl }}">{{ __('Privacy') }}</a>
                    @endif
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" class="uh-btn-primary uh-btn-sm" @click="choose('granted')">{{ __('Allow analytics') }}</button>
                    <button type="button" class="uh-btn-outline uh-btn-sm" @click="choose('denied')">{{ __('No thanks') }}</button>
                </div>
            </div>
        @endif

        @if(!empty($analyticsEvents))
            <script type="application/json" id="uh-page-events">{!! json_encode(array_values($analyticsEvents), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        @endif
    </body>
</html>
