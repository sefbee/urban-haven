@extends('layouts.public')

@section('content')
    <div class="uh-container-narrow uh-section">
        @if($confirmation)
            <div class="uh-panel text-center">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-forest/10 text-forest">
                    <x-icon name="check-circle" class="size-7" />
                </span>
                <h1 class="uh-h2 mt-5">{{ $confirmation['lead_type'] === 'visit_request' ? __('Visit request received') : __('Thank you, we have your enquiry') }}</h1>
                <p class="uh-lede mx-auto mt-3 max-w-lg">{{ __($confirmation['message']) }}</p>
                @if(filled($confirmation['subject'] ?? null))
                    <p class="mt-4 text-sm text-[var(--color-muted)]">
                        {{ __('About') }}:
                        @if(filled($confirmation['subject_url'] ?? null))
                            <a class="font-semibold text-forest underline" href="{{ $confirmation['subject_url'] }}">{{ $confirmation['subject'] }}</a>
                        @else
                            <strong>{{ $confirmation['subject'] }}</strong>
                        @endif
                    </p>
                @endif
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a class="uh-btn-primary" href="{{ route('properties.index') }}">{{ __('Keep browsing') }}</a>
                    <a class="uh-btn-outline" href="{{ route('home') }}">{{ __('Back to home') }}</a>
                </div>
            </div>
        @else
            <div class="uh-panel text-center">
                <h1 class="uh-h2">{{ __('Nothing to confirm') }}</h1>
                <p class="uh-lede mx-auto mt-3 max-w-lg">{{ __('This page confirms an enquiry right after you send it. If you meant to contact us, use the form on any property or the contact page.') }}</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a class="uh-btn-primary" href="{{ route('cms.show', 'contact') }}">{{ __('Contact us') }}</a>
                    <a class="uh-btn-outline" href="{{ route('properties.index') }}">{{ __('Search properties') }}</a>
                </div>
            </div>
        @endif
    </div>
@endsection
