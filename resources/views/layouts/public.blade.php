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
    $logoFooter = $brandUrl(\App\Models\Setting::get('brand_logo_footer')) ?? $logoDark;
    $favicon = $brandUrl(\App\Models\Setting::get('brand_favicon'));
    $footerNote = \App\Models\Setting::get('footer_note') ?: __('Every listing on this site is published by our own team, not a marketplace of unknown sellers.');
    $footerCopyright = \App\Models\Setting::get('footer_copyright');
    $footerDescription = \App\Models\Setting::get('footer_description') ?: $footerNote;
    $footerCtaLabel = \App\Models\Setting::get('footer_cta_label') ?: __('Explore properties');
    $footerCtaUrl = \App\Models\Setting::get('footer_cta_url') ?: route('properties.index');
    $footerMenuGroups = \App\Models\MenuItem::treeFor('footer');
    $footerBackground = \App\Models\Setting::get('footer_background', '#0d1110');
    $footerAccent = \App\Models\Setting::get('footer_accent', '#f3c88c');
    $footerLegalText = \App\Models\Setting::get('footer_legal_text');
    $footerCookieLink = (bool) \App\Models\Setting::get('footer_cookie_link', true);
    $footerStaffLink = (bool) \App\Models\Setting::get('footer_staff_link', true);
    $footerNewsletterEnabled = (bool) \App\Models\Setting::get('footer_newsletter_enabled', false);
    $socialProfiles = \App\Support\SocialProfiles::active();
    $footerExtraContacts = array_values(array_filter((array) \App\Models\Setting::get('footer_contacts', []), fn ($contact) => is_array($contact) && ($contact['active'] ?? false)));

    $themeVariables = \App\Support\ThemeColors::cssVariables();
    $themeDominant = $themeVariables['--color-dominant'];

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
    $privacyUrl = \App\Support\TaggedCache::remember(['cms'], 'privacy_page_exists', 3600, fn () => \App\Models\CmsPage::query()->where('slug', 'privacy')->published()->exists()) ? route('cms.show', 'privacy') : null;
    $isHome = request()->routeIs('home');
    $overlayHeader = $isHome || trim($__env->yieldContent('overlay_header')) !== '';

    $formatMenuLink = fn (\App\Models\MenuItem $item): array => [
        'label' => __($item->label),
        'url' => $item->url,
        'active' => $item->isInternal() && request()->getRequestUri() === $item->url,
        'opens_new_tab' => $item->opens_new_tab,
    ];
    $navLinks = \App\Models\MenuItem::treeFor('header')->map(function (\App\Models\MenuItem $item) use ($formatMenuLink): array {
        $link = $formatMenuLink($item);
        $link['children'] = $item->children->map($formatMenuLink)->all();
        $link['has_active_child'] = collect($link['children'])->contains('active', true);

        return $link;
    })->all();

    $footerLegalText = $footerLegalText ?: $footerCopyright;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ $themeDominant }}">
        @if($favicon)
            <link rel="icon" href="{{ $favicon }}">
            <link rel="apple-touch-icon" href="{{ $favicon }}">
        @endif
        <title>{{ $seo['title'] ?? config('app.name') }}</title>
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
                'form_rate_limit' => __('Too many requests. Please wait a moment and try again.'),
                'form_offline' => __('You appear to be offline. Check your connection and try again, or call us.'),
                'account_save_error' => __('We could not save your account. Please try again.'),
                'account_offline' => __('You appear to be offline. Check your connection and try again.'),
                'shortlist_clear_confirm' => __('Remove every property from your shortlist?'),
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
            <style>:root,body.uh-home{ {{ $themeStyle }} }</style>
        @endif
        @stack('head')
    </head>
    <body @class(['uh-home min-h-screen antialiased', 'uh-is-home' => $isHome, 'uh-has-overlay' => $overlayHeader])>
        <a class="uh-skip" href="#main">{{ __('Skip to content') }}</a>

        <header @class(['uh-site-bar', 'is-overlay' => $overlayHeader])
                x-data="uhSiteBar(@js($overlayHeader))"
                @uh-menu.window="open = $event.detail"
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
                        @if($link['children'])
                            <div class="uh-nav-entry" x-data="{ submenuOpen: false }" @click.outside="submenuOpen = false" @keydown.escape.stop="submenuOpen = false">
                                <div class="uh-nav-entry-row">
                                    <a href="{{ $link['url'] }}"
                                       @if($link['opens_new_tab']) rel="noopener" target="_blank" @endif
                                       @if($link['active']) aria-current="page" @endif
                                       @class(['uh-nav-link', 'uh-nav-link-active' => $link['active'] || $link['has_active_child']])>{{ $link['label'] }}</a>
                                    <button type="button" class="uh-nav-submenu-toggle"
                                            @click="submenuOpen = !submenuOpen" :aria-expanded="submenuOpen.toString()"
                                            aria-haspopup="true" aria-label="{{ __('Open :label submenu', ['label' => $link['label']]) }}">
                                        <x-icon name="chevron-down" class="size-3.5" />
                                    </button>
                                </div>
                                <div x-cloak x-show="submenuOpen" x-transition.origin.top.left class="uh-nav-submenu" role="group" aria-label="{{ $link['label'] }}">
                                    @foreach($link['children'] as $child)
                                        <a href="{{ $child['url'] }}"
                                           @if($child['opens_new_tab']) rel="noopener" target="_blank" @endif
                                           @if($child['active']) aria-current="page" @endif
                                           @class(['uh-nav-submenu-link', 'is-active' => $child['active']])>{{ $child['label'] }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ $link['url'] }}"
                               @if($link['opens_new_tab']) rel="noopener" target="_blank" @endif
                               @if($link['active']) aria-current="page" @endif
                               @class(['uh-nav-link', 'uh-nav-link-active' => $link['active']])>{{ $link['label'] }}</a>
                        @endif
                    @endforeach
                </nav>

                <div class="uh-bar-actions">
                    <a href="{{ route('compare') }}" x-data
                       class="uh-bar-quiet uh-bar-compare"
                       :aria-label="'{{ __('Compare') }} (' + $store.saved.compare.length + ')'" aria-label="{{ __('Compare') }}">
                        <x-icon name="compare" class="uh-bar-action-icon size-5" />
                        <span x-cloak x-show="$store.saved.compare.length" x-text="$store.saved.compare.length" class="uh-bar-count"></span>
                    </a>

                    <a href="{{ route('shortlist') }}" x-data
                       class="uh-bar-quiet uh-bar-shortlist"
                       :aria-label="'{{ __('Shortlist') }} (' + $store.saved.shortlist.length + ')'" aria-label="{{ __('Shortlist') }}">
                        <x-icon name="heart" class="uh-bar-action-icon size-5" />
                        <span x-cloak x-show="$store.saved.shortlist.length" x-text="$store.saved.shortlist.length" class="uh-bar-count"></span>
                    </a>

                    @auth
                        @if(auth()->user()->roles()->doesntExist())
                            <a href="{{ route('account.show') }}" class="uh-bar-quiet hidden sm:inline-flex" aria-label="{{ __('My property activity') }}" title="{{ __('My property activity') }}">
                                <x-icon name="user" class="size-[1.125rem]" />
                            </a>
                        @endif
                    @endauth

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
             @keydown.tab.prevent="open && $trapFocus($el, $event)"
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
                        @if($link['children'])
                            <div class="uh-menu-entry" x-data="{ submenuOpen: false }">
                                <div class="uh-menu-entry-row">
                                    <a href="{{ $link['url'] }}"
                                       @if($link['opens_new_tab']) rel="noopener" target="_blank" @endif
                                       @if($link['active']) aria-current="page" @endif
                                       @class(['is-parent-active' => $link['has_active_child']])>{{ $link['label'] }}</a>
                                    <button type="button" class="uh-menu-submenu-toggle"
                                            @click="submenuOpen = !submenuOpen" :aria-expanded="submenuOpen.toString()"
                                            aria-controls="mobile-submenu-{{ $loop->index }}"
                                            aria-label="{{ __('Open :label submenu', ['label' => $link['label']]) }}">
                                        <x-icon name="chevron-down" class="size-5" />
                                    </button>
                                </div>
                                <div id="mobile-submenu-{{ $loop->index }}" x-cloak x-show="submenuOpen" x-transition class="uh-menu-sublist">
                                    @foreach($link['children'] as $child)
                                        <a href="{{ $child['url'] }}"
                                           @if($child['opens_new_tab']) rel="noopener" target="_blank" @endif
                                           @if($child['active']) aria-current="page" @endif>{{ $child['label'] }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ $link['url'] }}" @if($link['opens_new_tab']) rel="noopener" target="_blank" @endif
                               @if($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                        @endif
                    @endforeach
                    <a href="{{ route('compare') }}" x-data>
                        {{ __('Compare') }}
                        <span x-cloak x-show="$store.saved.compare.length" x-text="$store.saved.compare.length" class="uh-menu-count"></span>
                    </a>
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

        <footer @class(['uh-site-foot', 'uh-site-foot-premium', 'has-floating-chat' => $showChat]) style="--uh-footer-bg: {{ $footerBackground }}; --uh-footer-accent: {{ $footerAccent }}">
            <div class="uh-foot-glow" aria-hidden="true"></div>
            <div class="uh-container relative z-10">
                <div class="uh-foot-premium-grid">
                    <section class="uh-foot-brand-column">
                        <a href="{{ route('home') }}" class="uh-brand">
                            @if($logoFooter)
                                <img class="uh-brand-logo-img" src="{{ $logoFooter }}" alt="{{ $companyName }}">
                            @else
                                <span class="uh-wordmark">{{ $companyName }}</span>
                            @endif
                        </a>
                        @if(filled($footerDescription))<p class="uh-foot-line">{{ $footerDescription }}</p>@endif
                        @if(filled(\App\Models\Setting::get('footer_tagline')))<p class="uh-foot-tagline">{{ \App\Models\Setting::get('footer_tagline') }}</p>@endif
                        @if(filled($footerCtaLabel))
                            <a class="uh-foot-cta-link" href="{{ $footerCtaUrl }}" @if(str_starts_with($footerCtaUrl, 'https://') && ! str_starts_with($footerCtaUrl, url('/'))) rel="noopener" target="_blank" @endif>
                                <span>{{ $footerCtaLabel }}</span><span aria-hidden="true">→</span>
                            </a>
                        @endif
                    </section>

                    <div class="uh-foot-columns uh-foot-premium-columns">
                        @foreach($footerMenuGroups as $group)
                            <nav aria-label="{{ __($group->label) }}">
                                <p class="uh-foot-heading">{{ __($group->label) }}</p>
                                <ul class="uh-foot-list">
                                    @foreach($group->children as $item)
                                        <li><a href="{{ $item->url }}" @if($item->opens_new_tab) rel="noopener" target="_blank" @endif>{{ __($item->label) }}</a></li>
                                    @endforeach
                                    @if($group->children->isEmpty() && $group->url !== '#')
                                        <li><a href="{{ $group->url }}" @if($group->opens_new_tab) rel="noopener" target="_blank" @endif>{{ __($group->label) }}</a></li>
                                    @endif
                                </ul>
                            </nav>
                        @endforeach
                    </div>
                </div>

                <div class="uh-foot-legal">
                    <p>© {{ \App\Models\Setting::get('footer_year_dynamic', true) ? date('Y') : (\App\Models\Setting::get('footer_copyright_year') ?: date('Y')) }} {{ $companyName }}{{ filled($footerLegalText) ? '. '.$footerLegalText : '' }}</p>
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                        @if($privacyUrl)<a href="{{ $privacyUrl }}">{{ __('Privacy') }}</a>@endif
                        @if($footerCookieLink && $hasAnalytics && $consentBanner)<button type="button" x-data @click="$dispatch('uh:consent-open')">{{ __('Cookie settings') }}</button>@endif
                        @if($footerStaffLink)<a href="{{ route('admin.login') }}" rel="nofollow">{{ __('Staff sign in') }}</a>@endif
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
                <div class="uh-compare-tray !pr-2" style="padding-right: 0.5rem;" x-show="$store.saved.compare.length > 1" x-cloak x-transition.opacity.duration.250ms>
                    <a href="{{ route('compare') }}" class="flex items-center gap-[0.625rem] py-2 outline-none">
                        <span x-text="$store.saved.compare.length === 1 ? @js(__('Pick one more to compare')) : @js(__('Compare :count properties')).replace(':count', $store.saved.compare.length)">{{ __('Compare') }}</span>
                        <x-icon name="arrow-right" class="size-3.5 shrink-0" />
                    </a>
                    <button type="button" @click.prevent="$store.saved.clear('compare')" class="flex items-center justify-center p-1.5 rounded-full hover:bg-white/20 transition-colors ml-1 outline-none" aria-label="{{ __('Clear compare') }}">
                        <x-icon name="close" class="size-4 shrink-0" />
                    </button>
                </div>
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
                <h2 id="consent-title" class="uh-h4">{{ __($consentTitle) }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-[var(--uh-muted)]">
                    {{ __($consentMessage) }}
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
