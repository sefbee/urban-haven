@php
    $contactSections = $contactSections ?? \App\Support\ContactPageSections::visible();
    $socialProfiles = collect((array) \App\Models\Setting::get('social_links', []))
        ->filter(fn ($profile): bool => is_array($profile) && ($profile['active'] ?? true) && filled($profile['url'] ?? null))
        ->take(4);
    $configuredMapUrl = \App\Models\Setting::get('map_url');
    $mapLocation = filled($address) ? $address : 'Dhaka, Bangladesh';
    $mapEmbedUrl = filled($address)
        ? 'https://www.google.com/maps?q='.urlencode($address).'&output=embed'
        : (filled($configuredMapUrl) ? $configuredMapUrl : 'https://www.google.com/maps?q='.urlencode($mapLocation).'&output=embed');
    $contactImage = $page->galleryImages()->first();
    $channels = $contactSections['channels'] ?? [];
    $hasOfficeChannel = filled($address)
        || filled($channels['office_title'] ?? null)
        || filled($channels['office_intro'] ?? null);
    $enquiry = $contactSections['enquiry'] ?? [];
    $office = $contactSections['office'] ?? [];
    $direct = $contactSections['direct'] ?? [];
    $cta = $contactSections['cta'] ?? [];
@endphp

<main class="uh-container uh-contact-page overflow-hidden bg-white pb-0">
    {{-- Hero Section --}}
    @if(isset($contactSections['hero']))
    <div class="uh-contact-hero group flex flex-col lg:flex-row min-h-[550px] 2xl:min-h-[650px] overflow-hidden">
        <div class="w-full lg:w-1/2 bg-[var(--color-dominant)] flex items-center justify-center px-[var(--uh-gutter)] py-12 lg:py-16 relative z-10">
            {{-- Subtle background decoration --}}
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none opacity-20">
                <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-gradient-to-br from-[var(--color-accent)]/20 to-transparent blur-3xl"></div>
            </div>
            
            <div class="w-full max-w-xl relative">
                <div class="inline-flex items-center gap-3 mb-5">
                    <span class="w-8 h-px bg-[var(--color-accent)]"></span>
                    @if(filled($contactSections['hero']['eyebrow'] ?? null))<p class="text-[11px] font-bold tracking-[0.2em] text-[var(--color-accent)] uppercase">{{ $contactSections['hero']['eyebrow'] }}</p>@endif
                </div>
                @if(filled($contactSections['hero']['title'] ?? null))<h1 class="text-[2.75rem] leading-[1.05] sm:text-6xl lg:text-[4rem] font-display text-[var(--color-body-text)] text-balance transition-all duration-700">{{ $contactSections['hero']['title'] }}</h1>@endif
                @if(filled($contactSections['hero']['intro'] ?? null))<p class="mt-6 text-[15px] sm:text-base leading-relaxed text-[var(--color-muted-text)] max-w-[480px]">{{ $contactSections['hero']['intro'] }}</p>@endif

            </div>
        </div>
        <div class="w-full lg:w-1/2 h-[400px] lg:h-auto relative overflow-hidden">
            <div class="absolute inset-0 bg-[var(--color-secondary)]/10 mix-blend-multiply z-10 transition-opacity duration-700 group-hover:opacity-0"></div>
            @php
                $heroPhoto = \App\Models\CmsBlock::imagesFor('contact_office')->first() ?? $contactImage;
            @endphp
            <img src="{{ $heroPhoto?->url(1920) ?? 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=2200&q=85' }}"
                 alt="{{ $heroPhoto?->alt(app()->getLocale()) ?: __('Contemporary home surrounded by greenery') }}"
                 class="absolute inset-0 size-full object-cover object-center transition-transform duration-[2000ms] ease-out group-hover:scale-105">
        </div>
    </div>
    @endif

    {{-- How Can We Help Section --}}
    @if(isset($contactSections['channels']) && (filled($phone) || filled($email) || $hasOfficeChannel))
    <section class="uh-contact-channels py-24 bg-[var(--color-card-surface)] border-b border-[var(--color-divider)] relative overflow-hidden">
        <div class="uh-container relative z-10">
            @if(filled($channels['title'] ?? null) || filled($channels['intro'] ?? null))
                <div class="text-center max-w-2xl mx-auto">
                    <x-icon name="sparkles" class="size-6 text-[var(--color-accent)] mx-auto mb-4 opacity-50" />
                    @if(filled($channels['title'] ?? null))<h2 class="text-3xl sm:text-4.5xl font-display text-[var(--color-body-text)]">{{ $channels['title'] }}</h2>@endif
                    @if(filled($channels['intro'] ?? null))<p class="mt-4 text-[16px] text-[var(--color-muted-text)]">{{ $channels['intro'] }}</p>@endif
                </div>
            @endif
            
            <div class="uh-contact-channel-grid mt-16 grid gap-6 md:grid-cols-3">
                {{-- Call Us --}}
                @if(filled($phone))
                <div class="uh-contact-channel-card group rounded-2xl border border-[var(--color-divider)] bg-[var(--color-card-surface)] p-8 xl:p-10 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)] transition-all duration-500 hover:shadow-xl hover:-translate-y-1 text-center flex flex-col items-center">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[var(--color-dominant)] text-[var(--color-accent)] border border-[var(--color-divider)] transition-all duration-500 group-hover:bg-[var(--color-accent)] group-hover:text-white group-hover:border-[var(--color-accent)] shadow-sm">
                        <x-icon name="phone" class="size-5 transition-transform duration-500 group-hover:scale-110" />
                    </div>
                    @if(filled($channels['phone_title'] ?? null))<h3 class="mt-6 font-display text-[22px] text-[var(--color-body-text)] transition-colors group-hover:text-[var(--color-accent)]">{{ $channels['phone_title'] }}</h3>@endif
                    @if(filled($phone))<a href="{{ \App\Support\PhoneNumber::telHref($phone) }}" class="mt-2 inline-block text-[15px] font-semibold text-[var(--color-body-text)] hover:text-[var(--color-accent)] transition-colors">{{ $phone }}</a>@endif
                    @if(filled($channels['phone_intro'] ?? null))<p class="mt-3 text-[14px] text-[var(--color-muted-text)] leading-relaxed max-w-[220px]">{{ $channels['phone_intro'] }}</p>@endif
                </div>
                @endif
                
                {{-- Email Us (elevated) --}}
                @if(filled($email))
                <div class="uh-contact-channel-card group rounded-2xl border border-[var(--color-divider)] border-t-[3px] border-t-[var(--color-accent)] bg-[var(--color-card-surface)] p-8 xl:p-10 shadow-[0_12px_40px_rgb(0,0,0,0.08)] transition-all duration-500 hover:shadow-[0_20px_50px_rgb(0,0,0,0.12)] hover:-translate-y-4 relative transform md:-translate-y-2 text-center flex flex-col items-center z-10">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[var(--color-dominant)] text-[var(--color-accent)] border border-[var(--color-divider)] transition-all duration-500 group-hover:bg-[var(--color-accent)] group-hover:text-white group-hover:border-[var(--color-accent)] shadow-sm">
                        <x-icon name="mail" class="size-5 transition-transform duration-500 group-hover:scale-110" />
                    </div>
                    @if(filled($channels['email_title'] ?? null))<h3 class="mt-6 font-display text-[22px] text-[var(--color-body-text)] transition-colors group-hover:text-[var(--color-accent)]">{{ $channels['email_title'] }}</h3>@endif
                    @if(filled($email))<a href="mailto:{{ $email }}" class="mt-2 inline-block text-[15px] font-semibold text-[var(--color-body-text)] hover:text-[var(--color-accent)] transition-colors">{{ $email }}</a>@endif
                    @if(filled($channels['email_intro'] ?? null))<p class="mt-3 text-[14px] text-[var(--color-muted-text)] leading-relaxed max-w-[220px]">{{ $channels['email_intro'] }}</p>@endif
                </div>
                @endif
                
                {{-- Visit Our Office --}}
                @if($hasOfficeChannel)
                <div class="uh-contact-channel-card group rounded-2xl border border-[var(--color-divider)] bg-[var(--color-card-surface)] p-8 xl:p-10 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)] transition-all duration-500 hover:shadow-xl hover:-translate-y-1 text-center flex flex-col items-center">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[var(--color-dominant)] text-[var(--color-accent)] border border-[var(--color-divider)] transition-all duration-500 group-hover:bg-[var(--color-accent)] group-hover:text-white group-hover:border-[var(--color-accent)] shadow-sm">
                        <x-icon name="pin" class="size-5 transition-transform duration-500 group-hover:scale-110" />
                    </div>
                    @if(filled($channels['office_title'] ?? null))<h3 class="mt-6 font-display text-[22px] text-[var(--color-body-text)] transition-colors group-hover:text-[var(--color-accent)]">{{ $channels['office_title'] }}</h3>@endif
                    @if(filled($address))<p class="mt-2 text-[15px] font-semibold text-[var(--color-body-text)]">{{ $address }}</p>@endif
                    @if(filled($channels['office_intro'] ?? null))<p class="mt-3 text-[14px] text-[var(--color-muted-text)] leading-relaxed max-w-[220px]">{{ $channels['office_intro'] }}</p>@endif
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    {{-- Form & Sidebar Section --}}
    <section class="uh-contact-enquiry uh-container py-20 lg:py-28 bg-white" id="page-form-heading">
        <div class="grid gap-12 lg:grid-cols-[1fr_400px] xl:grid-cols-[1fr_450px] lg:gap-16 xl:gap-24">
            
            {{-- Form --}}
            <div class="pt-2">
                @if(filled($enquiry['eyebrow'] ?? null))<div class="inline-flex items-center gap-3 mb-4"><span class="w-6 h-px bg-[var(--color-accent)]"></span><p class="text-[11px] font-bold tracking-[0.2em] text-[var(--color-accent)] uppercase">{{ $enquiry['eyebrow'] }}</p></div>@endif
                @if(filled($enquiry['title'] ?? null))<h2 class="text-[2rem] sm:text-[2.75rem] leading-[1.1] font-display text-[var(--color-body-text)]">{{ $enquiry['title'] }}</h2>@endif
                @if(filled($enquiry['intro'] ?? null))<p class="mt-5 text-[16px] text-[var(--color-muted-text)] leading-relaxed max-w-[500px]">{{ $enquiry['intro'] }}</p>@endif
                
                <div class="mt-8">
                    @include('public.partials.lead-form', [
                        'leadType' => 'general_contact',
                        'showSuggestedVisitTimes' => false,
                        'showVisitNotes' => false,
                        'showNextSteps' => false,
                    ])
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="uh-contact-office bg-[var(--color-secondary)] flex flex-col rounded-3xl overflow-hidden shadow-[0_20px_60px_rgba(13,17,16,0.2)] group relative border border-white/5">
                {{-- Decorative gradient background --}}
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-[var(--color-accent)]/10 via-night to-night pointer-events-none"></div>

                @php
                    $officeImage = \App\Models\CmsBlock::imagesFor('contact_office')->first();
                @endphp
                <div class="h-[260px] w-full relative overflow-hidden">
                    <div class="absolute inset-0 bg-[var(--color-secondary)]/20 mix-blend-multiply z-10"></div>
                    <img src="{{ $officeImage?->url(800) ?? 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=85' }}" alt="{{ $officeImage?->alt(app()->getLocale()) ?: __('Office Interior') }}" class="absolute inset-0 size-full object-cover transition-transform duration-[2000ms] ease-out group-hover:scale-110">
                </div>
                <div class="p-8 sm:p-10 flex-1 flex flex-col relative text-white z-10">
                    @if(filled($office['title'] ?? null))<h3 class="font-display text-[32px] text-white">{{ $office['title'] }}</h3>@endif
                    
                    <div class="mt-10 space-y-8 flex-1">
                        @if(filled($address))<div class="flex gap-5 group/item">
                            <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/5 border border-white/10 transition-colors duration-300 group-hover/item:bg-[var(--color-accent)] group-hover/item:border-[var(--color-accent)]">
                                <x-icon name="pin" class="size-[18px] text-[var(--color-accent)] transition-colors duration-300 group-hover/item:text-white" />
                            </div>
                            <div>
                                @if(filled($office['address_label'] ?? null))<h4 class="text-[15px] font-semibold text-white tracking-wide">{{ $office['address_label'] }}</h4>@endif
                                <p class="{{ filled($office['address_label'] ?? null) ? 'mt-1.5' : '' }} text-[14px] text-white/70 leading-relaxed">{{ $address }}</p>
                            </div>
                        </div>@endif
                        @if(filled($officeHours))<div class="flex gap-5 group/item">
                            <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/5 border border-white/10 transition-colors duration-300 group-hover/item:bg-[var(--color-accent)] group-hover/item:border-[var(--color-accent)]">
                                <x-icon name="clock" class="size-[18px] text-[var(--color-accent)] transition-colors duration-300 group-hover/item:text-white" />
                            </div>
                            <div>
                                @if(filled($office['hours_label'] ?? null))<h4 class="text-[15px] font-semibold text-white tracking-wide">{{ $office['hours_label'] }}</h4>@endif
                                <p class="{{ filled($office['hours_label'] ?? null) ? 'mt-1.5' : '' }} text-[14px] text-white/70 leading-relaxed">{!! nl2br(e($officeHours)) !!}</p>
                            </div>
                        </div>@endif
                        @if(filled($direct['title'] ?? null) || filled($direct['intro'] ?? null) || filled($phone) || filled($email))
                        <div class="flex gap-5 group/item">
                            <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/5 border border-white/10 transition-colors duration-300 group-hover/item:bg-[var(--color-accent)] group-hover/item:border-[var(--color-accent)]">
                                <x-icon name="phone" class="size-[18px] text-[var(--color-accent)] transition-colors duration-300 group-hover/item:text-white" />
                            </div>
                            <div>
                                @if(filled($direct['title'] ?? null))<h4 class="text-[15px] font-semibold text-white tracking-wide">{{ $direct['title'] }}</h4>@endif
                                @if(filled($direct['intro'] ?? null))
                                    <p class="mt-1.5 text-[14px] text-white/70 leading-relaxed">{{ $direct['intro'] }}</p>
                                @endif
                                @if(filled($phone))<a href="{{ \App\Support\PhoneNumber::telHref($phone) }}" class="mt-1.5 block text-[14px] text-white/70 hover:text-white transition-colors">{{ $phone }}</a>@endif
                                @if(filled($email))<a href="mailto:{{ $email }}" class="mt-0.5 block text-[14px] text-white/70 hover:text-white transition-colors">{{ $email }}</a>@endif
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($socialProfiles->isNotEmpty())<div class="mt-12 border-t border-white/10 pt-8 relative">
                        @if(filled($office['social_label'] ?? null))<h4 class="text-[12px] font-bold tracking-[0.15em] uppercase text-white/90 mb-5">{{ $office['social_label'] }}</h4>@endif
                        <div class="flex gap-3">
                            @foreach($socialProfiles as $profile)
                                <a href="{{ $profile['url'] }}" target="_blank" rel="noopener" class="flex h-11 w-11 items-center justify-center rounded-full bg-white/5 border border-white/10 transition-all duration-300 hover:bg-[var(--color-accent)] hover:border-[var(--color-accent)] hover:-translate-y-1">
                                    <span class="sr-only">{{ $profile['label'] ?: ucfirst($profile['platform'] ?? 'Social') }}</span>
                                    <x-icon :name="$profile['platform'] ?? 'link'" class="size-[18px] text-white" />
                                </a>
                            @endforeach
                        </div>
                    </div>@endif

                    {{-- Gold line art pattern at bottom right - gently animated --}}
                    <div class="absolute bottom-0 right-4 opacity-50 pointer-events-none transition-transform duration-[3000ms] ease-out group-hover:scale-105 group-hover:opacity-70 origin-bottom-right">
                        <svg width="120" height="200" viewBox="0 0 120 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="100" y="80" width="1" height="120" fill="var(--color-accent)"/>
                            <rect x="80" y="40" width="1" height="160" fill="var(--color-accent)"/>
                            <rect x="60" y="100" width="1" height="100" fill="var(--color-accent)"/>
                            <rect x="40" y="120" width="1" height="80" fill="var(--color-accent)"/>
                            <rect x="20" y="60" width="1" height="140" fill="var(--color-accent)"/>
                            <rect x="0" y="150" width="1" height="50" fill="var(--color-accent)"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Map Section --}}
    @if(isset($contactSections['map']) && (filled($address) || filled($configuredMapUrl)))
    <section class="uh-contact-map group relative h-[500px] w-full bg-[var(--color-dominant)] overflow-hidden">
        <iframe src="{{ $mapEmbedUrl }}" title="{{ __('Map showing :location', ['location' => $mapLocation]) }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="eager" referrerpolicy="no-referrer-when-downgrade" class="absolute inset-0 size-full object-cover transition-all duration-1000"></iframe>
    </section>
    @endif

    {{-- Bottom CTA Section --}}
    @if(isset($contactSections['cta']) && (filled($cta['title'] ?? null) || filled($cta['intro'] ?? null) || (filled($cta['primary_cta'] ?? null) && filled($cta['primary_url'] ?? null)) || (filled($cta['secondary_cta'] ?? null) && filled($cta['secondary_url'] ?? null))))
    <section class="uh-contact-cta bg-[var(--color-secondary)] py-24 lg:py-32 relative overflow-hidden group">
        {{-- Animated Gold line art pattern at background --}}
        <div class="absolute bottom-0 right-0 opacity-30 pointer-events-none transform transition-transform duration-[3000ms] ease-out group-hover:scale-105 group-hover:-translate-y-4">
            <svg width="400" height="250" viewBox="0 0 400 250" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="350" y="80" width="2" height="170" fill="var(--color-accent)"/>
                <rect x="300" y="30" width="2" height="220" fill="var(--color-accent)"/>
                <rect x="250" y="120" width="2" height="130" fill="var(--color-accent)"/>
                <rect x="200" y="0" width="2" height="250" fill="var(--color-accent)"/>
                <rect x="150" y="150" width="2" height="100" fill="var(--color-accent)"/>
                <rect x="100" y="90" width="2" height="160" fill="var(--color-accent)"/>
            </svg>
        </div>
        
        <div class="uh-container grid lg:grid-cols-[1fr_auto] gap-12 items-center relative z-10">
            <div class="max-w-2xl">
                @if(filled($cta['title'] ?? null))<h2 class="text-[2.25rem] sm:text-[3.25rem] leading-[1.05] font-display text-white text-balance">{{ $cta['title'] }}</h2>@endif
                @if(filled($cta['intro'] ?? null))<p class="mt-6 text-[17px] text-white/75 leading-relaxed">{{ $cta['intro'] }}</p>@endif
            </div>
            <div class="flex flex-col sm:flex-row gap-5">
                @if(filled($cta['primary_cta'] ?? null) && filled($cta['primary_url'] ?? null))<a href="{{ $cta['primary_url'] }}" class="group/btn inline-flex items-center justify-center rounded-xl bg-[var(--color-accent)] px-8 py-4.5 text-[15px] font-semibold text-white transition-all duration-300 hover:bg-[var(--color-accent)] hover:shadow-[0_8px_25px_rgba(166,132,86,0.3)] hover:-translate-y-1">
                    {{ $cta['primary_cta'] }} <x-icon name="arrow-right" class="size-4 ml-2 transition-transform duration-300 group-hover/btn:translate-x-1" />
                </a>
                @endif
                @if(filled($cta['secondary_cta'] ?? null) && filled($cta['secondary_url'] ?? null))<a href="{{ $cta['secondary_url'] }}" class="group/btn2 inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/5 backdrop-blur-sm px-8 py-4.5 text-[15px] font-semibold text-white transition-all duration-300 hover:bg-white/10 hover:border-white/30 hover:-translate-y-1">
                    {{ $cta['secondary_cta'] }}
                </a>@endif
            </div>
        </div>
    </section>
    @endif
</main>
