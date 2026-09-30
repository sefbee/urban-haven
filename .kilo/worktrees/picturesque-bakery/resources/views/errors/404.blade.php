@extends('layouts.app')

@section('title', __('Page not found'))

@section('content')
    <main id="main" class="uh-container-narrow flex flex-1 flex-col justify-center py-16 md:py-24">
        <p class="uh-eyebrow">404</p>
        <h1 class="uh-h1 mt-3">{{ __('We could not find that page') }}</h1>
        <p class="uh-lede mt-4">
            {{ __('The link may be out of date, or the home you were looking for is no longer published. These pages will help you pick up where you left off.') }}
        </p>

        <div class="mt-8 grid gap-3 sm:grid-cols-3">
            <a class="uh-card uh-card-hover p-5" href="{{ url('/') }}">
                <x-icon name="home" class="size-5 text-[var(--color-gold-ink)]" />
                <p class="mt-3 font-semibold">{{ __('Home page') }}</p>
                <p class="mt-1 text-sm text-[var(--color-muted)]">{{ __('Start again from the beginning.') }}</p>
            </a>
            <a class="uh-card uh-card-hover p-5" href="{{ route('properties.index') }}">
                <x-icon name="search" class="size-5 text-[var(--color-gold-ink)]" />
                <p class="mt-3 font-semibold">{{ __('Browse homes') }}</p>
                <p class="mt-1 text-sm text-[var(--color-muted)]">{{ __('Search everything we have available.') }}</p>
            </a>
            <a class="uh-card uh-card-hover p-5" href="{{ route('projects.index') }}">
                <x-icon name="building" class="size-5 text-[var(--color-gold-ink)]" />
                <p class="mt-3 font-semibold">{{ __('Our projects') }}</p>
                <p class="mt-1 text-sm text-[var(--color-muted)]">{{ __('See the developments we have built.') }}</p>
            </a>
        </div>
    </main>
@endsection
