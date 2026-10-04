@extends('layouts.app')

@section('title', __('Access denied'))

@section('content')
    <main id="main" class="uh-container-narrow uh-confirm flex flex-1 flex-col justify-center">
        <p class="uh-error-code" aria-hidden="true">403</p>
        <h1 class="uh-h1">{{ __('You do not have access to this page') }}</h1>
        <p class="uh-lede">
            {{ __('Your account does not have permission for this action. If you believe it should, ask an Owner Admin to review your role.') }}
        </p>
        <div class="uh-confirm-actions">
            <a class="uh-btn-primary uh-btn-sm" href="{{ url('/') }}">{{ __('Go to the home page') }}</a>
            @auth
                <a class="uh-btn-secondary uh-btn-sm" href="{{ route('admin.dashboard') }}">{{ __('Back to the staff desk') }}</a>
            @endauth
        </div>
    </main>
@endsection
