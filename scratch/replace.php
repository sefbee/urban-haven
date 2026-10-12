<?php

$content = file_get_contents('resources/views/public/partials/saved-compare.blade.php');

$newContent = preg_replace(
    '/<h2 id="tradeoffs-heading" class="uh-h3">.*?<\/h2>\s*<p.*?>.*?<\/p>/s',
    '<h2 id="tradeoffs-heading" class="uh-h3 flex items-center gap-2">
                    <x-icon name="sparkle" class="size-5 text-[var(--uh-accent)]" />
                    {{ __(\'Smart Comparison\') }}
                </h2>
                <p class="mt-2 text-sm text-[var(--uh-muted)]">{{ __(\'Our automated analysis highlights the key tradeoffs to help you decide.\') }}</p>',
    $content
);

$newContent = preg_replace(
    '/<article class="uh-tradeoff">.*?<\/article>/s',
    '<article class="relative bg-[var(--uh-paper)] rounded-[var(--uh-radius)] border border-[var(--uh-line)] p-5 sm:p-6 shadow-sm overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[var(--uh-accent)] to-purple-500"></div>
                        <p class="text-sm font-medium text-[var(--uh-muted)] mb-1">{{ __(\'Compared with :title\', [\'title\' => \Illuminate\Support\Str::limit($base->title, 40)]) }}</p>
                        <h3 class="text-lg font-semibold text-[var(--uh-ink)] mb-4 leading-tight">
                            <a href="{{ route(\'properties.show\', $other->slug) }}" class="hover:text-[var(--uh-accent)] transition-colors">{{ $other->title }}</a>
                        </h3>
                        
                        @if($tradeoff[\'summary\'])
                            <div class="p-3 mb-4 rounded bg-[var(--uh-accent-ring)] border border-[var(--uh-accent)]/20 text-[var(--uh-accent)] font-medium text-sm sm:text-base leading-snug">
                                {{ $tradeoff[\'summary\'] }}
                            </div>
                        @elseif($tradeoff[\'deltas\'] === [])
                            <div class="p-3 mb-4 rounded bg-gray-50 dark:bg-white/5 border border-[var(--uh-line)] text-[var(--uh-muted)] text-sm sm:text-base">
                                {{ __(\'No measurable difference in price, size or rooms.\') }}
                            </div>
                        @endif
                        
                        @if($tradeoff[\'deltas\'] !== [])
                            <ul class="space-y-2.5">
                                @foreach($tradeoff[\'deltas\'] as $delta)
                                    <li class="flex items-start gap-2.5 text-[0.9375rem] text-[var(--uh-ink-soft)]">
                                        @if($delta[\'tone\'] === \App\Support\CompareTradeoffs::TONE_PLUS)
                                            <span class="mt-0.5 flex-shrink-0 size-5 rounded-full bg-[var(--uh-accent-ring)] text-[var(--uh-accent)] flex items-center justify-center">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                            </span>
                                        @elseif($delta[\'tone\'] === \App\Support\CompareTradeoffs::TONE_MINUS)
                                            <span class="mt-0.5 flex-shrink-0 size-5 rounded-full bg-[var(--uh-accent-ring)] text-[var(--uh-accent)] flex items-center justify-center">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                                            </span>
                                        @else
                                            <span class="mt-0.5 flex-shrink-0 size-5 flex items-center justify-center text-[var(--uh-faint)] font-bold">
                                                =
                                            </span>
                                        @endif
                                        <span class="font-medium">{{ $delta[\'label\'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>',
    $newContent
);

file_put_contents('resources/views/public/partials/saved-compare.blade.php', $newContent);
echo "Replaced\n";
