@extends('layouts.admin')
@section('title', 'Settings')

@php
    $analytics = array_filter([
        'Google Tag Manager' => config('urbanhaven.analytics.gtm_id'),
        'Google Analytics 4' => config('urbanhaven.analytics.ga4_id'),
        'Meta Pixel' => config('urbanhaven.analytics.meta_pixel_id'),
    ]);
@endphp

@section('content')
    <x-ui.page-header compact title="Settings"
                      description="Company identity, public contact details, and how enquiries are handled.">
        <x-slot:eyebrow>Company</x-slot:eyebrow>
    </x-ui.page-header>

    <x-ui.admin-related label="Also configure">
        @can('settings.update')
            <a href="{{ route('admin.listing-display') }}">Listing display</a>
        @endcan
        @can('reference.manage')
            <a href="{{ route('admin.areas.index') }}">Areas</a>
            <a href="{{ route('admin.property-types.index') }}">Property types</a>
            <a href="{{ route('admin.amenities.index') }}">Amenities</a>
        @endcan
    </x-ui.admin-related>

    <section class="uh-panel" aria-labelledby="email-heading">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 id="email-heading" class="uh-h4">Email delivery</h2>
                <p class="mt-1 text-sm {{ $mailConfigured ? 'text-[var(--color-muted)]' : 'font-semibold text-[var(--color-danger)]' }}">
                    {{ $mailConfigured ? 'Configured. Send a test to confirm the server can deliver.' : 'Not configured. Staff alerts and enquiry acknowledgements are not being sent.' }}
                </p>
            </div>
            <form method="POST" action="{{ route('admin.settings.test-email') }}">
                @csrf
                <button type="submit" class="uh-btn-outline uh-btn-sm" @disabled(! $mailConfigured)>Send a test email to me</button>
            </form>
        </div>
        @error('mail')<p class="uh-error mt-2">{{ $message }}</p>@enderror
        <p class="mt-3 text-xs text-[var(--color-muted)]">
            Analytics: {{ $analytics !== [] ? implode(', ', array_keys($analytics)).' configured, loaded only after visitors accept cookies.' : 'no tracking IDs configured. Set them in the server environment to enable measurement.' }}
        </p>
    </section>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="uh-admin-compose-main mt-6" x-data="uhForm" @submit="submit">
        @csrf
        @method('PUT')
        <div class="uh-admin-stack">
            @include('admin.settings._fields', ['groups' => ['branding', 'contact', 'leads']])
        </div>

        <div class="uh-admin-dock">
            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                <span>Save settings</span>
            </button>
        </div>
    </form>
@endsection
