@php
    $capabilities = array_values(array_filter([
        ['icon' => 'shield', 'title' => __('Listed directly by Urban Haven'), 'detail' => __('No third-party sellers or agents')],
        ['icon' => 'check-circle', 'title' => __('Availability shown on every listing'), 'detail' => __('Available, reserved, sold or rented')],
        ['icon' => 'calendar', 'title' => __('Book a site visit online'), 'detail' => __('We call to confirm the time')],
        $hasWhatsapp
            ? ['icon' => 'whatsapp', 'title' => __('Call or WhatsApp our team'), 'detail' => __('Talk to the people who manage the property')]
            : ['icon' => 'phone', 'title' => __('Talk to our sales team'), 'detail' => __('Talk to the people who manage the property')],
    ]));
@endphp

<section class="border-b border-line bg-paper" aria-label="{{ __('What you get with Urban Haven') }}">
    <div class="uh-container uh-section-tight">
        <ul class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($capabilities as $capability)
                <li class="flex items-start gap-3 rounded-xl bg-cream px-4 py-4 ring-1 ring-line">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-forest/8 text-forest">
                        <x-icon :name="$capability['icon']" class="size-5" />
                    </span>
                    <span>
                        <span class="block text-sm font-semibold leading-snug">{{ $capability['title'] }}</span>
                        <span class="mt-1 block text-xs text-[var(--color-muted)]">{{ $capability['detail'] }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
