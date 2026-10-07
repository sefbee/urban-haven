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

    @php($whatsappHref = \App\Support\PhoneNumber::whatsappHref(\App\Models\Setting::get('whatsapp')))

    <div class="uh-container uh-section-tight">
        <div id="emi" class="uh-surface uh-tool-card scroll-mt-28">
            @include('public.partials.emi-calculator', ['price' => 10000000, 'editable' => true, 'headingId' => 'tools-emi'])
        </div>

        <div class="uh-tool-split">
            <div>
                <h2 class="uh-h2">{{ __('Valuation Tool') }}</h2>
                <p class="mt-4 max-w-md text-[0.9375rem] leading-relaxed text-[var(--uh-muted)]">{{ __('Share the basics. A member of the sales desk will call with a guided figure — we do not publish automated valuations.') }}</p>
                <ul class="uh-check-list mt-8">
                    <li><x-icon name="check-circle" class="size-5" />{{ __('A real person from our sales desk calls you') }}</li>
                    <li><x-icon name="check-circle" class="size-5" />{{ __('Based on recent Urban Haven sales and lettings') }}</li>
                    <li><x-icon name="check-circle" class="size-5" />{{ __('No obligation to list with us') }}</li>
                </ul>
            </div>
            <div id="valuation" class="uh-convert uh-surface scroll-mt-28">
                <div class="uh-convert-body mt-0">
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

        <aside class="uh-next">
            <p class="uh-h3">{{ __('Contact Urban Haven Agent') }}</p>
            <p>{{ __('Every home on this site is Urban Haven inventory. Call, WhatsApp, or send a brief and the same desk will follow up.') }}</p>
            <div class="uh-next-links">
                <x-ui.pill-link variant="light" :href="route('cms.show', 'contact')">{{ __('Contact us') }}</x-ui.pill-link>
                @if($whatsappHref)
                    <a class="uh-btn-secondary uh-btn-sm" href="{{ $whatsappHref }}" rel="noopener" target="_blank" data-track="whatsapp_click" data-track-location="tools">
                        <x-icon name="whatsapp" class="size-4" />
                        {{ __('WhatsApp Us') }}
                    </a>
                @endif
            </div>
        </aside>
    </div>
@endsection
