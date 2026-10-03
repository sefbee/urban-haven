@extends('layouts.public')

@section('content')
    @include('public.partials.page-head', [
        'title' => __('Tools'),
        'lede' => __('Estimate a home loan EMI or ask our desk for a valuation on a Dhaka property.'),
        'crumbs' => [
            ['label' => __('Home'), 'url' => route('home')],
            ['label' => __('Tools')],
        ],
    ])

    <div class="uh-container grid gap-10 py-10 lg:grid-cols-2 lg:items-start">
            <div id="valuation" class="scroll-mt-24">
            <div class="uh-panel">
                <h2 class="uh-h3">{{ __('Valuation Tool') }}</h2>
                <p class="mt-1.5 text-sm text-[var(--color-muted)]">{{ __('Share the basics. A member of the sales desk will call with a guided figure — we do not publish automated valuations.') }}</p>

                <div class="mt-6">
                    @include('public.partials.lead-form', [
                        'leadType' => 'general_contact',
                        'source' => 'valuation',
                        'prefix' => 'valuation',
                        'submitLabel' => __('Request a valuation call'),
                        'messageLabel' => __('About your property'),
                        'messagePlaceholder' => __('Area, size in sq ft or katha, property type, and whether it is for sale or rent.'),
                    ])
                </div>
            </div>
        </div>

        <div id="emi" class="scroll-mt-24">
            @include('public.partials.emi-calculator', ['price' => 10000000, 'editable' => true, 'headingId' => 'tools-emi'])
        </div>

        @php($whatsappHref = \App\Support\PhoneNumber::whatsappHref(\App\Models\Setting::get('whatsapp')))
        <div class="lg:col-span-2">
            <div class="uh-panel">
                <h2 class="uh-h3">{{ __('Contact Urban Haven Agent') }}</h2>
                <p class="mt-1.5 max-w-2xl text-sm text-[var(--color-muted)]">
                    {{ __('Every home on this site is Urban Haven inventory. Call, WhatsApp, or send a brief and the same desk will follow up.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a class="uh-btn-primary" href="{{ route('cms.show', 'contact') }}">
                        {{ __('Contact us') }}
                    </a>
                    @if($whatsappHref)
                        <a class="uh-btn-whatsapp" href="{{ $whatsappHref }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="tools">
                            <x-icon name="whatsapp" class="size-4" />
                            {{ __('WhatsApp Us') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
