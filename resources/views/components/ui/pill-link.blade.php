@props(['href', 'variant' => 'light'])

<a href="{{ $href }}" {{ $attributes->class(['group inline-flex items-center justify-between gap-3 rounded-full bg-slate-900 py-1 pl-5 pr-1 text-sm font-medium text-white transition hover:bg-slate-800 shadow-sm min-h-[2.75rem] w-max']) }}>
    <span>{{ $slot }}</span>
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-slate-900 transition group-hover:scale-105" aria-hidden="true">
        <x-icon name="arrow-up-right" class="size-4 transition group-hover:rotate-45" />
    </span>
</a>