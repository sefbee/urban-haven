@php
    $whyPoints = [
        ['title' => __('Our own inventory'), 'body' => __('Every property and project here is published by the Urban Haven team, not collected from other sellers.')],
        ['title' => __('Clear information'), 'body' => __('Price (or "price on request"), size, location and current availability are shown on each listing.')],
        ['title' => __('Search the way you think'), 'body' => __('Filter by purpose, type, location, budget and bedrooms, on a list or a map.')],
        ['title' => __('One team from enquiry to visit'), 'body' => __('The same sales team answers your questions and arranges your site visit.')],
    ];
    $whyImage = collect([$featuredSale ?? collect(), $featuredRent ?? collect(), $latestSale ?? collect(), $latestRent ?? collect()])
        ->flatten()
        ->first(fn ($property) => $property->featuredImage() !== null)
        ?->featuredImage();
@endphp

<section class="uh-home-section uh-home-why" x-data="{ open: 0 }">
    <div class="uh-container">
        <div class="uh-home-why-intro">
            <h2 class="uh-home-title">{{ $about['title'] ?? __('Why Urban Haven?') }}</h2>
            <p class="uh-home-lede">
                {{ $about['body'] ?? __('One place to find Urban Haven properties and projects, with the details you need to decide.') }}
            </p>
        </div>

        <div class="uh-home-why-grid">
            <ul class="uh-home-why-list">
                @foreach($whyPoints as $index => $point)
                    <li class="uh-home-why-item" :data-open="open === {{ $index }} ? true : null">
                        <button type="button" class="uh-home-why-trigger" @click="open = open === {{ $index }} ? -1 : {{ $index }}"
                                :aria-expanded="(open === {{ $index }}).toString()">
                            <span>{{ $point['title'] }}</span>
                            <x-icon name="chevron-down" class="uh-home-why-chevron size-4" />
                        </button>
                        <p class="uh-home-why-body" x-show="open === {{ $index }}" x-cloak>{{ $point['body'] }}</p>
                    </li>
                @endforeach
            </ul>

            @if($whyImage)
                <div class="uh-home-why-photo">
                    <img src="{{ $whyImage->url(1280) }}"
                         srcset="{{ $whyImage->url(768) }} 768w, {{ $whyImage->url(1280) }} 1280w"
                         sizes="(min-width: 1024px) 36rem, 100vw"
                         alt="" loading="lazy" decoding="async">
                </div>
            @endif
        </div>
    </div>
</section>
