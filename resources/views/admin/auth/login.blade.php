@extends('layouts.auth')
@section('title', 'Staff sign in')

@section('card')
    <h1 class="uh-h3">Welcome back</h1>
    <p class="mt-1.5 text-sm text-[var(--color-muted)]">Sign in to your account</p>

    @if($errors->any())
        <div class="uh-alert uh-alert-danger mt-5" role="alert">
            <x-icon name="alert" class="mt-px size-4 shrink-0" />
            <p>{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}" class="uh-auth-form mt-6 space-y-4"
          x-data="uhForm" @submit="submit">
        @csrf
        <label class="uh-auth-input-wrap">
            <span class="sr-only">Email</span>
            <input class="uh-input" name="email" type="email" dir="ltr" placeholder="Email*"
                   value="{{ old('email') }}" autocomplete="username" required autofocus>
            <x-icon name="mail" class="size-4" />
        </label>
        <label class="uh-auth-input-wrap" x-data="{ visible: false }">
            <span class="sr-only">Password</span>
            <input class="uh-input" name="password" :type="visible ? 'text' : 'password'" placeholder="Password*"
                   autocomplete="current-password" required>
            <button class="uh-auth-password-toggle" type="button" @click="visible = !visible" :aria-label="visible ? 'Hide password' : 'Show password'">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
            </button>
        </label>
        <label class="uh-auth-remember">
            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
            <span>Remember me</span>
        </label>

        <button type="submit" class="uh-btn-primary uh-btn-block uh-auth-submit" :disabled="submitting">
            <span class="uh-spinner" x-show="submitting" x-cloak></span>
            <span x-text="submitting ? 'Checking…' : 'Sign In'">Sign In</span>
        </button>
    </form>

@endsection
