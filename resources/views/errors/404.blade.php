@extends('layouts.app')

@section('title', __('Page not found'))

@section('content')
    <main id="main" class="uh-container-narrow uh-confirm flex flex-1 flex-col justify-center">
        <p class="uh-error-code" aria-hidden="true">404</p>
        <h1 class="uh-h1">{{ __('We could not find that page') }}</h1>
        <p class="uh-lede">
            {{ __('The link may be out of date, or the home you were looking for is no longer published. These pages will help you pick up where you left off.') }}
        </p>

        <ul class="uh-error-links">
            @foreach([
                ['url' => url('/'), 'title' => __('Home page'), 'body' => __('Start again from the beginning.')],
                ['url' => route('properties.index'), 'title' => __('Browse homes'), 'body' => __('Search everything we have available.')],
                ['url' => route('projects.index'), 'title' => __('Our projects'), 'body' => __('See the developments we have built.')],
            ] as $link)
                <li>
                    <a href="{{ $link['url'] }}">
                        <span>
                            <span class="block font-medium">{{ $link['title'] }}</span>
                            <span class="mt-0.5 block text-sm text-[var(--uh-muted)]">{{ $link['body'] }}</span>
                        </span>
                        <x-icon name="arrow-right" class="size-4 shrink-0" />
                    </a>
                </li>
            @endforeach
        </ul>
    </main>
@endsection
