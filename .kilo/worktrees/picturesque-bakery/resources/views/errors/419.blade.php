@extends('layouts.app')

@section('title', __('Page expired'))

@section('content')
    <main id="main" class="uh-container-narrow flex flex-1 flex-col justify-center py-16 md:py-24">
        <p class="uh-eyebrow">419</p>
        <h1 class="uh-h1 mt-3">{{ __('This page sat open too long') }}</h1>
        <p class="uh-lede mt-4">
            {{ __('For your security the form expired before it was submitted. Nothing was saved. Reload the page and send it once more.') }}
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <button type="button" class="uh-btn-primary uh-btn-sm" onclick="window.location.reload()">{{ __('Reload the page') }}</button>
            <a class="uh-btn-outline uh-btn-sm" href="{{ url('/') }}">{{ __('Go to the home page') }}</a>
        </div>
    </main>
@endsection
