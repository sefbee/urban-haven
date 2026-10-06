<section class="uh-section" aria-labelledby="why-title">
    <div class="uh-container uh-why">
        <h2 id="why-title" class="uh-h2" data-reveal>{{ $about['title'] ?? __('Why Urban Haven?') }}</h2>
        <div data-reveal style="--uh-i: 1">
            <p class="uh-why-body">
                {{ $about['body'] ?? __('One place to find Urban Haven properties, with the details you need to decide. The same team that publishes each listing answers your questions and walks you through the door.') }}
            </p>
            <a class="uh-arrow-link mt-8" href="{{ route('cms.show', 'about') }}">
                {{ __('More about us') }}
                <x-icon name="arrow-right" class="size-3.5" />
            </a>
        </div>
    </div>
</section>
