<section id="home-locations" class="uh-section uh-band-paper scroll-mt-20" @if(filled($sectionContent['title'])) aria-labelledby="locations-title" @else aria-label="{{ __('Explore by location') }}" @endif>
    <div class="uh-container">
        @if(filled($sectionContent['title']) || filled($sectionContent['subtitle']) || filled($sectionContent['link_label']))
            <header class="uh-section-head" data-reveal>
                @if(filled($sectionContent['title']))
                    <h2 id="locations-title" class="uh-h2">{{ $sectionContent['title'] }}</h2>
                @endif
                @if(filled($sectionContent['subtitle']))
                    <p class="uh-lede">{{ $sectionContent['subtitle'] }}</p>
                @endif
                @if(filled($sectionContent['link_label']))
                    <x-ui.pill-link :href="route('properties.index', ['view' => 'map'])">{{ $sectionContent['link_label'] }}</x-ui.pill-link>
                @endif
            </header>
        @endif

        <ul class="uh-places">
            @foreach($areas as $area)
                <li data-reveal style="--uh-i: {{ $loop->index % 4 }}">
                        @if($area->image_path)
                            <a href="{{ $area->hasLandingPage() ? route('locations.show', $area->slug) : route('properties.index', ['location_area_id' => $area->id]) }}"
                               class="uh-type-tile" style="height: 100%; min-height: 5.25rem; border-radius: 1.5rem;">
                                <img class="uh-type-tile-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($area->image_path) }}" alt="{{ $area->name }}" style="object-fit: cover;">
                                <span class="uh-type-tile-shade" aria-hidden="true"></span>
                                <span class="uh-type-tile-name flex flex-col gap-0.5">
                                    <span>{{ $area->name }}</span>
                                    <span class="text-[0.6875rem] font-normal text-white/80 text-shadow-none">@if($area->city){{ $area->city }} · @endif{{ trans_choice(':count listing|:count listings', $area->properties_count, ['count' => $area->properties_count]) }}</span>
                                </span>
                            </a>
                        @else
                            <a href="{{ $area->hasLandingPage() ? route('locations.show', $area->slug) : route('properties.index', ['location_area_id' => $area->id]) }}"
                               class="uh-place">
                                <span class="uh-place-icon" aria-hidden="true">
                                    <x-icon name="pin" class="size-4" />
                                </span>
                                <span class="uh-place-copy">
                                    <span class="uh-place-name">{{ $area->name }}</span>
                                    <span class="uh-place-meta">
                                        @if($area->city){{ $area->city }} · @endif{{ trans_choice(':count listing|:count listings', $area->properties_count, ['count' => $area->properties_count]) }}
                                    </span>
                                </span>
                                <x-icon name="arrow-right" class="uh-place-arrow" />
                            </a>
                        @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
