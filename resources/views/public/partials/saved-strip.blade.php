<ul class="flex snap-x gap-3 overflow-x-auto pb-2">
    @foreach($properties as $property)
        @php($image = $property->featuredImage())
        <li class="w-56 shrink-0 snap-start">
            <a href="{{ route('properties.show', $property->slug) }}" class="uh-card block overflow-hidden">
                <span class="uh-media block aspect-4/3">
                    @if($image)
                        <img src="{{ $image->url(480) }}" alt="{{ $image->alt(app()->getLocale()) }}" loading="lazy" decoding="async">
                    @else
                        <span class="uh-media-placeholder"><x-icon name="image" class="size-6" /></span>
                    @endif
                </span>
                <span class="block p-3">
                    <span class="line-clamp-2 text-sm font-semibold">{{ $property->title }}</span>
                    <span class="mt-1 block text-xs text-[var(--color-muted)]">{{ $property->isPriceOnRequest() ? __('Price on request') : \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis) }}</span>
                </span>
            </a>
        </li>
    @endforeach
</ul>
