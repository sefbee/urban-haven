<ul class="uh-strip">
    @foreach($properties as $property)
        @php($image = $property->featuredImage())
        <li>
            <a href="{{ route('properties.show', $property->slug) }}" class="uh-strip-item">
                <span class="uh-compare-photo">
                    @if($image)
                        <img src="{{ $image->url(480) }}" alt="{{ $image->alt(app()->getLocale()) }}" loading="lazy" decoding="async">
                    @else
                        <span class="uh-media-placeholder"></span>
                    @endif
                </span>
                <span class="uh-strip-title">{{ $property->title }}</span>
                <span class="uh-strip-meta uh-numeric">{{ $property->locationArea?->name }}@if($property->locationArea?->name) · @endif{{ $property->isPriceOnRequest() ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis) }}</span>
            </a>
        </li>
    @endforeach
</ul>
