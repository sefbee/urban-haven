@extends('layouts.app')

@section('title', __('Something went wrong'))

@section('content')
    <main id="main" class="uh-container-narrow uh-confirm flex flex-1 flex-col justify-center">
        <p class="uh-error-code" aria-hidden="true">500</p>
        <h1 class="uh-h1">{{ __('Something went wrong on our side') }}</h1>
        <p class="uh-lede">
            {{ __('This is our fault, not yours. The error has been logged for our team. Try again in a moment, and if it keeps happening call our office and we will help you directly.') }}
        </p>
        <div class="uh-confirm-actions">
            <button type="button" class="uh-btn-primary uh-btn-sm" onclick="window.location.reload()">{{ __('Try again') }}</button>
            <a class="uh-btn-secondary uh-btn-sm" href="{{ url('/') }}">{{ __('Go to the home page') }}</a>
        </div>
    </main>
@endsection
