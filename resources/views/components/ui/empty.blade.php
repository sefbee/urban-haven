@props(['icon' => 'inbox', 'title', 'description' => null])

<div {{ $attributes->class('uh-empty') }}>
    <span class="uh-empty-icon">
        <x-icon :name="$icon" class="size-5" />
    </span>
    <p class="uh-h4">{{ $title }}</p>
    @if($description)
        <p class="mt-2 max-w-md text-sm leading-relaxed text-[var(--color-muted)]">{{ $description }}</p>
    @endif
    @if(! $slot->isEmpty())
        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">{{ $slot }}</div>
    @endif
</div>
