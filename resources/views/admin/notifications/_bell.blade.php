@php($unread = auth()->user()->unreadNotifications()->limit(6)->get())
@php($unreadCount = auth()->user()->unreadNotifications()->count())

<div class="relative" x-data="{ open: false }" @keydown.escape="open = false">
    <button type="button" class="uh-icon-btn relative" @click="open = ! open"
            :aria-expanded="open.toString()" aria-controls="notification-menu">
        <x-icon name="bell" class="size-5" />
        @if($unreadCount)
            <span class="absolute -right-0.5 -top-0.5 flex min-w-4 items-center justify-center rounded-full bg-[var(--color-gold-ink)] px-1 text-[0.625rem] font-bold leading-4 text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
        <span class="sr-only">{{ $unreadCount ? $unreadCount.' unread notifications' : 'Notifications' }}</span>
    </button>

    <div id="notification-menu" x-show="open" x-cloak @click.outside="open = false"
         class="absolute right-0 z-30 mt-2 w-80 overflow-hidden rounded-xl border border-line bg-paper shadow-lg">
        <p class="border-b border-line bg-sand px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.12em] text-[var(--color-muted)]">
            Unread
        </p>

        @forelse($unread as $notification)
            <div class="flex items-start gap-3 border-b border-line px-4 py-3">
                <p class="flex-1 text-sm leading-snug">{{ $notification->data['message'] ?? 'Update' }}</p>
                <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="uh-link text-xs">Mark read</button>
                </form>
            </div>
        @empty
            <p class="px-4 py-6 text-center text-sm text-[var(--color-muted)]">Nothing unread right now.</p>
        @endforelse

        <a href="{{ route('admin.notifications.index') }}" class="block bg-sand px-4 py-2.5 text-xs font-semibold text-forest hover:underline">
            View all notifications
        </a>
    </div>
</div>
