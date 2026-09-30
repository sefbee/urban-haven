@extends('layouts.public')

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container-narrow py-8 md:py-10">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => $page->title],
            ]" />
            <h1 class="uh-h1 mt-3">{{ $page->title }}</h1>
        </div>
    </div>

    <div class="uh-container-narrow uh-section-tight">
        @if($page->slug === 'contact')
            @php
                $phone = \App\Models\Setting::get('phone');
                $email = \App\Models\Setting::get('email');
                $whatsapp = preg_replace('/\D+/', '', (string) config('urbanhaven.whatsapp.number'));
            @endphp
            @if(filled($phone) || filled($email) || filled($whatsapp))
                <aside class="uh-panel mb-10">
                    <p class="uh-eyebrow">{{ __('Talk to an advisor') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-[var(--color-muted)]">
                        {{ __('Our sales desk is in Dhaka. Call, message or write — the same team handles viewings and paperwork.') }}
                    </p>
                    <ul class="mt-5 space-y-1 text-sm">
                        @if(filled($phone))
                            <li>
                                <a class="uh-link-quiet inline-flex min-h-10 items-center gap-2" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">
                                    <x-icon name="phone" class="size-4 text-[var(--color-gold-ink)]" />
                                    <span dir="ltr" class="uh-numeric">{{ $phone }}</span>
                                </a>
                            </li>
                        @endif
                        @if(filled($email))
                            <li>
                                <a class="uh-link-quiet inline-flex min-h-10 items-center gap-2 break-all" href="mailto:{{ $email }}">
                                    <x-icon name="mail" class="size-4 text-[var(--color-gold-ink)]" />
                                    {{ $email }}
                                </a>
                            </li>
                        @endif
                        @if(filled($whatsapp))
                            <li>
                                <a class="uh-link-quiet inline-flex min-h-10 items-center gap-2" href="https://wa.me/{{ $whatsapp }}" rel="noopener">
                                    <x-icon name="whatsapp" class="size-4 text-[var(--color-gold-ink)]" />
                                    {{ __('WhatsApp') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </aside>
            @endif
        @endif

        <div class="uh-prose">{!! $page->body !!}</div>

        <div class="mt-12 border-t border-line pt-7">
            <p class="uh-eyebrow">{{ __('Next step') }}</p>
            <div class="mt-3 flex flex-wrap gap-3">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Browse homes') }}</a>
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('projects.index') }}">{{ __('See our projects') }}</a>
            </div>
        </div>
    </div>
@endsection
