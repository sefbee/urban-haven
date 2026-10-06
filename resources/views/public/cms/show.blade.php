@extends('layouts.public')

@php
    $template = $page->slug === 'contact' ? 'contact' : ($page->template ?: 'default');
    $contactBlock = $template === 'contact' ? (\App\Models\CmsBlock::contentFor('contact_details') ?? []) : [];
    $phone = \App\Models\Setting::get('phone') ?: ($contactBlock['phone'] ?? null);
    $email = \App\Models\Setting::get('email') ?: ($contactBlock['email'] ?? null);
    $address = \App\Models\Setting::get('address') ?: ($contactBlock['address'] ?? null);
    $officeHours = \App\Models\Setting::get('office_hours');
    $whatsappHref = \App\Support\PhoneNumber::whatsappHref(\App\Models\Setting::get('whatsapp'));
    $hasForm = in_array($template, ['contact', 'campaign'], true);
@endphp

@section('content')
    @if($isPreview ?? false)
        <div class="bg-warn/15 py-2 text-center text-xs font-semibold text-ink" role="status">
            {{ __('Preview — this shows unpublished changes and is not visible to the public.') }}
        </div>
    @endif

    <header class="uh-page-head">
        <div @class(['uh-container' => $hasForm, 'uh-container-narrow' => ! $hasForm])>
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => $page->title],
            ]" />
            <h1 class="uh-h1 uh-page-title">{{ $template === 'contact' ? __('Talk to our sales team') : $page->title }}</h1>
            @if($template === 'contact')
                <p class="uh-page-lede">{{ __('Call, WhatsApp or send a message. The people who manage our homes will get back to you.') }}</p>
                <div class="uh-contact-actions">
                    @if(filled($phone))
                        <a class="uh-btn-primary" href="{{ \App\Support\PhoneNumber::telHref($phone) }}" data-track="phone_click" data-track-location="contact_page_head">
                            <x-icon name="phone" class="size-4" />
                            {{ __('Call now') }}
                        </a>
                    @endif
                    @if($whatsappHref)
                        <a class="uh-btn-whatsapp" href="{{ $whatsappHref }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="contact_page_head">
                            <x-icon name="whatsapp" class="size-4" />
                            {{ __('Chat on WhatsApp') }}
                        </a>
                    @endif
                    <a class="uh-btn-secondary" href="#page-form-heading">
                        <x-icon name="mail" class="size-4" />
                        {{ __('Send a message') }}
                    </a>
                </div>
            @endif
        </div>
    </header>

    @if($hasForm)
        <div class="uh-container uh-detail-grid">
            <div class="min-w-0">
                <div class="uh-prose uh-prose-lead max-w-[64ch]">{!! \Stevebauman\Purify\Facades\Purify::clean((string) $page->body) !!}</div>

                @if($template === 'contact' && (filled($phone) || filled($email) || $whatsappHref || filled($address)))
                    <ul class="uh-contact-cards">
                        @if(filled($phone))
                            <li>
                                <a class="uh-contact-card" href="{{ \App\Support\PhoneNumber::telHref($phone) }}" data-track="phone_click" data-track-location="contact_page">
                                    <span class="uh-contact-card-icon"><x-icon name="phone" class="size-5" /></span>
                                    <span class="min-w-0">
                                        <span class="uh-contact-card-label">{{ __('Phone') }}</span>
                                        <span class="uh-contact-card-value uh-numeric" dir="ltr">{{ $phone }}</span>
                                    </span>
                                </a>
                            </li>
                        @endif
                        @if($whatsappHref)
                            <li>
                                <a class="uh-contact-card" href="{{ $whatsappHref }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="contact_page">
                                    <span class="uh-contact-card-icon"><x-icon name="whatsapp" class="size-5" /></span>
                                    <span class="min-w-0">
                                        <span class="uh-contact-card-label">{{ __('WhatsApp') }}</span>
                                        <span class="uh-contact-card-value">{{ __('Message us') }}</span>
                                    </span>
                                </a>
                            </li>
                        @endif
                        @if(filled($email))
                            <li>
                                <a class="uh-contact-card" href="mailto:{{ $email }}">
                                    <span class="uh-contact-card-icon"><x-icon name="mail" class="size-5" /></span>
                                    <span class="min-w-0">
                                        <span class="uh-contact-card-label">{{ __('Email') }}</span>
                                        <span class="uh-contact-card-value">{{ $email }}</span>
                                    </span>
                                </a>
                            </li>
                        @endif
                        @if(filled($address))
                            <li>
                                <div class="uh-contact-card">
                                    <span class="uh-contact-card-icon"><x-icon name="pin" class="size-5" /></span>
                                    <span class="min-w-0">
                                        <span class="uh-contact-card-label">{{ __('Office') }}</span>
                                        <span class="uh-contact-card-value">{{ $address }}</span>
                                        @if(filled($officeHours))
                                            <span class="uh-contact-card-note">{{ $officeHours }}</span>
                                        @endif
                                    </span>
                                </div>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>

            <aside class="min-w-0 min-[1100px]:sticky min-[1100px]:top-[calc(var(--uh-bar-h)+1.5rem)]" aria-labelledby="page-form-heading">
                <div class="uh-convert uh-surface">
                    <h2 id="page-form-heading" class="uh-convert-title">{{ $template === 'campaign' ? __('Register your interest') : __('Send us a message') }}</h2>
                    <p class="uh-convert-who">{{ __('Our sales team replies during office hours.') }}</p>
                    <div class="uh-convert-body">
                        @include('public.partials.lead-form', [
                            'leadType' => $template === 'campaign' ? 'campaign' : 'general_contact',
                            'source' => $template === 'campaign' ? 'campaign:'.$page->slug : 'contact_page',
                            'prefix' => 'page',
                            'messageLabel' => __('How can we help?'),
                            'messagePlaceholder' => __('What are you looking for, budget, preferred area…'),
                        ])
                    </div>
                </div>
            </aside>
        </div>
    @else
        <div class="uh-container-narrow uh-section-tight">
            <div class="uh-prose uh-prose-lead">{!! \Stevebauman\Purify\Facades\Purify::clean((string) $page->body) !!}</div>

            <aside class="uh-next">
                <p class="uh-h3">{{ __('Ready for the next step?') }}</p>
                <p>{{ __('Browse the homes we have available, or ask our team anything.') }}</p>
                <div class="uh-next-links">
                    <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Explore properties') }}</a>
                    @if($page->slug !== 'contact')
                        <a class="uh-arrow-link" href="{{ route('cms.show', 'contact') }}">{{ __('Contact us') }} <x-icon name="arrow-right" class="size-3.5" /></a>
                    @endif
                </div>
            </aside>
        </div>
    @endif
@endsection
