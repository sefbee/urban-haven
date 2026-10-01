@extends('layouts.public')

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container-narrow py-8 md:py-10">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Legal Services')],
            ]" />
            <h1 class="uh-h1 mt-3">{{ __('Legal Services') }}</h1>
            <p class="mt-2 text-sm text-[var(--color-muted)]">
                {{ __('Paperwork for Urban Haven sales, lettings and handover — not a public legal marketplace.') }}
            </p>
        </div>
    </div>

    <div class="uh-container-narrow uh-section-tight">
        <div class="uh-prose">
            <p>{{ __('Every home on this site is company inventory. When you buy or rent through Urban Haven, the same desk coordinates the documents with our counsel.') }}</p>
            <h2>{{ __('What we help with') }}</h2>
            <ul>
                <li>{{ __('Sale agreements and mutation follow-up for our listings') }}</li>
                <li>{{ __('Letting paperwork and handover notes') }}</li>
                <li>{{ __('Introductions to the firm that acts for Urban Haven Properties Ltd.') }}</li>
            </ul>
            <p>{{ __('We do not give independent legal advice to the public, and we do not list third-party lawyers for hire.') }}</p>
        </div>

        <div class="uh-panel mt-10">
            <p class="uh-eyebrow">{{ __('Contact Urban Haven Agent') }}</p>
            <p class="mt-2 text-sm text-[var(--color-muted)]">{{ __('Ask the sales desk to outline the steps for a specific listing.') }}</p>
            <div class="mt-5 flex flex-wrap gap-3">
                @if(filled($contactPhone))
                    <a class="uh-btn-primary uh-btn-sm" href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}">
                        <x-icon name="phone" class="size-4" />
                        {{ __('Call Now') }}
                    </a>
                @endif
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('cms.show', 'contact') }}">{{ __('Inquire Now') }}</a>
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Browse homes') }}</a>
            </div>
        </div>
    </div>
@endsection
