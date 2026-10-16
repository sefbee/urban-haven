@php
    $reasons = collect($sectionContent['items'] ?? [])
        ->filter(fn (array $item): bool => filled($item['title'] ?? null) || filled($item['text'] ?? null));
    $hasWhyContent = filled($sectionContent['eyebrow'] ?? null)
        || filled($sectionContent['title'] ?? null)
        || filled($sectionContent['body'] ?? null)
        || $reasons->isNotEmpty()
        || (filled($sectionContent['link_label'] ?? null) && filled($sectionContent['link_url'] ?? null));
@endphp

@if($hasWhyContent)
    <section class="uh-section uh-home-why-section bg-white" @if(filled($sectionContent['title'] ?? null)) aria-labelledby="why-title" @else aria-label="{{ __('Why Urban Haven') }}" @endif>
        <div class="uh-container">
            <div class="uh-home-why-layout grid gap-10 lg:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.9fr)] lg:gap-14 xl:gap-20">
                <div>
                    @if(filled($sectionContent['eyebrow'] ?? null))
                        <p class="mb-2 text-xs font-bold uppercase tracking-[0.28em] text-blue-900" data-reveal>{{ $sectionContent['eyebrow'] }}</p>
                    @endif
                    @if(filled($sectionContent['title'] ?? null))
                        <h2 id="why-title" class="mb-5 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl" data-reveal>{{ $sectionContent['title'] }}</h2>
                    @endif

                    @if($reasons->isNotEmpty())
                        <ul class="divide-y divide-slate-200" data-reveal style="--uh-i: 1">
                            @foreach($reasons as $item)
                                <li class="flex items-center gap-4 py-3.5 sm:gap-5">
                                    <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-700 sm:size-14" aria-hidden="true">
                                        <x-icon :name="$item['icon'] ?? 'check-circle'" class="size-5 sm:size-6" />
                                    </span>
                                    <span class="min-w-0">
                                        @if(filled($item['title'] ?? null))
                                            <strong class="block text-base font-bold leading-tight text-slate-950">{{ $item['title'] }}</strong>
                                        @endif
                                        @if(filled($item['text'] ?? null))
                                            <span class="mt-1 block text-sm leading-relaxed text-slate-600">{{ $item['text'] }}</span>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if(filled($sectionContent['body'] ?? null) || (filled($sectionContent['link_label'] ?? null) && filled($sectionContent['link_url'] ?? null)))
                    <div class="flex flex-col items-start justify-center border-t border-slate-200 pt-8 lg:border-l lg:border-t-0 lg:pl-10 lg:pt-0 xl:pl-14" data-reveal style="--uh-i: 2">
                        @if(filled($sectionContent['body'] ?? null))
                            <p class="max-w-2xl text-xl leading-relaxed tracking-tight text-slate-600 sm:text-2xl">{{ $sectionContent['body'] }}</p>
                        @endif
                        @if(filled($sectionContent['link_label'] ?? null) && filled($sectionContent['link_url'] ?? null))
                            <x-ui.pill-link class="mt-7" :mobile-label="true" :href="$sectionContent['link_url']">{{ $sectionContent['link_label'] }}</x-ui.pill-link>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
