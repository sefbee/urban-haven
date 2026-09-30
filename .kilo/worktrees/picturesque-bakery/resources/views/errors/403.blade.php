@extends('layouts.app')

@section('title', __('Access denied'))

@section('content')
    <main id="main" class="uh-container-narrow flex flex-1 flex-col justify-center py-16 md:py-24">
        <p class="uh-eyebrow">403</p>
        <h1 class="uh-h1 mt-3">{{ __('You do not have access to this page') }}</h1>
        <p class="uh-lede mt-4">
            {{ __('Your account does not have permission for this action. If you believe it should, ask an Owner Admin to review your role.') }}
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a class="uh-btn-primary uh-btn-sm" href="{{ url('/') }}">{{ __('Go to the home page') }}</a>
            @auth
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.dashboard') }}">{{ __('Back to the staff desk') }}</a>
            @endauth
        </div>
    </main>
@endsection
