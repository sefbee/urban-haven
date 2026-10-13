@php
    $reasons = collect($sectionContent['items'])->filter(fn (array $item): bool => filled($item['title'] ?? null) || filled($item['text'] ?? null));
    $hasWhyContent = filled($sectionContent['title']) || filled($sectionContent['body']) || $reasons->isNotEmpty() || (filled($sectionContent['link_label']) && filled($sectionContent['link_url']));
@endphp

@if($hasWhyContent)
<section class="uh-section" @if(filled($sectionContent['title'])) aria-labelledby="why-title" @else aria-label="{{ __('Why Urban Haven') }}" @endif>
    <div class="uh-container uh-why">
        @if(filled($sectionContent['title']))
            <h2 id="why-title" class="uh-h2" data-reveal>{{ $sectionContent['title'] }}</h2>
        @endif
        <div data-reveal style="--uh-i: 1">
            @if(filled($sectionContent['body']))
                <p class="uh-why-body">{{ $sectionContent['body'] }}</p>
            @endif
            @if($reasons->isNotEmpty())
                <ul class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach($reasons as $item)
                        <li class="flex items-start gap-3">
                            <x-icon :name="$item['icon']" class="mt-0.5 size-4 shrink-0 text-forest" />
                            <span>
                                @if(filled($item['title'] ?? null))
                                    <strong class="block font-semibold">{{ $item['title'] }}</strong>
                                @endif
                                @if(filled($item['text']))<span class="mt-1 block text-sm text-muted">{{ $item['text'] }}</span>@endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
            @if(filled($sectionContent['link_label']) && filled($sectionContent['link_url']))
                <x-ui.pill-link class="mt-8" :href="$sectionContent['link_url']">{{ $sectionContent['link_label'] }}</x-ui.pill-link>
            @endif
        </div>
    </div>
</section>
@endif
