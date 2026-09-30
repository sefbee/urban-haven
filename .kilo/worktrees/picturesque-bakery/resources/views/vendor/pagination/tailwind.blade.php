@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-[var(--color-muted)]">
            {{ __('Showing') }}
            <span class="uh-numeric font-semibold text-ink">{{ $paginator->firstItem() }}</span>
            {{ __('to') }}
            <span class="uh-numeric font-semibold text-ink">{{ $paginator->lastItem() }}</span>
            {{ __('of') }}
            <span class="uh-numeric font-semibold text-ink">{{ $paginator->total() }}</span>
            {{ __('results') }}
        </p>

        <ul class="flex flex-wrap items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="uh-btn-outline uh-btn-sm pointer-events-none opacity-40" aria-disabled="true">{{ __('Previous') }}</span>
                @else
                    <a class="uh-btn-outline uh-btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ __('Previous') }}</a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="inline-flex min-h-9 min-w-9 items-center justify-center text-sm text-[var(--color-muted)]">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg bg-forest text-sm font-semibold text-cream">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg text-sm font-medium text-ink transition hover:bg-sand">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a class="uh-btn-outline uh-btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('Next') }}</a>
                @else
                    <span class="uh-btn-outline uh-btn-sm pointer-events-none opacity-40" aria-disabled="true">{{ __('Next') }}</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
