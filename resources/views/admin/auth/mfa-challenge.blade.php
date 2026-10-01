@extends('layouts.auth')
@section('title', 'Two-factor check')

@section('card')
    <h1 class="uh-h3">Two-factor check</h1>
    <p class="mt-1.5 text-sm text-[var(--color-muted)]">
        Enter the six-digit code from your authenticator app, or one of your recovery codes.
    </p>

    <form method="POST" action="{{ route('admin.mfa.verify') }}" class="mt-6 space-y-4" x-data="uhForm" @submit="submit">
        @csrf
        <x-ui.input name="code" label="Code" autocomplete="one-time-code" dir="ltr" required autofocus />
        <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">Verify</button>
    </form>

    <form method="POST" action="{{ route('admin.logout') }}" class="mt-3 text-center">
        @csrf
        <button type="submit" class="text-xs text-[var(--color-muted)] underline">Sign out</button>
    </form>
@endsection
