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

<section id="home-standards" class="uh-statement scroll-mt-20" aria-labelledby="statement-title">
    <div class="uh-container">
        <p id="statement-title" class="uh-statement-line" data-reveal>
            <strong>{{ __('No marketplace. No unknown sellers.') }}</strong>
            <span>{{ __('Every property here is published by the team that will show it to you.') }}</span>
        </p>
        <ul class="uh-statement-facts" aria-label="{{ __('What you get with Urban Haven') }}">
            @foreach($capabilities as $capability)
                <li data-reveal style="--uh-i: {{ $loop->index }}">
                    <strong>{{ $capability['title'] }}</strong>
                    <span>{{ $capability['detail'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
