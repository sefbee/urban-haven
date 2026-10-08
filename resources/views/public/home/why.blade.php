<section class="uh-section" aria-labelledby="why-title">
    <div class="uh-container uh-why">
        <h2 id="why-title" class="uh-h2" data-reveal>{{ $sectionContent['title'] }}</h2>
        <div data-reveal style="--uh-i: 1">
            <p class="uh-why-body">
                {{ $sectionContent['body'] }}
            </p>
            @if($sectionContent['items'])
                <ul class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach($sectionContent['items'] as $item)
                        <li class="flex items-start gap-3">
                            <x-icon :name="$item['icon']" class="mt-0.5 size-4 shrink-0 text-forest" />
                            <span>
                                <strong class="block font-semibold">{{ $item['title'] }}</strong>
                                @if(filled($item['text']))<span class="mt-1 block text-sm text-muted">{{ $item['text'] }}</span>@endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <x-ui.pill-link class="mt-8" :href="$sectionContent['link_url']">{{ $sectionContent['link_label'] }}</x-ui.pill-link>
        </div>
    </div>
</section>
