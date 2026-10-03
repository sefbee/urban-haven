@php($unread = auth()->user()->unreadNotifications()->limit(6)->get())
@php($unreadCount = auth()->user()->unreadNotifications()->count())

<div class="relative" x-data="{ open: false }" @keydown.escape="open = false">
    <button type="button" class="uh-admin-icon-btn relative" @click="open = ! open"
            :aria-expanded="open.toString()" aria-controls="notification-menu">
        <x-icon name="bell" class="size-5" />
        @if($unreadCount)
            <span class="uh-admin-notify-count">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
        <span class="sr-only">{{ $unreadCount ? $unreadCount.' unread notifications' : 'Notifications' }}</span>
    </button>

    <div id="notification-menu" x-show="open" x-cloak x-transition.opacity.duration.120ms @click.outside="open = false"
         class="uh-admin-popover w-80">
        <p class="uh-admin-user-menu-id text-[0.6875rem] font-medium tracking-wide text-[var(--color-muted)]">
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

        <a href="{{ route('admin.notifications.index') }}" class="uh-admin-popover-foot">
            View all notifications
        </a>
    </div>
</div>
