@php
    $capabilities = array_values(array_filter([
        ['title' => __('Listed directly by Urban Haven'), 'detail' => __('No third-party sellers or agents')],
        ['title' => __('Availability shown on every listing'), 'detail' => __('Available, reserved, sold or rented')],
        ['title' => __('Book a site visit online'), 'detail' => __('We call to confirm the time')],
        $hasWhatsapp
            ? ['title' => __('Call or WhatsApp our team'), 'detail' => __('Talk to the people who manage the property')]
            : ['title' => __('Talk to our sales team'), 'detail' => __('Talk to the people who manage the property')],
    ]));
@endphp

<section class="uh-trust" aria-label="{{ __('What you get with Urban Haven') }}">
    <div class="uh-container">
        <ul class="grid gap-8 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($capabilities as $capability)
                <li>
                    <strong>{{ $capability['title'] }}</strong>
                    <span>{{ $capability['detail'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
