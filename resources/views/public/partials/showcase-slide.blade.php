@php
    $onRequest = $property->isPriceOnRequest();
    $compact = $onRequest ? null : \App\Support\MoneyFormatter::compactBdt($property->price);
    $exact = $onRequest ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headline = $property->listing_type !== 'rent' && $compact ? 'BDT '.$compact : $exact;
    $url = route('properties.show', $property->slug);
    $image = $property->featuredImage();
    $story = \App\Support\PropertyStory::for($property);
    $isPlot = $story->kind() === \App\Support\PropertyStory::KIND_PLOT;
    $place = collect([$property->locationArea?->name, $property->locationArea?->city])->filter()->implode(', ') ?: __('Dhaka');
    $summary = \Illuminate\Support\Str::of(strip_tags((string) $property->description))->squish()->limit(110)->toString()
        ?: $place.($property->propertyType?->label ? ' · '.$property->propertyType->label : '');
    $specs = array_values(array_filter([
        ! $isPlot && $property->bedrooms ? trans_choice(':count bedroom|:count bedrooms', (int) $property->bedrooms, ['count' => $property->bedrooms]) : null,
        ! $isPlot && $property->bathrooms ? trans_choice(':count bathroom|:count bathrooms', (float) $property->bathrooms, ['count' => $property->bathrooms]) : null,
        $isPlot || ! $property->bedrooms ? $story->size() : null,
    ]));
    $slot = match (true) {
        $index === 0 => 'active',
        $index === 1 => 'next',
        $index === $total - 1 && $total > 2 => 'prev',
        default => 'far-next',
    };
@endphp

<article class="uh-showcase-slide" data-slot="{{ $slot }}" :data-slot="slot({{ $index }})"
         :aria-hidden="(slot({{ $index }}) !== 'active').toString()"
         @click="if (slot({{ $index }}) !== 'active') { $event.preventDefault(); go({{ $index }}); }"
         aria-roledescription="{{ __('slide') }}" aria-label="{{ __(':current of :total', ['current' => $index + 1, 'total' => $total]) }}"
         data-spotlight="{{ $property->id }}">
    <a class="uh-showcase-media" href="{{ $url }}" :tabindex="slot({{ $index }}) === 'active' ? 0 : -1"
       aria-label="{{ $story->exploreLabel() }}: {{ $property->title }}"
       style="view-transition-name: uh-property-{{ $property->id }}">
        @if($image)
            <img src="{{ $image->url(1280) }}"
                 srcset="{{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w"
                 sizes="(min-width: 1024px) 46vw, 80vw"
                 alt="" @if($index > 1) loading="lazy" @endif decoding="async" draggable="false">
        @else
            <span class="uh-media-placeholder">{{ $property->locationArea?->name ?? __('Urban Haven') }}</span>
        @endif
    </a>

    <div class="uh-showcase-copy">
        <h3 class="uh-showcase-title">
            <a href="{{ $url }}" lang="{{ app()->getLocale() }}" :tabindex="slot({{ $index }}) === 'active' ? 0 : -1"
               data-track="property_card_click" data-track-property-id="{{ $property->id }}">{{ $property->title }}</a>
        </h3>
        <p class="uh-showcase-price uh-numeric">{{ $headline }}</p>
        <p class="uh-showcase-summary">{{ $summary }}</p>
        @if($specs)
            <ul class="uh-showcase-specs uh-numeric">
                @foreach($specs as $spec)
                    <li>{{ $spec }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</article>
