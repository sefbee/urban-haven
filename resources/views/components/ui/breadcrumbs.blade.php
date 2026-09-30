@props(['items' => [], 'onDark' => false])

@php
    $items = collect($items)->filter()->values();
@endphp

@if($items->isNotEmpty())
    <nav {{ $attributes->class('min-w-0') }} aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs {{ $onDark ? 'text-cream/70' : 'text-[var(--color-muted)]' }}">
            @foreach($items as $index => $item)
                <li class="flex min-w-0 items-center gap-1.5">
                    @if($index > 0)
                        <x-icon name="chevron-right" class="size-3 shrink-0 opacity-50" />
                    @endif

                    @if(! empty($item['url']) && ! $loop->last)
                        <a href="{{ $item['url'] }}"
                           @class([
                               'truncate transition hover:underline hover:underline-offset-4',
                               'hover:text-gold-soft' => $onDark,
                               'hover:text-forest' => ! $onDark,
                           ])>{{ $item['label'] }}</a>
                    @else
                        <span class="truncate font-medium {{ $onDark ? 'text-cream' : 'text-ink' }}" @if($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
