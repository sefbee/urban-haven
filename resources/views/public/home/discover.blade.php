<section class="border-t border-line bg-sand">
    <div class="uh-container py-12 md:py-14">
        <h2 class="uh-h2">{{ __('Explore Urban Haven') }}</h2>
        <p class="uh-lede mt-3 max-w-2xl">{{ __('Jump to the listings, types, locations, and projects that are live on this site.') }}</p>

        <div class="mt-8 grid gap-8 sm:grid-cols-2 xl:grid-cols-4">
            <nav aria-label="{{ __('Properties') }}">
                <h3 class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-gold-ink)]">{{ __('Properties') }}</h3>
                <ul class="mt-3 space-y-1 text-sm">
                    @if(in_array('sale', $purposes, true))
                        <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('properties.index', ['listing_type' => 'sale']) }}">{{ __('Properties for sale') }}</a></li>
                    @endif
                    @if(in_array('rent', $purposes, true))
                        <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('properties.index', ['listing_type' => 'rent']) }}">{{ __('Properties for rent') }}</a></li>
                    @endif
                    <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('articles.index') }}">{{ __('Articles and guides') }}</a></li>
                    <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('faq') }}">{{ __('Frequently asked questions') }}</a></li>
                    <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('properties.index') }}">{{ __('All Properties') }}</a></li>
                </ul>
            </nav>

            @if($typeCards->isNotEmpty())
                <nav aria-label="{{ __('Property Types') }}">
                    <h3 class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-gold-ink)]">{{ __('Property Types') }}</h3>
                    <ul class="mt-3 space-y-1 text-sm">
                        @foreach($typeCards as $type)
                            <li>
                                <a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('properties.index', ['property_type_id' => $type->id]) }}">
                                    {{ $type->label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif

            @if($areas->isNotEmpty() || $cities->isNotEmpty())
                <nav aria-label="{{ __('Locations') }}">
                    <h3 class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-gold-ink)]">{{ __('Locations') }}</h3>
                    <ul class="mt-3 space-y-1 text-sm">
                        @foreach($cities as $city)
                            <li>
                                <a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('properties.index', ['city' => $city]) }}">
                                    {{ $city }}
                                </a>
                            </li>
                        @endforeach
                        @foreach($areas->take(6) as $area)
                            <li>
                                <a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ $area->hasLandingPage() ? route('locations.show', $area->slug) : route('properties.index', ['location_area_id' => $area->id]) }}">
                                    {{ $area->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif

            <nav aria-label="{{ __('Projects') }}">
                <h3 class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-gold-ink)]">{{ __('Projects') }}</h3>
                <ul class="mt-3 space-y-1 text-sm">
                    <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('projects.index') }}">{{ __('All Projects') }}</a></li>
                    @if($hasAvailableProjects)
                        <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('projects.index', ['development_stage' => 'available']) }}">{{ __('Available Projects') }}</a></li>
                    @endif
                    @if($hasCompletedProjects)
                        <li><a class="uh-link-quiet inline-flex min-h-9 items-center" href="{{ route('projects.index', ['development_stage' => 'completed']) }}">{{ __('Completed Projects') }}</a></li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>
</section>
