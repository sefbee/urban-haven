@php
    $contactSections = $contactSections ?? \App\Support\ContactPageSections::visible();
    $socialProfiles = collect((array) \App\Models\Setting::get('social_links', []))
        ->filter(fn ($profile): bool => is_array($profile) && ($profile['active'] ?? true) && filled($profile['url'] ?? null))
        ->take(4);
    $configuredMapUrl = \App\Models\Setting::get('map_url');
    $mapUrl = filled($configuredMapUrl)
        ? $configuredMapUrl
        : (filled($address) ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($address) : null);
    $contactImage = $page->galleryImages()->first();
@endphp

<main class="bg-white pb-0">
    {{-- Hero Section --}}
    <div class="group flex flex-col lg:flex-row min-h-[550px] 2xl:min-h-[650px] overflow-hidden">
        <div class="w-full lg:w-1/2 bg-[var(--color-dominant)] flex items-center justify-center p-8 sm:p-12 lg:p-16 xl:p-24 relative z-10">
            {{-- Subtle background decoration --}}
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none opacity-20">
                <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-gradient-to-br from-[var(--color-accent)]/20 to-transparent blur-3xl"></div>
            </div>
            
            <div class="w-full max-w-xl relative">
                <div class="inline-flex items-center gap-3 mb-5">
                    <span class="w-8 h-px bg-[var(--color-accent)]"></span>
                    <p class="text-[11px] font-bold tracking-[0.2em] text-[var(--color-accent)] uppercase">{{ __('Let\'s connect') }}</p>
                </div>
                <h1 class="text-[2.75rem] leading-[1.05] sm:text-6xl lg:text-[4rem] font-display text-[var(--color-body-text)] text-balance transition-all duration-700">
                    {{ $page->title ?: __('Let’s Find the Right Property for You.') }}
                </h1>
                <p class="mt-6 text-[15px] sm:text-base leading-relaxed text-[var(--color-muted-text)] max-w-[480px]">
                    {{ filled(trim(strip_tags((string) $page->body)))
                        ? trim(strip_tags((string) $page->body))
                        : __('Whether you\'re looking to buy, sell or invest, our property specialists are ready to guide you.') }}
                </p>

            </div>
        </div>
        <div class="w-full lg:w-1/2 h-[400px] lg:h-auto relative overflow-hidden">
            <div class="absolute inset-0 bg-[var(--color-secondary)]/10 mix-blend-multiply z-10 transition-opacity duration-700 group-hover:opacity-0"></div>
            <img src="{{ $contactImage?->url(1920) ?? 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=2200&q=85' }}"
                 alt="{{ $contactImage?->alt(app()->getLocale()) ?: __('Contemporary home surrounded by greenery') }}"
                 class="absolute inset-0 size-full object-cover object-center transition-transform duration-[2000ms] ease-out group-hover:scale-105">
        </div>
    </div>

    {{-- How Can We Help Section --}}
    <section class="py-24 px-4 sm:px-6 lg:px-8 bg-white border-b border-[#f3f0ea]/50 relative overflow-hidden">
        <div class="max-w-7xl mx-auto relative z-10">
            <div class="text-center max-w-2xl mx-auto">
                <x-icon name="sparkles" class="size-6 text-[var(--color-accent)] mx-auto mb-4 opacity-50" />
                <h2 class="text-3xl sm:text-4.5xl font-display text-[var(--color-body-text)]">{{ __('How Can We Help?') }}</h2>
                <p class="mt-4 text-[16px] text-[var(--color-muted-text)]">{{ __('Choose the most convenient way to connect with our team of property specialists.') }}</p>
            </div>
            
            <div class="mt-16 grid gap-6 md:grid-cols-3 max-w-[1050px] mx-auto">
                {{-- Call Us --}}
                <div class="group rounded-2xl border border-[#eaeaea] bg-white p-8 xl:p-10 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)] transition-all duration-500 hover:shadow-xl hover:-translate-y-1 text-center flex flex-col items-center">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[var(--color-dominant)] text-[var(--color-accent)] border border-[#eaeaea] transition-all duration-500 group-hover:bg-[var(--color-accent)] group-hover:text-white group-hover:border-[var(--color-accent)] shadow-sm">
                        <x-icon name="phone" class="size-5 transition-transform duration-500 group-hover:scale-110" />
                    </div>
                    <h3 class="mt-6 font-display text-[22px] text-[var(--color-body-text)] transition-colors group-hover:text-[var(--color-accent)]">{{ __('Call Us') }}</h3>
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone ?? '') }}" class="mt-2 inline-block text-[15px] font-semibold text-[var(--color-body-text)] hover:text-[var(--color-accent)] transition-colors">{{ $phone ?? '+880 1XXX-XXXXXX' }}</a>
                    <p class="mt-3 text-[14px] text-[var(--color-muted-text)] leading-relaxed max-w-[220px]">{{ __('Speak directly with a property consultant.') }}</p>
                </div>
                
                {{-- Email Us (elevated) --}}
                <div class="group rounded-2xl border border-[#eaeaea] border-t-[3px] border-t-[var(--color-accent)] bg-white p-8 xl:p-10 shadow-[0_12px_40px_rgb(0,0,0,0.08)] transition-all duration-500 hover:shadow-[0_20px_50px_rgb(0,0,0,0.12)] hover:-translate-y-4 relative transform md:-translate-y-2 text-center flex flex-col items-center z-10">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[var(--color-dominant)] text-[var(--color-accent)] border border-[#eaeaea] transition-all duration-500 group-hover:bg-[var(--color-accent)] group-hover:text-white group-hover:border-[var(--color-accent)] shadow-sm">
                        <x-icon name="mail" class="size-5 transition-transform duration-500 group-hover:scale-110" />
                    </div>
                    <h3 class="mt-6 font-display text-[22px] text-[var(--color-body-text)] transition-colors group-hover:text-[var(--color-accent)]">{{ __('Email Us') }}</h3>
                    <a href="mailto:{{ $email ?? '' }}" class="mt-2 inline-block text-[15px] font-semibold text-[var(--color-body-text)] hover:text-[var(--color-accent)] transition-colors">{{ $email ?? 'info@urbanhavenbd.com' }}</a>
                    <p class="mt-3 text-[14px] text-[var(--color-muted-text)] leading-relaxed max-w-[220px]">{{ __('Send your questions and requirements.') }}</p>
                </div>
                
                {{-- Visit Our Office --}}
                <div class="group rounded-2xl border border-[#eaeaea] bg-white p-8 xl:p-10 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)] transition-all duration-500 hover:shadow-xl hover:-translate-y-1 text-center flex flex-col items-center">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[var(--color-dominant)] text-[var(--color-accent)] border border-[#eaeaea] transition-all duration-500 group-hover:bg-[var(--color-accent)] group-hover:text-white group-hover:border-[var(--color-accent)] shadow-sm">
                        <x-icon name="pin" class="size-5 transition-transform duration-500 group-hover:scale-110" />
                    </div>
                    <h3 class="mt-6 font-display text-[22px] text-[var(--color-body-text)] transition-colors group-hover:text-[var(--color-accent)]">{{ __('Visit Our Office') }}</h3>
                    <p class="mt-2 text-[15px] font-semibold text-[var(--color-body-text)]">{{ $address ?? 'Office Address, Dhaka, Bangladesh' }}</p>
                    <p class="mt-3 text-[14px] text-[var(--color-muted-text)] leading-relaxed max-w-[220px]">{{ __('Meet our team for a personal consultation.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Form & Sidebar Section --}}
    <section class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 bg-white" id="page-form-heading">
        <div class="grid gap-12 lg:grid-cols-[1fr_400px] xl:grid-cols-[1fr_450px] lg:gap-16 xl:gap-24">
            
            {{-- Form --}}
            <div class="pt-2">
                <div class="inline-flex items-center gap-3 mb-4">
                    <span class="w-6 h-px bg-[var(--color-accent)]"></span>
                    <p class="text-[11px] font-bold tracking-[0.2em] text-[var(--color-accent)] uppercase">{{ __('Send an enquiry') }}</p>
                </div>
                <h2 class="text-[2rem] sm:text-[2.75rem] leading-[1.1] font-display text-[var(--color-body-text)]">{{ __('Tell Us What You\'re Looking For') }}</h2>
                <p class="mt-5 text-[16px] text-[var(--color-muted-text)] leading-relaxed max-w-[500px]">
                    {{ __('Share a few details and our team will contact you with relevant property options.') }}
                </p>
                
                <form class="mt-12 grid gap-x-8 gap-y-7 sm:grid-cols-2">
                    <div class="group relative">
                        <label class="block text-[13px] font-semibold text-[var(--color-body-text)] mb-2 transition-colors group-focus-within:text-[var(--color-accent)]">{{ __('Full Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" placeholder="{{ __('Enter your full name') }}" class="w-full rounded-lg bg-[var(--color-dominant)]/50 border border-[#e4ddd3] px-4 py-3.5 text-[14px] text-[var(--color-body-text)] placeholder:text-[#a09a90] focus:bg-white focus:border-[var(--color-accent)] focus:ring-4 focus:ring-[var(--color-accent)]/10 outline-none transition-all duration-300">
                    </div>
                    <div class="group relative">
                        <label class="block text-[13px] font-semibold text-[var(--color-body-text)] mb-2 transition-colors group-focus-within:text-[var(--color-accent)]">{{ __('Phone Number') }} <span class="text-red-500">*</span></label>
                        <input type="tel" placeholder="{{ __('+880 1XXX-XXXXXX') }}" class="w-full rounded-lg bg-[var(--color-dominant)]/50 border border-[#e4ddd3] px-4 py-3.5 text-[14px] text-[var(--color-body-text)] placeholder:text-[#a09a90] focus:bg-white focus:border-[var(--color-accent)] focus:ring-4 focus:ring-[var(--color-accent)]/10 outline-none transition-all duration-300">
                    </div>
                    <div class="group relative">
                        <label class="block text-[13px] font-semibold text-[var(--color-body-text)] mb-2 transition-colors group-focus-within:text-[var(--color-accent)]">{{ __('Email Address') }}</label>
                        <input type="email" placeholder="{{ __('you@example.com') }}" class="w-full rounded-lg bg-[var(--color-dominant)]/50 border border-[#e4ddd3] px-4 py-3.5 text-[14px] text-[var(--color-body-text)] placeholder:text-[#a09a90] focus:bg-white focus:border-[var(--color-accent)] focus:ring-4 focus:ring-[var(--color-accent)]/10 outline-none transition-all duration-300">
                    </div>
                    <div class="group relative">
                        <label class="block text-[13px] font-semibold text-[var(--color-body-text)] mb-2 transition-colors group-focus-within:text-[var(--color-accent)]">{{ __('I\'m Interested In') }} <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select data-uh-select class="w-full rounded-lg bg-[var(--color-dominant)]/50 border border-[#e4ddd3] px-4 py-3.5 text-[14px] text-[#7a746b] focus:bg-white focus:border-[var(--color-accent)] focus:ring-4 focus:ring-[var(--color-accent)]/10 outline-none transition-all duration-300 appearance-none pr-10">
                                <option value="">{{ __('Select an option') }}</option>
                                <option value="buy">{{ __('Buying a property') }}</option>
                                <option value="rent">{{ __('Renting a property') }}</option>
                                <option value="sell">{{ __('Selling my property') }}</option>
                            </select>
                            <x-icon name="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 size-4 text-[#a09a90] pointer-events-none transition-transform group-focus-within:rotate-180 group-focus-within:text-[var(--color-accent)]" />
                        </div>
                    </div>
                    <div class="group relative">
                        <label class="block text-[13px] font-semibold text-[var(--color-body-text)] mb-2 transition-colors group-focus-within:text-[var(--color-accent)]">{{ __('Preferred Location') }}</label>
                        <input type="text" placeholder="{{ __('e.g. Dhaka, Gulshan, Banani') }}" class="w-full rounded-lg bg-[var(--color-dominant)]/50 border border-[#e4ddd3] px-4 py-3.5 text-[14px] text-[var(--color-body-text)] placeholder:text-[#a09a90] focus:bg-white focus:border-[var(--color-accent)] focus:ring-4 focus:ring-[var(--color-accent)]/10 outline-none transition-all duration-300">
                    </div>
                    <div class="group relative">
                        <label class="block text-[13px] font-semibold text-[var(--color-body-text)] mb-2 transition-colors group-focus-within:text-[var(--color-accent)]">{{ __('Budget Range') }}</label>
                        <div class="relative">
                            <select data-uh-select class="w-full rounded-lg bg-[var(--color-dominant)]/50 border border-[#e4ddd3] px-4 py-3.5 text-[14px] text-[#7a746b] focus:bg-white focus:border-[var(--color-accent)] focus:ring-4 focus:ring-[var(--color-accent)]/10 outline-none transition-all duration-300 appearance-none pr-10">
                                <option value="">{{ __('Select an option') }}</option>
                                <option value="1">{{ __('Below BDT 1 Crore') }}</option>
                                <option value="2">{{ __('BDT 1 - 3 Crore') }}</option>
                                <option value="3">{{ __('Above BDT 3 Crore') }}</option>
                            </select>
                            <x-icon name="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 size-4 text-[#a09a90] pointer-events-none transition-transform group-focus-within:rotate-180 group-focus-within:text-[var(--color-accent)]" />
                        </div>
                    </div>
                    <div class="sm:col-span-2 group relative">
                        <label class="block text-[13px] font-semibold text-[var(--color-body-text)] mb-2 transition-colors group-focus-within:text-[var(--color-accent)]">{{ __('Message') }}</label>
                        <textarea rows="4" placeholder="{{ __('Tell us about your requirements, preferred property type, budget, or any specific details...') }}" class="w-full rounded-lg bg-[var(--color-dominant)]/50 border border-[#e4ddd3] px-4 py-4 text-[14px] text-[var(--color-body-text)] placeholder:text-[#a09a90] focus:bg-white focus:border-[var(--color-accent)] focus:ring-4 focus:ring-[var(--color-accent)]/10 outline-none resize-none transition-all duration-300"></textarea>
                    </div>
                    <div class="sm:col-span-2 flex items-start gap-3 mt-2">
                        <input type="checkbox" id="privacy-consent" class="mt-0.5 h-4.5 w-4.5 shrink-0 rounded border-[#d3cbc0] text-[var(--color-accent)] focus:ring-[var(--color-accent)] transition-colors cursor-pointer">
                        <label for="privacy-consent" class="text-[13px] text-[var(--color-muted-text)] cursor-pointer select-none">{{ __('I agree to the') }} <a href="#" class="text-[var(--color-accent)] font-medium underline decoration-[var(--color-accent)]/30 underline-offset-4 hover:decoration-[var(--color-accent)] transition-colors">{{ __('privacy policy') }}</a>.</label>
                    </div>
                    <div class="sm:col-span-2 mt-6">
                        <button type="button" class="group inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--color-secondary)] px-9 py-4 text-[15px] font-semibold text-white transition-all duration-300 hover:bg-[#1f2623] hover:shadow-[0_8px_20px_rgba(13,17,16,0.15)] hover:-translate-y-0.5 w-full sm:w-auto">
                            {{ __('Send Enquiry') }} 
                            <x-icon name="arrow-right" class="size-4 transition-transform duration-300 group-hover:translate-x-1" />
                        </button>
                    </div>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="bg-[var(--color-secondary)] flex flex-col rounded-3xl overflow-hidden shadow-[0_20px_60px_rgba(13,17,16,0.2)] group relative border border-white/5">
                {{-- Decorative gradient background --}}
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-[var(--color-accent)]/10 via-night to-night pointer-events-none"></div>

                <div class="h-[260px] w-full relative overflow-hidden">
                    <div class="absolute inset-0 bg-[var(--color-secondary)]/20 mix-blend-multiply z-10"></div>
                    <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=85" alt="Office Interior" class="absolute inset-0 size-full object-cover transition-transform duration-[2000ms] ease-out group-hover:scale-110">
                </div>
                <div class="p-8 sm:p-10 flex-1 flex flex-col relative text-white z-10">
                    <h3 class="font-display text-[32px] text-white">{{ __('Visit Urban Haven') }}</h3>
                    
                    <div class="mt-10 space-y-8 flex-1">
                        <div class="flex gap-5 group/item">
                            <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/5 border border-white/10 transition-colors duration-300 group-hover/item:bg-[var(--color-accent)] group-hover/item:border-[var(--color-accent)]">
                                <x-icon name="pin" class="size-[18px] text-[var(--color-accent)] transition-colors duration-300 group-hover/item:text-white" />
                            </div>
                            <div>
                                <h4 class="text-[15px] font-semibold text-white tracking-wide">{{ __('Office Address') }}</h4>
                                <p class="mt-1.5 text-[14px] text-white/70 leading-relaxed">{{ $address ?? 'Dhaka, Bangladesh' }}</p>
                            </div>
                        </div>
                        <div class="flex gap-5 group/item">
                            <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/5 border border-white/10 transition-colors duration-300 group-hover/item:bg-[var(--color-accent)] group-hover/item:border-[var(--color-accent)]">
                                <x-icon name="clock" class="size-[18px] text-[var(--color-accent)] transition-colors duration-300 group-hover/item:text-white" />
                            </div>
                            <div>
                                <h4 class="text-[15px] font-semibold text-white tracking-wide">{{ __('Business Hours') }}</h4>
                                <p class="mt-1.5 text-[14px] text-white/70 leading-relaxed">{!! nl2br(e($officeHours ?? "Saturday - Thursday\n9:00 AM - 6:00 PM")) !!}</p>
                            </div>
                        </div>
                        <div class="flex gap-5 group/item">
                            <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/5 border border-white/10 transition-colors duration-300 group-hover/item:bg-[var(--color-accent)] group-hover/item:border-[var(--color-accent)]">
                                <x-icon name="phone" class="size-[18px] text-[var(--color-accent)] transition-colors duration-300 group-hover/item:text-white" />
                            </div>
                            <div>
                                <h4 class="text-[15px] font-semibold text-white tracking-wide">{{ $contactSections['direct']['title'] ?? __('Direct Contact') }}</h4>
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone ?? '') }}" class="mt-1.5 block text-[14px] text-white/70 hover:text-white transition-colors">{{ $phone ?? '+880 1XXX-XXXXXX' }}</a>
                                <a href="mailto:{{ $email ?? '' }}" class="mt-0.5 block text-[14px] text-white/70 hover:text-white transition-colors">{{ $email ?? 'info@urbanhavenbd.com' }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 border-t border-white/10 pt-8 relative">
                        <h4 class="text-[12px] font-bold tracking-[0.15em] uppercase text-white/90 mb-5">{{ __('Follow Us') }}</h4>
                        @if($socialProfiles->isNotEmpty())
                            <div class="flex gap-3">
                                @foreach($socialProfiles as $profile)
                                    <a href="{{ $profile['url'] }}" target="_blank" rel="noopener" class="flex h-11 w-11 items-center justify-center rounded-full bg-white/5 border border-white/10 transition-all duration-300 hover:bg-[var(--color-accent)] hover:border-[var(--color-accent)] hover:-translate-y-1">
                                        <span class="sr-only">{{ $profile['label'] ?: ucfirst($profile['platform'] ?? 'Social') }}</span>
                                        <x-icon :name="$profile['platform'] ?? 'link'" class="size-[18px] text-white" />
                                    </a>
                                @endforeach
                            </div>
                        @else
                            {{-- Fallback social icons --}}
                            <div class="flex gap-3">
                                <a href="#" class="flex h-11 w-11 items-center justify-center rounded-full bg-white/5 border border-white/10 transition-all duration-300 hover:bg-[var(--color-accent)] hover:border-[var(--color-accent)] hover:-translate-y-1 hover:shadow-[0_4px_15px_rgba(166,132,86,0.4)]">
                                    <x-icon name="facebook" class="size-[18px] text-white" />
                                </a>
                                <a href="#" class="flex h-11 w-11 items-center justify-center rounded-full bg-white/5 border border-white/10 transition-all duration-300 hover:bg-[var(--color-accent)] hover:border-[var(--color-accent)] hover:-translate-y-1 hover:shadow-[0_4px_15px_rgba(166,132,86,0.4)]">
                                    <x-icon name="instagram" class="size-[18px] text-white" />
                                </a>
                                <a href="#" class="flex h-11 w-11 items-center justify-center rounded-full bg-white/5 border border-white/10 transition-all duration-300 hover:bg-[var(--color-accent)] hover:border-[var(--color-accent)] hover:-translate-y-1 hover:shadow-[0_4px_15px_rgba(166,132,86,0.4)]">
                                    <x-icon name="linkedin" class="size-[18px] text-white" />
                                </a>
                            </div>
                        @endif
                    </div>

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
    <section class="group relative h-[500px] w-full bg-[var(--color-dominant)] overflow-hidden">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14602.700312015096!2d90.39513346977234!3d23.794582087733475!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c70c15ea1de1%3A0x97856381e88fb311!2sBanani%2C%20Dhaka!5e0!3m2!1sen!2sbd!4v1700000000000!5m2!1sen!2sbd" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="absolute inset-0 size-full object-cover transition-all duration-1000 grayscale opacity-80 mix-blend-multiply group-hover:grayscale-0 group-hover:opacity-100 group-hover:mix-blend-normal"></iframe>
        
        <div class="absolute inset-0 pointer-events-none transition-colors duration-1000 group-hover:bg-[var(--color-secondary)]/5">
            <div class="max-w-7xl mx-auto h-full relative px-4 sm:px-6 lg:px-8">
                <div class="absolute right-4 sm:right-6 lg:right-8 top-1/2 -translate-y-1/2 w-full max-w-[360px] bg-white p-8 shadow-[0_20px_50px_rgba(0,0,0,0.1)] pointer-events-auto rounded-2xl transition-transform duration-700 ease-out group-hover:-translate-y-[calc(50%+8px)] border border-white/40 backdrop-blur-md">
                    <div class="w-12 h-12 bg-sand rounded-full flex items-center justify-center mb-5 text-[var(--color-accent)]">
                        <x-icon name="building-office" class="size-6" />
                    </div>
                    <h3 class="font-display text-[22px] text-[var(--color-body-text)]">{{ __('Urban Haven Properties') }}</h3>
                    <p class="mt-3 text-[15px] text-[var(--color-muted-text)] leading-relaxed">{{ $address ?? 'Office Address, Dhaka, Bangladesh' }}</p>
                    @if($mapUrl)
                        <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="group/btn mt-8 flex w-full items-center justify-center rounded-xl bg-[var(--color-accent)] px-6 py-4 text-[14px] font-semibold text-white transition-all duration-300 hover:bg-[var(--color-accent)] hover:shadow-[0_8px_20px_rgba(166,132,86,0.25)]">
                            {{ __('Get Directions') }} <x-icon name="arrow-right" class="size-4 ml-2 transition-transform duration-300 group-hover/btn:translate-x-1" />
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Bottom CTA Section --}}
    <section class="bg-[var(--color-secondary)] py-24 lg:py-32 relative overflow-hidden group">
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
        
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid lg:grid-cols-[1fr_auto] gap-12 items-center relative z-10">
            <div class="max-w-2xl">
                <h2 class="text-[2.25rem] sm:text-[3.25rem] leading-[1.05] font-display text-white text-balance">{{ __('Ready to Make Your Next Property Move?') }}</h2>
                <p class="mt-6 text-[17px] text-white/75 leading-relaxed">{{ __('Schedule a confidential consultation with our real-estate team and let us guide you home.') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-5">
                <a href="#page-form-heading" class="group/btn inline-flex items-center justify-center rounded-xl bg-[var(--color-accent)] px-8 py-4.5 text-[15px] font-semibold text-white transition-all duration-300 hover:bg-[var(--color-accent)] hover:shadow-[0_8px_25px_rgba(166,132,86,0.3)] hover:-translate-y-1">
                    {{ __('Book a Consultation') }} <x-icon name="arrow-right" class="size-4 ml-2 transition-transform duration-300 group-hover/btn:translate-x-1" />
                </a>
                <a href="{{ route('properties.index') }}" class="group/btn2 inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/5 backdrop-blur-sm px-8 py-4.5 text-[15px] font-semibold text-white transition-all duration-300 hover:bg-white/10 hover:border-white/30 hover:-translate-y-1">
                    {{ __('Browse Properties') }}
                </a>
            </div>
        </div>
    </section>
</main>
