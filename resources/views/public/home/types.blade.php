<section class="uh-home-section">
    <div class="uh-container">
        <h2 class="uh-home-title">{{ __('Explore by Property Type') }}</h2>
        <p class="uh-home-lede">{{ __('Browse Urban Haven inventory by the kinds of homes we actually publish.') }}</p>

        <div class="uh-home-spots">
            @foreach($typeCards as $type)
                @php
                    $image = $type->coverSource?->featuredImage();
                @endphp
                <a href="{{ route('properties.index', ['property_type_id' => $type->id]) }}" class="uh-home-spot">
                    <span class="uh-home-spot-photo">
                        @if($image)
                            <img src="{{ $image->url(768) }}"
                                 srcset="{{ $image->url(480) }} 480w, {{ $image->url(768) }} 768w"
                                 sizes="(min-width: 1024px) 22rem, 100vw"
                                 alt="" loading="lazy" decoding="async">
                        @endif
                    </span>
                    <span class="uh-home-spot-title">{{ $type->label }}</span>
                    <span class="uh-home-spot-meta">
                        {{ trans_choice(':count property|:count properties', $type->properties_count, ['count' => $type->properties_count]) }}
                    </span>
                    <span class="uh-home-link mt-2 inline-flex text-sm">{{ __('View listings') }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
