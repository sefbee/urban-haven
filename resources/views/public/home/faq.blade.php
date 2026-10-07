<section id="home-faq" class="uh-section scroll-mt-20" aria-labelledby="faq-title">
    <div class="uh-container uh-home-faq">
        <header class="uh-home-faq-head" data-reveal>
            <h2 id="faq-title" class="uh-h2">{{ __('Frequently') }}<br>{{ __('asked questions.') }}</h2>
            <div class="uh-home-faq-aside">
                <p>{{ __('Answers to your questions,') }}<br>{{ __('every step of the way.') }}</p>
                <x-ui.pill-link variant="light" :href="route('cms.show', 'contact')" data-track="home_cta_click" data-track-cta="faq">
                    {{ __('Get in touch') }}
                </x-ui.pill-link>
            </div>
        </header>

        <div class="uh-home-faq-list">
            @foreach($faqs as $faq)
                <details class="uh-home-faq-item" @if($loop->first) open @endif data-reveal style="--uh-i: {{ $loop->index }}">
                    <summary>
                        <span>{{ $faq->question }}</span>
                        <span class="uh-home-faq-toggle" aria-hidden="true">
                            <x-icon name="chevron-down" class="size-3.5" />
                        </span>
                    </summary>
                    <div class="uh-home-faq-answer">{!! \Stevebauman\Purify\Facades\Purify::clean($faq->answer) !!}</div>
                </details>
            @endforeach
        </div>

        <div class="uh-section-foot">
            <x-ui.pill-link :href="route('faq')">{{ __('View all') }}</x-ui.pill-link>
        </div>
    </div>
</section>
