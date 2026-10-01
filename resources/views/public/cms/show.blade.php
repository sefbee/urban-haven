@extends('layouts.public')

@php
    $template = $page->template ?: ($page->slug === 'contact' ? 'contact' : 'default');
    $phone = \App\Models\Setting::get('phone');
    $email = \App\Models\Setting::get('email');
    $address = \App\Models\Setting::get('address');
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

    <div class="border-b border-line bg-paper">
        <div @class(['py-8 md:py-10', 'uh-container' => $hasForm, 'uh-container-narrow' => ! $hasForm])>
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => $page->title],
            ]" />
            <h1 class="uh-h1 mt-3">{{ $page->title }}</h1>
        </div>
    </div>

    @if($hasForm)
        <div class="uh-container uh-section-tight grid gap-10 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-start">
            <div class="min-w-0">
                <div class="uh-prose">{!! \Stevebauman\Purify\Facades\Purify::clean((string) $page->body) !!}</div>

                @if($template === 'contact' && (filled($phone) || filled($email) || $whatsappHref || filled($address)))
                    <ul class="mt-8 grid gap-3 text-sm sm:grid-cols-2">
                        @if(filled($phone))
                            <li>
                                <a class="uh-panel flex items-center gap-3" href="{{ \App\Support\PhoneNumber::telHref($phone) }}" data-track="phone_click" data-track-location="contact_page">
                                    <x-icon name="phone" class="size-5 text-[var(--color-gold-ink)]" />
                                    <span dir="ltr" class="uh-numeric font-semibold">{{ $phone }}</span>
                                </a>
                            </li>
                        @endif
                        @if($whatsappHref)
                            <li>
                                <a class="uh-panel flex items-center gap-3" href="{{ $whatsappHref }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="contact_page">
                                    <x-icon name="whatsapp" class="size-5 text-[var(--color-gold-ink)]" />
                                    <span class="font-semibold">{{ __('WhatsApp') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(filled($email))
                            <li>
                                <a class="uh-panel flex items-center gap-3 break-all" href="mailto:{{ $email }}">
                                    <x-icon name="mail" class="size-5 text-[var(--color-gold-ink)]" />
                                    <span class="font-semibold">{{ $email }}</span>
                                </a>
                            </li>
                        @endif
                        @if(filled($address))
                            <li class="uh-panel flex items-start gap-3">
                                <x-icon name="pin" class="mt-0.5 size-5 shrink-0 text-[var(--color-gold-ink)]" />
                                <span>
                                    <span class="block font-semibold">{{ $address }}</span>
                                    @if(filled($officeHours))
                                        <span class="mt-1 block text-xs text-[var(--color-muted)]">{{ $officeHours }}</span>
                                    @endif
                                </span>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>

            <aside class="uh-panel" aria-labelledby="page-form-heading">
                <h2 id="page-form-heading" class="uh-h3">{{ $template === 'campaign' ? __('Register your interest') : __('Send us a message') }}</h2>
                <p class="mt-1 text-sm text-[var(--color-muted)]">{{ __('Our sales team replies during office hours.') }}</p>
                <div class="mt-5">
                    @include('public.partials.lead-form', [
                        'leadType' => $template === 'campaign' ? 'campaign' : 'general_contact',
                        'source' => $template === 'campaign' ? 'campaign:'.$page->slug : 'contact_page',
                        'prefix' => 'page',
                        'messageLabel' => __('How can we help?'),
                        'messagePlaceholder' => __('What are you looking for, budget, preferred area…'),
                    ])
                </div>
            </aside>
        </div>
    @else
        <div class="uh-container-narrow uh-section-tight">
            <div class="uh-prose">{!! \Stevebauman\Purify\Facades\Purify::clean((string) $page->body) !!}</div>

            <div class="mt-12 border-t border-line pt-7">
                <p class="uh-eyebrow">{{ __('Next step') }}</p>
                <div class="mt-3 flex flex-wrap gap-3">
                    <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Search properties') }}</a>
                    <a class="uh-btn-outline uh-btn-sm" href="{{ route('cms.show', 'contact') }}">{{ __('Contact us') }}</a>
                </div>
            </div>
        </div>
    @endif
@endsection
