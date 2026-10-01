@extends('layouts.public')

@section('content')
    <div class="border-b border-line bg-paper">
        <div class="uh-container py-8 md:py-10">
            <x-ui.breadcrumbs :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Tools')],
            ]" />
            <h1 class="uh-h1 mt-3">{{ __('Tools') }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-[var(--color-muted)]">
                {{ __('Estimate a home loan EMI or ask our desk for a valuation on a Dhaka property.') }}
            </p>
        </div>
    </div>

    <div class="uh-container grid gap-10 py-10 lg:grid-cols-2 lg:items-start">
            <div id="valuation" class="scroll-mt-24">
            <div class="uh-panel">
                <h2 class="uh-h3">{{ __('Valuation Tool') }}</h2>
                <p class="mt-1.5 text-sm text-[var(--color-muted)]">{{ __('Share the basics. A member of the sales desk will call with a guided figure — we do not publish automated valuations.') }}</p>

                <form method="POST" action="{{ route('inquiries.store') }}" class="mt-6 space-y-4" x-data="uhForm" @submit="submit">
                    @csrf
                    <input type="hidden" name="source" value="valuation">
                    <x-ui.input name="name" :label="__('Your name')" autocomplete="name" required />
                    <x-ui.input name="phone" :label="__('Mobile number')" type="tel" dir="ltr" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" required />
                    <x-ui.input name="email" :label="__('Email')" type="email" dir="ltr" autocomplete="email" optional />
                    <x-ui.select name="area_label" :label="__('Area')">
                        <option value="">{{ __('Any area') }}</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->name }}">{{ $area->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.textarea name="message" :label="__('Property notes')" rows="4" required
                                   :placeholder="__('Area, size in sq-ft or katha, property type, and whether it is for sale or rent.')" />
                    <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting">
                        <span class="uh-spinner" x-show="submitting" x-cloak></span>
                        {{ __('Request a valuation') }}
                    </button>
                </form>
            </div>
        </div>

        <div id="emi" class="scroll-mt-24">
            @include('public.partials.emi-calculator', ['price' => 10000000, 'editable' => true, 'headingId' => 'tools-emi'])
        </div>

        @php($whatsappNumber = preg_replace('/\D+/', '', (string) config('urbanhaven.whatsapp.number')))
        <div class="lg:col-span-2">
            <div class="uh-panel">
                <h2 class="uh-h3">{{ __('Contact Urban Haven Agent') }}</h2>
                <p class="mt-1.5 max-w-2xl text-sm text-[var(--color-muted)]">
                    {{ __('Every home on this site is Urban Haven inventory. Call, WhatsApp, or send a brief and the same desk will follow up.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a class="uh-btn-gold" href="{{ route('cms.show', 'contact') }}">
                        {{ __('Inquire Now') }}
                    </a>
                    @if(filled($whatsappNumber))
                        <a class="uh-btn-whatsapp" href="https://wa.me/{{ $whatsappNumber }}" rel="noopener">
                            <x-icon name="whatsapp" class="size-4" />
                            {{ __('WhatsApp Us') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
