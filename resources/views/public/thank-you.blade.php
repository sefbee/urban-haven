@extends('layouts.public')

@section('content')
    <div class="uh-page-head">
        <div class="uh-container-narrow">
            <div class="uh-surface uh-confirm-card">
                @if($confirmation)
                    <span class="uh-confirm-icon"><x-icon name="check" class="size-6" /></span>
                    <h1 class="uh-h1 uh-page-title">{{ $confirmation['lead_type'] === 'visit_request' ? __('Visit request received') : __('Thank you, we have your enquiry') }}</h1>
                    <p class="uh-lede">{{ __($confirmation['message']) }}</p>
                    @if(filled($confirmation['subject'] ?? null))
                        <p class="uh-confirm-subject">
                            <span>{{ __('About') }}</span>
                            @if(filled($confirmation['subject_url'] ?? null))
                                <a href="{{ $confirmation['subject_url'] }}">{{ $confirmation['subject'] }}</a>
                            @else
                                <strong class="font-medium">{{ $confirmation['subject'] }}</strong>
                            @endif
                        </p>
                    @endif
                    <div class="uh-confirm-actions">
                        <a class="uh-btn-primary" href="{{ route('properties.index') }}">{{ __('Keep browsing') }}</a>
                        <a class="uh-btn-secondary" href="{{ route('home') }}">{{ __('Back to home') }}</a>
                    </div>
                @else
                    <span class="uh-confirm-icon"><x-icon name="info" class="size-6" /></span>
                    <h1 class="uh-h1 uh-page-title">{{ __('Nothing to confirm') }}</h1>
                    <p class="uh-lede">{{ __('This page confirms an enquiry right after you send it. If you meant to contact us, use the form on any property or the contact page.') }}</p>
                    <div class="uh-confirm-actions">
                        <x-ui.pill-link variant="light" :href="route('cms.show', 'contact')">{{ __('Contact us') }}</x-ui.pill-link>
                        <a class="uh-btn-secondary" href="{{ route('properties.index') }}">{{ __('Explore properties') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
