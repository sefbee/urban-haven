@extends('layouts.public')

@section('title', __('My property activity'))

@section('content')
    <main class="uh-container uh-section">
        <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="uh-eyebrow">{{ __('Your account') }}</p>
                <h1 class="uh-h1">{{ __('My property activity') }}</h1>
                <p class="mt-2 text-sm text-[var(--uh-muted)]">{{ auth()->user()->name }} · {{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('account.logout') }}">
                @csrf
                <button class="uh-btn-secondary" type="submit">{{ __('Sign out') }}</button>
            </form>
        </header>

        @if($leads->isEmpty())
            <section class="uh-pd-card">
                <h2 class="uh-h3">{{ __('No property activity yet') }}</h2>
                <p class="mt-2 text-sm text-[var(--uh-muted)]">{{ __('When you enquire about a property or request a visit while signed in, it will appear here.') }}</p>
                <a class="uh-btn-primary mt-5 inline-flex" href="{{ route('properties.index') }}">{{ __('Browse properties') }}</a>
            </section>
        @else
            <div class="uh-account-activity-list">
                @foreach($leads as $lead)
                    @php
                        $listing = $lead->property;
                        $image = $listing?->featuredImage();
                        $listingTitle = $listing?->title ?? $lead->project?->name ?? __('Property enquiry');
                    @endphp
                    <article class="uh-account-activity">
                        <div @class(['uh-account-activity-image', 'grid place-items-center' => ! $image])>
                            @if($image)
                                <img src="{{ $image->url(320) }}" alt="{{ $image->alt(app()->getLocale()) ?: $listingTitle }}" loading="lazy" decoding="async">
                            @else
                                <x-icon name="building" class="size-6 text-[var(--uh-faint)]" />
                            @endif
                        </div>
                        <div class="min-w-0 py-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-[var(--uh-muted)]">{{ $lead->typeLabel() }} · {{ \App\Support\DisplayTimezone::format($lead->created_at) }}</p>
                            <h2 class="mt-1 truncate text-base font-semibold">
                                @if($listing)
                                    <a class="uh-link-quiet" href="{{ route('properties.show', $listing->slug) }}">{{ $listingTitle }}</a>
                                @else
                                    {{ $listingTitle }}
                                @endif
                            </h2>
                            @if($lead->property?->publicAddress())
                                <p class="mt-1 text-sm text-[var(--uh-muted)]">{{ $lead->property->publicAddress() }}</p>
                            @endif
                            <p class="mt-2 text-sm text-[var(--uh-muted)]">{{ __('Status') }}: {{ $lead->statusLabel() }}</p>
                            @foreach($lead->siteVisits as $visit)
                                <p class="mt-1 text-sm text-[var(--uh-muted)]">{{ __('Visit') }}: {{ \App\Support\DisplayTimezone::format($visit->preferred_at) }} · {{ $visit->statusLabel() }}</p>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-6">{{ $leads->links() }}</div>
        @endif
    </main>
@endsection
