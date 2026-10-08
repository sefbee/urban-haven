@php
    $socialProfiles = collect((array) \App\Models\Setting::get('social_links', []))
        ->filter(fn ($profile): bool => is_array($profile) && ($profile['active'] ?? true) && filled($profile['url'] ?? null))
        ->take(4);
    $configuredMapUrl = \App\Models\Setting::get('map_url');
    $mapUrl = filled($configuredMapUrl)
        ? $configuredMapUrl
        : (filled($address) ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($address) : null);
    $contactImage = $page->galleryImages()->first();
@endphp

<div class="uh-container space-y-5 pb-14 pt-8 sm:space-y-7 sm:pb-20 sm:pt-12">
    <section class="relative isolate overflow-hidden rounded-[2rem] bg-night text-white sm:rounded-[2.5rem]" aria-labelledby="contact-title">
        <img src="{{ $contactImage?->url(1920) ?? 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=2200&q=85' }}"
             alt="{{ $contactImage?->alt(app()->getLocale()) ?: __('Contemporary home surrounded by greenery') }}"
             class="absolute inset-0 -z-20 size-full object-cover object-center" fetchpriority="high">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-[#101a27]/95 via-[#101a27]/85 to-[#101a27]/55"></div>

        <div class="grid gap-8 p-5 sm:gap-10 sm:p-8 lg:grid-cols-[minmax(0,1fr)_minmax(24rem,.88fr)] lg:gap-12 lg:p-12 xl:p-16">
            <div class="flex flex-col justify-between gap-12 py-3 sm:py-6">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gold-soft">{{ __('We’re here to help') }}</p>
                    <h1 id="contact-title" class="mt-5 text-balance text-5xl leading-[1.02] font-medium tracking-[-0.05em] sm:text-6xl lg:text-7xl">{{ $page->title }}</h1>
                    <p class="mt-6 max-w-xl text-base leading-relaxed text-white/75 sm:text-lg">
                        {{ filled(trim(strip_tags((string) $page->body)))
                            ? trim(strip_tags((string) $page->body))
                            : __('Tell us what you are looking for. Our team is ready to help with every detail, big or small.') }}
                    </p>
                    @if($whatsappHref)
                        <a href="{{ $whatsappHref }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="contact_hero"
                           class="mt-7 inline-flex min-h-12 items-center gap-3 rounded-full border border-white/35 bg-white/10 px-5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20">
                            <x-icon name="whatsapp" class="size-5" />
                            {{ __('Chat with us on WhatsApp') }}
                            <x-icon name="arrow-right" class="size-4" />
                        </a>
                    @endif
                </div>

                <div class="grid gap-x-8 gap-y-7 border-t border-white/20 pt-7 sm:grid-cols-2 sm:gap-y-8">
                    @if(filled($address))
                        <div>
                            <h2 class="text-lg font-semibold">{{ __('Visit our office') }}</h2>
                            <p class="mt-2 max-w-xs text-sm leading-relaxed text-white/70">{{ $address }}</p>
                            @if(filled($officeHours))
                                <p class="mt-2 text-sm leading-relaxed text-white/60">{{ $officeHours }}</p>
                            @endif
                            @if($mapUrl)
                                <a href="{{ $mapUrl }}" rel="noopener" target="_blank" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-white underline decoration-white/40 underline-offset-4 hover:decoration-white">
                                    <x-icon name="pin" class="size-4" />
                                    {{ __('Open directions') }}
                                </a>
                            @endif
                        </div>
                    @endif

                    <div>
                        <h2 class="text-lg font-semibold">{{ __('Call or email') }}</h2>
                        <div class="mt-2 grid justify-items-start gap-2 text-sm text-white/75">
                            @if(filled($phone))
                                <a href="{{ \App\Support\PhoneNumber::telHref($phone) }}" dir="ltr" data-track="phone_click" data-track-location="contact_page" class="hover:text-white">{{ $phone }}</a>
                            @endif
                            @if(filled($email))
                                <a href="mailto:{{ $email }}" class="break-all hover:text-white">{{ $email }}</a>
                            @endif
                            @if($whatsappHref)
                                <a href="{{ $whatsappHref }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="contact_page" class="hover:text-white">{{ __('WhatsApp us') }}</a>
                            @endif
                        </div>
                    </div>

                    @if($socialProfiles->isNotEmpty())
                        <div class="sm:col-span-2">
                            <h2 class="text-sm font-semibold text-white/65">{{ __('Follow along') }}</h2>
                            <ul class="mt-3 flex flex-wrap gap-2">
                                @foreach($socialProfiles as $profile)
                                    <li>
                                        <a href="{{ $profile['url'] }}" rel="noopener" target="_blank" class="inline-flex min-h-10 items-center rounded-full border border-white/20 px-4 text-sm text-white/80 transition hover:bg-white/10 hover:text-white">
                                            {{ $profile['label'] ?: ucfirst($profile['platform'] ?? __('Social')) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <aside id="page-form-heading" class="min-w-0 scroll-mt-24 rounded-[1.5rem] bg-paper p-5 text-ink shadow-2xl shadow-black/20 sm:rounded-[2rem] sm:p-6 lg:p-7" aria-labelledby="contact-form-title">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-ink">{{ __('Contact Urban Haven') }}</p>
                <h2 id="contact-form-title" class="mt-3 text-3xl leading-tight font-semibold tracking-[-0.035em] sm:text-4xl">{{ __('Tell us what you need.') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-muted sm:text-base">{{ __('Share a few details and our team will get back to you.') }}</p>

                <div class="mt-5">
                    @include('public.partials.lead-form', [
                        'leadType' => 'general_contact',
                        'source' => 'contact_page',
                        'prefix' => 'page',
                        'messageLabel' => __('How can we help?'),
                        'messagePlaceholder' => __('Tell us about the property, area or information you need…'),
                        'submitLabel' => __('Send message'),
                        'compact' => true,
                        'showNextSteps' => false,
                    ])
                </div>
            </aside>
        </div>
    </section>

    @if($mapUrl)
        <section class="grid gap-5 rounded-[2rem] bg-paper p-6 sm:grid-cols-[1fr_auto] sm:items-center sm:rounded-[2.5rem] sm:p-9" aria-labelledby="contact-location-title">
            <div class="flex items-start gap-4">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-sand text-ink">
                    <x-icon name="pin" class="size-5" />
                </span>
                <div>
                    <h2 id="contact-location-title" class="text-lg font-semibold sm:text-xl">{{ __('Find our office') }}</h2>
                    <p class="mt-1 text-sm leading-relaxed text-muted">{{ $address }}</p>
                </div>
            </div>
            <a href="{{ $mapUrl }}" rel="noopener" target="_blank" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-night px-6 text-sm font-semibold text-white transition hover:bg-forest">
                {{ __('View on Google Maps') }}
                <x-icon name="arrow-right" class="size-4" />
            </a>
        </section>
    @endif
</div>
