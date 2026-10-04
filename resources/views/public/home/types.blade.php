<section id="home-types" class="uh-section uh-band-paper scroll-mt-20" x-data="{ active: 0 }" aria-labelledby="types-title">
    <div class="uh-container">
        <div class="uh-types">
            <h2 id="types-title" class="uh-h2 uh-types-title" data-reveal>{{ __('Explore by Property Type') }}</h2>
            <p class="uh-lede uh-types-lede" data-reveal>{{ __('Start with the kind of space you have in mind.') }}</p>

            <ul class="uh-types-list">
                @foreach($typeCards as $type)
                    @php
                        $image = $type->coverSource?->featuredImage();
                    @endphp
                    <li data-reveal style="--uh-i: {{ $loop->index }}">
                        <a href="{{ route('properties.index', ['property_type_id' => $type->id]) }}"
                           class="uh-types-row"
                           :class="active === {{ $loop->index }} ? 'is-active' : ''"
                           @mouseenter="active = {{ $loop->index }}" @focus="active = {{ $loop->index }}">
                            @if($image)
                                <span class="uh-types-thumb" aria-hidden="true">
                                    <img src="{{ $image->url(768) }}" sizes="(min-width: 1024px) 0px, 96px"
                                         alt="" loading="lazy" decoding="async"
                                         class="uh-shape" data-shape="{{ $type->key }}">
                                </span>
                            @endif
                            <span class="uh-types-name">{{ $type->label }}</span>
                            <span class="uh-types-count">
                                {{ trans_choice(':count available|:count available', $type->properties_count, ['count' => $type->properties_count]) }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="uh-types-preview" aria-hidden="true">
                @foreach($typeCards as $type)
                    @php
                        $image = $type->coverSource?->featuredImage();
                    @endphp
                    @if($image)
                        <img src="{{ $image->url(1280) }}"
                             srcset="{{ $image->url(768) }} 768w, {{ $image->url(1280) }} 1280w"
                             sizes="(min-width: 1024px) 40vw, 0px"
                             alt="" loading="lazy" decoding="async"
                             data-shape="{{ $type->key }}"
                             :class="{ 'is-active': active === {{ $loop->index }} }"
                             @class(['uh-shape', 'is-active' => $loop->first])>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
