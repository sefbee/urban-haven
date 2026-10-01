@extends('layouts.auth')
@section('title', 'Set up two-factor authentication')

@section('card')
    <h1 class="uh-h3">Set up two-factor authentication</h1>
    <p class="mt-1.5 text-sm text-[var(--color-muted)]">
        Scan this code with an authenticator app (Google Authenticator, Microsoft Authenticator, 1Password), then enter the six-digit code it shows.
    </p>

    @if(session('status'))
        <div class="uh-alert uh-alert-info mt-5" role="status"><p>{{ session('status') }}</p></div>
    @endif

    <div class="mt-5 flex justify-center rounded-xl bg-white p-4" aria-hidden="true">{!! $qrSvg !!}</div>
    <p class="mt-3 text-center text-xs text-[var(--color-muted)]">
        Can’t scan? Enter this key manually:<br>
        <code class="mt-1 inline-block select-all break-all rounded bg-[var(--color-surface-2)] px-2 py-1 font-mono text-[0.8rem] text-ink" dir="ltr">{{ trim(chunk_split($secret, 4, ' ')) }}</code>
    </p>

    <form method="POST" action="{{ route('admin.mfa.confirm') }}" class="mt-6 space-y-4" x-data="uhForm" @submit="submit">
        @csrf
        <x-ui.input name="code" label="Six-digit code" inputmode="numeric" autocomplete="one-time-code" dir="ltr" required autofocus />
        <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">Turn on two-factor authentication</button>
    </form>

    <form method="POST" action="{{ route('admin.logout') }}" class="mt-3 text-center">
        @csrf
        <button type="submit" class="text-xs text-[var(--color-muted)] underline">Sign out</button>
    </form>
@endsection
