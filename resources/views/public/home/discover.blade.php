<section class="uh-home-section">
    <div class="uh-container">
        <h2 class="uh-home-title">{{ __('Explore Urban Haven') }}</h2>
        <p class="uh-home-lede">{{ __('Jump to the listings, types, locations, and projects that are live on this site.') }}</p>

        <div class="mt-8 grid gap-8 sm:grid-cols-2 xl:grid-cols-4">
            <nav aria-label="{{ __('Properties') }}">
                <h3 class="text-sm font-semibold">{{ __('Properties') }}</h3>
                <ul class="mt-3 space-y-1 text-sm text-[var(--color-muted)]">
                    @if(in_array('sale', $purposes, true))
                        <li><a class="uh-home-link inline-flex min-h-9 items-center font-normal" href="{{ route('properties.index', ['listing_type' => 'sale']) }}">{{ __('Properties for sale') }}</a></li>
                    @endif
                    @if(in_array('rent', $purposes, true))
                        <li><a class="uh-home-link inline-flex min-h-9 items-center font-normal" href="{{ route('properties.index', ['listing_type' => 'rent']) }}">{{ __('Properties for rent') }}</a></li>
                    @endif
                    <li><a class="uh-home-link inline-flex min-h-9 items-center font-normal" href="{{ route('articles.index') }}">{{ __('Articles and guides') }}</a></li>
                    <li><a class="uh-home-link inline-flex min-h-9 items-center font-normal" href="{{ route('faq') }}">{{ __('Frequently asked questions') }}</a></li>
                    <li><a class="uh-home-link inline-flex min-h-9 items-center font-normal" href="{{ route('properties.index') }}">{{ __('All Properties') }}</a></li>
                </ul>
            </nav>

            @if($typeCards->isNotEmpty())
                <nav aria-label="{{ __('Property Types') }}">
                    <h3 class="text-sm font-semibold">{{ __('Property Types') }}</h3>
                    <ul class="mt-3 space-y-1 text-sm text-[var(--color-muted)]">
                        @foreach($typeCards as $type)
                            <li>
                                <a class="uh-home-link inline-flex min-h-9 items-center font-normal" href="{{ route('properties.index', ['property_type_id' => $type->id]) }}">
                                    {{ $type->label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif

            @if($areas->isNotEmpty() || $cities->isNotEmpty())
                <nav aria-label="{{ __('Locations') }}">
                    <h3 class="text-sm font-semibold">{{ __('Locations') }}</h3>
                    <ul class="mt-3 space-y-1 text-sm text-[var(--color-muted)]">
                        @foreach($cities as $city)
                            <li>
                                <a class="uh-home-link inline-flex min-h-9 items-center font-normal" href="{{ route('properties.index', ['city' => $city]) }}">
                                    {{ $city }}
                                </a>
                            </li>
                        @endforeach
                        @foreach($areas->take(6) as $area)
                            <li>
                                <a class="uh-home-link inline-flex min-h-9 items-center font-normal" href="{{ $area->hasLandingPage() ? route('locations.show', $area->slug) : route('properties.index', ['location_area_id' => $area->id]) }}">
                                    {{ $area->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif

            <nav aria-label="{{ __('Projects') }}">
                <h3 class="text-sm font-semibold">{{ __('Projects') }}</h3>
                <ul class="mt-3 space-y-1 text-sm text-[var(--color-muted)]">
                    <li><a class="uh-home-link inline-flex min-h-9 items-center font-normal" href="{{ route('projects.index') }}">{{ __('All Projects') }}</a></li>
                    @if($hasAvailableProjects)
                        <li><a class="uh-home-link inline-flex min-h-9 items-center font-normal" href="{{ route('projects.index', ['development_stage' => 'available']) }}">{{ __('Available Projects') }}</a></li>
                    @endif
                    @if($hasCompletedProjects)
                        <li><a class="uh-home-link inline-flex min-h-9 items-center font-normal" href="{{ route('projects.index', ['development_stage' => 'completed']) }}">{{ __('Completed Projects') }}</a></li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>
</section>
