<section class="uh-home-section">
    <div class="uh-container">
        <h2 class="uh-home-title">{{ __('Explore Properties by Location') }}</h2>
        <p class="uh-home-lede">{{ __('Neighbourhoods where Urban Haven currently has properties listed.') }}</p>

        <div class="uh-home-spots">
            @foreach($areas as $area)
                @php
                    $image = $area->coverSource?->featuredImage();
                @endphp
                <a href="{{ $area->hasLandingPage() ? route('locations.show', $area->slug) : route('properties.index', ['location_area_id' => $area->id]) }}"
                   class="uh-home-spot">
                    <span class="uh-home-spot-photo">
                        @if($image)
                            <img src="{{ $image->url(768) }}"
                                 srcset="{{ $image->url(480) }} 480w, {{ $image->url(768) }} 768w"
                                 sizes="(min-width: 1024px) 22rem, 100vw"
                                 alt="" loading="lazy" decoding="async">
                        @endif
                    </span>
                    <span class="uh-home-spot-title">{{ $area->name }}</span>
                    <span class="uh-home-spot-meta">
                        {{ $area->city }}
                        <span aria-hidden="true"> · </span>
                        {{ trans_choice(':count property|:count properties', $area->properties_count, ['count' => $area->properties_count]) }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
