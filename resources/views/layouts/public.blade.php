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
    $isHome = request()->routeIs('home');
    $overlayHeader = $isHome || trim($__env->yieldContent('overlay_header')) !== '';

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
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#ffffff">
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
    <body @class(['uh-home min-h-screen antialiased', 'uh-is-home' => $isHome, 'uh-has-overlay' => $overlayHeader])>
        <a class="uh-skip" href="#main">{{ __('Skip to content') }}</a>

        <header @class(['uh-site-bar', 'is-overlay' => $overlayHeader])
                x-data="uhSiteBar(@js($overlayHeader))"
                @uh-menu.window="open = $event.detail"
                @keydown.escape.window="lang = false"
                :class="{ 'is-overlay': !solid }">
            <div class="uh-container uh-bar-row">
                <a href="{{ route('home') }}" class="uh-brand" @if($isHome) aria-current="page" @endif>
                    <span class="uh-wordmark">Urban Haven</span>
                </a>

                <nav class="uh-nav" aria-label="{{ __('Main navigation') }}">
                    @foreach($navLinks as $link)
                        <a href="{{ $link['url'] }}"
                           @if($link['external']) rel="noopener" target="_blank" @endif
                           @if($link['active']) aria-current="page" @endif
                           @class(['uh-nav-link', 'uh-nav-link-active' => $link['active']])>{{ $link['label'] }}</a>
                    @endforeach
                </nav>

                <div class="uh-bar-actions">
                    <div class="relative hidden sm:block" @click.outside="lang = false">
                        <button type="button" class="uh-bar-quiet"
                                @click="lang = !lang" :aria-expanded="lang.toString()" aria-haspopup="listbox"
                                aria-controls="locale-menu">
                            {{ $locale === 'bn' ? 'বাংলা' : 'EN' }}
                        </button>
                        <div id="locale-menu" x-cloak x-bind:class="lang ? 'block' : 'hidden'" class="uh-lang-menu"
                             role="listbox" aria-label="{{ __('Language') }}">
                            @foreach(['en' => 'English', 'bn' => 'বাংলা'] as $code => $label)
                                <form method="POST" action="{{ route('locale.switch') }}">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ $code }}">
                                    <button type="submit" lang="{{ $code }}" role="option" aria-selected="{{ $locale === $code ? 'true' : 'false' }}">
                                        {{ $label }}
                                        @if($locale === $code)
                                            <x-icon name="check" class="size-3.5" />
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>

                    <a href="{{ route('shortlist') }}" x-data
                       class="uh-bar-quiet uh-bar-shortlist"
                       :aria-label="'{{ __('Shortlist') }} (' + $store.saved.shortlist.length + ')'" aria-label="{{ __('Shortlist') }}">
                        <x-icon name="heart" class="size-[1.125rem]" />
                        <span x-cloak x-show="$store.saved.shortlist.length" x-text="$store.saved.shortlist.length" class="uh-bar-count"></span>
                    </a>

                    <button type="button" class="uh-bar-quiet min-[1100px]:hidden"
                            @click="open = true; $dispatch('uh-menu', true)" :aria-expanded="open.toString()" aria-controls="mobile-nav">
                        <span class="sr-only">{{ __('Menu') }}</span>
                        <x-icon name="menu" class="size-5" />
                    </button>
                </div>
            </div>
        </header>

        <div id="mobile-nav" class="uh-menu min-[1100px]:hidden" x-cloak
             x-data="{ open: false }"
             x-show="open"
             x-transition.opacity.duration.250ms
             @uh-menu.window="open = $event.detail; $nextTick(() => open && $refs.close.focus())"
             @keydown.escape.window="if (open) { open = false; $dispatch('uh-menu', false) }"
             x-effect="document.documentElement.style.overflow = open ? 'hidden' : ''"
             role="dialog" aria-modal="true" aria-label="{{ __('Mobile navigation') }}">
            <div class="uh-container flex flex-1 flex-col pb-8">
                <div class="uh-menu-head">
                    <a href="{{ route('home') }}" class="uh-brand">
                        <span class="uh-wordmark">Urban Haven</span>
                    </a>
                    <button type="button" x-ref="close" class="uh-bar-quiet" @click="open = false; $dispatch('uh-menu', false)">
                        <span class="sr-only">{{ __('Close menu') }}</span>
                        <x-icon name="close" class="size-5" />
                    </button>
                </div>

                <nav class="uh-menu-list" aria-label="{{ __('Mobile navigation') }}">
                    @foreach($navLinks as $link)
                        <a href="{{ $link['url'] }}" @if($link['external']) rel="noopener" target="_blank" @endif
                           @if($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                    @endforeach
                    <a href="{{ route('shortlist') }}" x-data>
                        {{ __('Shortlist') }}
                        <span x-cloak x-show="$store.saved.shortlist.length" x-text="$store.saved.shortlist.length" class="uh-menu-count"></span>
                    </a>
                </nav>

                <div class="uh-menu-foot">
                    @if(filled($contactPhone))
                        <a href="{{ \App\Support\PhoneNumber::telHref($contactPhone) }}" data-track="phone_click" data-track-location="mobile_menu">
                            <span dir="ltr" class="uh-numeric">{{ $contactPhone }}</span>
                        </a>
                    @endif
                    <div class="flex items-center gap-1">
                        @foreach(['en' => 'EN', 'bn' => 'বাংলা'] as $code => $label)
                            <form method="POST" action="{{ route('locale.switch') }}">
                                @csrf
                                <input type="hidden" name="locale" value="{{ $code }}">
                                <button type="submit" lang="{{ $code }}" @class(['uh-menu-lang', 'is-active' => $locale === $code])
                                        @if($locale === $code) aria-current="true" @endif>{{ $label }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <main id="main">
            @if(session('status') || $errors->any())
                <div @class(['uh-container pt-6', 'relative z-10 pt-24' => $overlayHeader])>
                    <x-ui.flash />
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="uh-site-foot">
            <div class="uh-container">
                <div class="uh-foot-top">
                    <a href="{{ route('home') }}" class="uh-brand">
                        <span class="uh-wordmark">{{ $companyName }}</span>
                    </a>
                    <p class="uh-foot-line">
                        {{ __('Every listing on this site is published by our own team, not a marketplace of unknown sellers.') }}
                    </p>
                </div>

                <div class="uh-foot-columns">
                    <nav aria-label="{{ __('Explore') }}">
                        <p class="uh-foot-heading">{{ __('Explore') }}</p>
                        <ul class="uh-foot-list">
                            @if(in_array('sale', $purposes, true))
                                <li><a href="{{ route('properties.index', ['listing_type' => 'sale']) }}">{{ __('Properties for sale') }}</a></li>
                            @endif
                            @if(in_array('rent', $purposes, true))
                                <li><a href="{{ route('properties.index', ['listing_type' => 'rent']) }}">{{ __('Properties for rent') }}</a></li>
                            @endif
                            <li><a href="{{ route('properties.index', ['view' => 'map']) }}">{{ __('Map') }}</a></li>
                            <li><a href="{{ route('projects.index') }}">{{ __('Projects') }}</a></li>
                            <li><a href="{{ route('shortlist') }}">{{ __('Shortlist') }}</a></li>
                            <li><a href="{{ route('compare') }}">{{ __('Compare') }}</a></li>
                        </ul>
                    </nav>

                    <nav aria-label="{{ __('Company') }}">
                        <p class="uh-foot-heading">{{ __('Company') }}</p>
                        <ul class="uh-foot-list">
                            @foreach($footerLinks as $link)
                                <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </nav>

                    <div>
                        <p class="uh-foot-heading">{{ __('Contact') }}</p>
                        <ul class="uh-foot-list">
                            @if(filled($contactPhone))
                                <li>
                                    <a href="{{ \App\Support\PhoneNumber::telHref($contactPhone) }}" data-track="phone_click" data-track-location="footer">
                                        <span dir="ltr" class="uh-numeric">{{ $contactPhone }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($whatsappHref)
                                <li><a href="{{ $whatsappHref }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="footer">{{ __('WhatsApp') }}</a></li>
                            @endif
                            @if(filled($contactEmail))
                                <li><a class="break-all" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></li>
                            @endif
                            @if(filled($contactAddress))
                                <li class="uh-foot-address">{{ $contactAddress }}</li>
                            @endif
                        </ul>
                        @if($socialLinks)
                            <ul class="uh-foot-social">
                                @foreach($socialLinks as $social)
                                    <li>
                                        <a href="{{ $social }}" rel="noopener me" target="_blank">{{ \Illuminate\Support\Str::of(parse_url($social, PHP_URL_HOST))->replace('www.', '') }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="uh-foot-legal">
                    <p>© {{ date('Y') }} {{ $companyName }}</p>
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                        @if($privacyUrl)
                            <a href="{{ $privacyUrl }}">{{ __('Privacy') }}</a>
                        @endif
                        @if($hasAnalytics)
                            <button type="button" x-data @click="$dispatch('uh:consent-open')">{{ __('Cookie settings') }}</button>
                        @endif
                        <a href="{{ route('admin.login') }}" rel="nofollow">{{ __('Staff sign in') }}</a>
                    </div>
                </div>
            </div>
        </footer>

        <div class="uh-float" x-data="{ show: false, timer: null }"
             x-init="$watch('$store.saved.notice', (message) => { if (! message) return; show = true; clearTimeout(timer); timer = setTimeout(() => { show = false; $store.saved.notice = '' }, 3200) })">
            <div class="uh-toast" x-show="show" x-cloak x-transition.opacity.duration.250ms role="status" aria-live="polite">
                <span x-text="$store.saved.notice"></span>
            </div>
            @unless(request()->routeIs('compare'))
                <a href="{{ route('compare') }}" class="uh-compare-tray" x-show="$store.saved.compare.length > 0" x-cloak x-transition.opacity.duration.250ms>
                    <span x-text="$store.saved.compare.length === 1 ? @js(__('Pick one more to compare')) : @js(__('Compare :count properties')).replace(':count', $store.saved.compare.length)">{{ __('Compare') }}</span>
                    <x-icon name="arrow-right" class="size-3.5 shrink-0" />
                </a>
            @endunless
        </div>

        @if($hasAnalytics)
            <div x-data="uhConsent(@js($consent))" x-cloak x-show="open" @uh:consent-open.window="open = true"
                 x-transition.opacity.duration.250ms
                 class="uh-dialog uh-consent"
                 role="dialog" aria-modal="false" aria-labelledby="consent-title">
                <h2 id="consent-title" class="uh-h4">{{ __('Analytics cookies') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-[var(--uh-muted)]">
                    {{ __('We would like to measure visits and enquiries to improve this site. Nothing is loaded until you agree.') }}
                    @if($privacyUrl)
                        <a class="uh-link" href="{{ $privacyUrl }}">{{ __('Privacy') }}</a>
                    @endif
                </p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <button type="button" class="uh-btn-primary uh-btn-sm" @click="choose('granted')">{{ __('Allow analytics') }}</button>
                    <button type="button" class="uh-btn-secondary uh-btn-sm" @click="choose('denied')">{{ __('No thanks') }}</button>
                </div>
            </div>
        @endif

        @if(!empty($analyticsEvents))
            <script type="application/json" id="uh-page-events">{!! json_encode(array_values($analyticsEvents), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        @endif
    </body>
</html>
