@extends('layouts.public')

@section('content')
    @include('public.partials.page-head', [
        'wide' => false,
        'title' => __('Legal Services'),
        'lede' => __('Paperwork for Urban Haven sales, lettings and handover — not a public legal marketplace.'),
    ])

    <div class="uh-container-narrow uh-section-tight">
        <p class="uh-prose uh-prose-lead">{{ __('Every home on this site is company inventory. When you buy or rent through Urban Haven, the same desk coordinates the documents with our counsel.') }}</p>

        <div class="uh-surface uh-tool-card mt-10">
            <h2 class="uh-pd-card-title">{{ __('What we help with') }}</h2>
            <ul class="uh-check-list mt-5">
                <li><x-icon name="check-circle" class="size-5" />{{ __('Sale agreements and mutation follow-up for our listings') }}</li>
                <li><x-icon name="check-circle" class="size-5" />{{ __('Letting paperwork and handover notes') }}</li>
                <li><x-icon name="check-circle" class="size-5" />{{ __('Introductions to the firm that acts for Urban Haven Properties Ltd.') }}</li>
            </ul>
            <p class="mt-6 border-t border-[var(--uh-line)] pt-5 text-[0.9375rem] leading-relaxed text-[var(--uh-muted)]">{{ __('We do not give independent legal advice to the public, and we do not list third-party lawyers for hire.') }}</p>
        </div>

        <aside class="uh-next">
            <p class="uh-h3">{{ __('Contact Urban Haven Agent') }}</p>
            <p>{{ __('Ask the sales desk to outline the steps for a specific listing.') }}</p>
            <div class="uh-next-links">
                @if(filled($contactPhone))
                    <a class="uh-btn-primary uh-btn-sm" href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}">
                        <x-icon name="phone" class="size-4" />
                        {{ __('Call Now') }}
                    </a>
                @endif
                <x-ui.pill-link variant="light" :href="route('cms.show', 'contact')">{{ __('Inquire Now') }}</x-ui.pill-link>
                <a class="uh-arrow-link" href="{{ route('properties.index') }}">{{ __('Browse homes') }} <x-icon name="arrow-right" class="size-3.5" /></a>
            </div>
        </aside>
    </div>
@endsection
