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
        'ads' => config('urbanhaven.analytics.google_ads_id'),
    ];
    $hasAnalytics = filled(array_filter($analytics));
    $consentCookie = config('urbanhaven.analytics.consent_cookie');
    $consentBanner = (bool) \App\Models\Setting::get('consent_banner_enabled', true);
    $consent = $consentBanner ? request()->cookie($consentCookie) : 'granted';
    $analyticsConfig = ['ids' => $analytics, 'cookie' => $consentCookie, 'trackUrl' => route('track'), 'autoConsent' => ! $consentBanner];
    $consentTitle = \App\Models\Setting::get('consent_title') ?: __('Analytics cookies');
    $consentMessage = \App\Models\Setting::get('consent_message') ?: __('We would like to measure visits and enquiries to improve this site. Nothing is loaded until you agree.');

    $brandUrl = fn (?string $path): ?string => filled($path) ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
    $logoLight = $brandUrl(\App\Models\Setting::get('brand_logo'));
    $logoDark = $brandUrl(\App\Models\Setting::get('brand_logo_dark')) ?? $logoLight;
    $logoMobile = $brandUrl(\App\Models\Setting::get('brand_logo_mobile'));
    $logoFooter = $brandUrl(\App\Models\Setting::get('brand_logo_footer')) ?? $logoLight;
    $favicon = $brandUrl(\App\Models\Setting::get('brand_favicon'));
    $headerCtaLabel = \App\Models\Setting::get('header_cta_label');
    $headerCtaUrl = \App\Models\Setting::get('header_cta_url') ?: '/contact';
    $footerNote = \App\Models\Setting::get('footer_note') ?: __('Every listing on this site is published by our own team, not a marketplace of unknown sellers.');
    $footerCopyright = \App\Models\Setting::get('footer_copyright');
    $socialProfiles = \App\Support\SocialProfiles::active();

    $savedSettings = \App\Models\Setting::allValues();
    $themeVariables = array_filter([
        '--uh-accent' => $savedSettings['theme_accent'] ?? null,
        '--uh-night' => $savedSettings['theme_dark'] ?? null,
        '--uh-paper' => $savedSettings['theme_surface'] ?? null,
        '--color-forest' => $savedSettings['theme_primary'] ?? null,
        '--color-emerald' => $savedSettings['theme_primary'] ?? null,
        '--uh-primary' => $savedSettings['theme_primary'] ?? null,
    ], fn ($value) => is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value));

    $chatWhatsapp = \App\Support\PhoneNumber::whatsappHref(\App\Models\Setting::get('whatsapp'), \App\Models\Setting::get('floating_chat_message'));
    $chatMessenger = \App\Models\Setting::get('messenger_url');
    $chatClasses = \Illuminate\Support\Arr::toCssClasses([
        'uh-chat-float',
        'is-left' => \App\Models\Setting::get('floating_chat_position') === 'bottom-left',
        'hide-desktop' => ! \App\Models\Setting::get('floating_chat_desktop', true),
        'hide-mobile' => ! \App\Models\Setting::get('floating_chat_mobile', true),
    ]);
    $themeStyle = collect($themeVariables)->map(fn ($colour, $variable) => $variable.':'.$colour)->implode(';');
    $showChat = (bool) \App\Models\Setting::get('floating_chat_enabled', true) && ($chatWhatsapp || $chatMessenger);
    $privacyUrl = \App\Models\CmsPage::query()->where('slug', 'privacy')->published()->exists() ? route('cms.show', 'privacy') : null;
    $isHome = request()->routeIs('home');
    $overlayHeader = $isHome || trim($__env->yieldContent('overlay_header')) !== '';

    $headerMenu = \App\Models\MenuItem::forLocation('header');
    $navLinks = $headerMenu->isNotEmpty()
        ? $headerMenu->map(fn ($item) => ['label' => $item->label, 'url' => $item->url, 'external' => ! $item->isInternal(), 'active' => $item->isInternal() && request()->getRequestUri() === $item->url])->all()
        : array_values(array_filter([
            in_array('sale', $purposes, true) ? ['label' => __('Buy'), 'url' => route('properties.index', ['listing_type' => 'sale']), 'external' => false, 'active' => request()->routeIs('properties.index') && request('listing_type') === 'sale'] : null,
            in_array('rent', $purposes, true) ? ['label' => __('Rent'), 'url' => route('properties.index', ['listing_type' => 'rent']), 'external' => false, 'active' => request()->routeIs('properties.index') && request('listing_type') === 'rent'] : null,
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
        @if($favicon)
            <link rel="icon" href="{{ $favicon }}">
            <link rel="apple-touch-icon" href="{{ $favicon }}">
        @endif
        <title>{{ ($seo['title'] ?? config('app.name')) === config('app.name') ? config('app.name') : ($seo['title'].' — '.config('app.name')) }}</title>
        @include('partials.seo-meta')
        <script>
            window.dataLayer = window.dataLayer || [];
            window.uhAnalytics = {!! json_encode($analyticsConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
            window.uhCopy = @js([
                'saved_removed_compare' => __('Removed from compare.'),
                'saved_removed_shortlist' => __('Removed from your shortlist.'),
                'saved_compare_limit' => __('You can compare up to :limit properties. Remove one first.'),
                'saved_shortlist_limit' => __('Your shortlist is full (:limit). Remove one first.'),
                'saved_compare_added_first' => __('Added to compare. Add one more to see them side by side.'),
                'saved_compare_added' => __('Added. :count properties ready to compare.'),
                'saved_shortlist_added' => __('Saved. It stays on this device — find it any time under Shortlist.'),
                'saved_storage_error' => __('Your browser is blocking storage, so this list will reset when you leave.'),
                'map_no_results' => __('No properties with a map location match these filters.'),
                'map_cluster_title' => __(':count properties'),
                'map_load_error' => __('The map could not load. The list still shows every result.'),
                'map_tiles_error' => __('Map tiles are not loading right now. Pins may still be shown.'),
                'map_pins_error' => __('Map pins could not load. The list still shows every result.'),
                'select_keyword' => __('Search for “:term”'),
                'select_empty' => __('No matching areas'),
                'select_searching' => __('Searching…'),
                'select_error' => __('Suggestions could not load'),
                'form_check_fields' => __('Please check the highlighted fields.'),
                'form_expired' => __('This page has expired. Refresh and try again.'),
                'form_send_error' => __('We could not send your request. Please try again or call us.'),
                'form_offline' => __('You appear to be offline. Check your connection and try again, or call us.'),
                'account_save_error' => __('We could not save your account. Please try again.'),
                'account_offline' => __('You appear to be offline. Check your connection and try again.'),
            ]);
            window.uhCopyText = (key, replacements = {}) => Object.entries(replacements).reduce(
                (text, [name, value]) => text.split(`:${name}`).join(String(value)),
                window.uhCopy[key] || '',
            );
        </script>
        @if($hasAnalytics)
            <script>
                function gtag(){dataLayer.push(arguments);}
                gtag('consent', 'default', {ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'denied', wait_for_update: 500});
            </script>
        @endif
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @if($themeVariables)
            <style>body.uh-home{ {{ $themeStyle }} }</style>
        @endif
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
                    @if($logoLight)
                        <picture class="uh-brand-logo is-light">
                            @if($logoMobile)<source media="(max-width: 639px)" srcset="{{ $logoMobile }}">@endif
                            <img src="{{ $logoLight }}" alt="{{ $companyName }}">
                        </picture>
                        <picture class="uh-brand-logo is-dark">
                            @if($logoMobile)<source media="(max-width: 639px)" srcset="{{ $logoMobile }}">@endif
                            <img src="{{ $logoDark }}" alt="{{ $companyName }}">
                        </picture>
                    @else
                        <span class="uh-wordmark">{{ $companyName }}</span>
                    @endif
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

                    @auth
                        @if(auth()->user()->roles()->doesntExist())
                            <a href="{{ route('account.show') }}" class="uh-bar-quiet hidden sm:inline-flex" aria-label="{{ __('My property activity') }}" title="{{ __('My property activity') }}">
                                <x-icon name="user" class="size-[1.125rem]" />
                            </a>
                        @endif
                    @endauth

                    @if(filled($headerCtaLabel))
                        <a href="{{ $headerCtaUrl }}" class="uh-bar-cta hidden sm:inline-flex" data-track="header_cta_click">{{ $headerCtaLabel }}</a>
                    @endif

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
                        @if($logoLight)
                            <img class="uh-brand-logo-img" src="{{ $logoMobile ?? $logoLight }}" alt="{{ $companyName }}">
                        @else
                            <span class="uh-wordmark">{{ $companyName }}</span>
                        @endif
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
                    @if(filled($headerCtaLabel))
                        <a href="{{ $headerCtaUrl }}">{{ $headerCtaLabel }}</a>
                    @endif
                    <a href="{{ route('shortlist') }}" x-data>
                        {{ __('Shortlist') }}
                        <span x-cloak x-show="$store.saved.shortlist.length" x-text="$store.saved.shortlist.length" class="uh-menu-count"></span>
                    </a>
                    @auth
                        @if(auth()->user()->roles()->doesntExist())
                            <a href="{{ route('account.show') }}">{{ __('My property activity') }}</a>
                        @endif
                    @endauth
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
                        @if($logoFooter)
                            <img class="uh-brand-logo-img" src="{{ $logoFooter }}" alt="{{ $companyName }}">
                        @else
                            <span class="uh-wordmark">{{ $companyName }}</span>
                        @endif
                    </a>
                    <p class="uh-foot-line">{{ $footerNote }}</p>
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
                            <li><a href="{{ route('map') }}">{{ __('Map') }}</a></li>
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
                        @if($socialProfiles)
                            <ul class="uh-foot-social">
                                @foreach($socialProfiles as $social)
                                    <li>
                                        <a href="{{ $social['url'] }}" rel="noopener me" @if($social['new_tab']) target="_blank" @endif>{{ $social['label'] ?: (\App\Support\SocialProfiles::PLATFORMS[$social['platform']] ?? \Illuminate\Support\Str::of(parse_url($social['url'], PHP_URL_HOST))->replace('www.', '')) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="uh-foot-legal">
                    <p>© {{ date('Y') }} {{ $companyName }}{{ filled($footerCopyright) ? '. '.$footerCopyright : '' }}</p>
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                        @if($privacyUrl)
                            <a href="{{ $privacyUrl }}">{{ __('Privacy') }}</a>
                        @endif
                        @if($hasAnalytics && $consentBanner)
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
                <a href="{{ route('compare') }}" class="uh-compare-tray" x-show="$store.saved.compare.length > 1" x-cloak x-transition.opacity.duration.250ms>
                    <span x-text="$store.saved.compare.length === 1 ? @js(__('Pick one more to compare')) : @js(__('Compare :count properties')).replace(':count', $store.saved.compare.length)">{{ __('Compare') }}</span>
                    <x-icon name="arrow-right" class="size-3.5 shrink-0" />
                </a>
            @endunless
        </div>

        @if($showChat)
            <div class="{{ $chatClasses }}">
                @if($chatMessenger)
                    <a href="{{ $chatMessenger }}" class="uh-chat-btn is-messenger" rel="noopener" target="_blank" aria-label="{{ __('Chat on Messenger') }}" data-track="messenger_click" data-track-location="floating">
                        <svg viewBox="0 0 24 24" class="size-6" fill="currentColor" aria-hidden="true"><path d="M12 2C6.36 2 2 6.13 2 11.7c0 2.91 1.19 5.44 3.14 7.17.16.15.26.35.27.57l.05 1.78a.8.8 0 0 0 1.12.71l1.98-.87a.8.8 0 0 1 .53-.04c.91.25 1.87.38 2.91.38 5.64 0 10-4.13 10-9.7S17.64 2 12 2Zm6 7.46-2.94 4.66a1.5 1.5 0 0 1-2.17.4l-2.34-1.75a.6.6 0 0 0-.72 0l-3.16 2.4c-.42.32-.97-.18-.69-.63l2.94-4.66a1.5 1.5 0 0 1 2.17-.4l2.34 1.75a.6.6 0 0 0 .72 0l3.16-2.4c.42-.32.97.18.69.63Z"/></svg>
                    </a>
                @endif
                @if($chatWhatsapp)
                    <a href="{{ $chatWhatsapp }}" class="uh-chat-btn is-whatsapp" rel="noopener" target="_blank" aria-label="{{ __('Chat on WhatsApp') }}" data-track="whatsapp_click" data-track-location="floating">
                        <x-icon name="whatsapp" class="size-6" />
                    </a>
                @endif
            </div>
        @endif

        @if($hasAnalytics && $consentBanner)
            <div x-data="uhConsent(@js($consent))" x-cloak x-show="open" @uh:consent-open.window="open = true"
                 x-transition.opacity.duration.250ms
                 class="uh-dialog uh-consent"
                 role="dialog" aria-modal="false" aria-labelledby="consent-title">
                <h2 id="consent-title" class="uh-h4">{{ $consentTitle }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-[var(--uh-muted)]">
                    {{ $consentMessage }}
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
