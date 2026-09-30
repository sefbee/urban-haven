@extends('layouts.auth')
@section('title', 'Staff sign in')

@section('card')
    <h1 class="uh-h3">Staff sign in</h1>
    <p class="mt-1.5 text-sm text-[var(--color-muted)]">
        This desk is for Urban Haven staff. Sign-in attempts are rate limited and logged.
    </p>

    @if($errors->any())
        <div class="uh-alert uh-alert-danger mt-5" role="alert">
            <x-icon name="alert" class="mt-px size-4 shrink-0" />
            <p>{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4"
          x-data="uhForm" @submit="submit">
        @csrf
        <x-ui.input name="email" label="Work email" type="email" dir="ltr"
                    autocomplete="username" required autofocus />
        <x-ui.input name="password" label="Password" type="password"
                    autocomplete="current-password" required />

        <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">
            <span class="uh-spinner" x-show="submitting" x-cloak></span>
            <span x-text="submitting ? 'Checking…' : 'Continue'">Continue</span>
        </button>
    </form>

@endsection
