@php($unread = auth()->user()->unreadNotifications()->latest()->limit(8)->get())
@php($unreadCount = auth()->user()->unreadNotifications()->count())

<div class="relative" x-data="adminNotifications('{{ route('admin.notifications.unread') }}', '{{ route('admin.notifications.mark-all-read') }}', {{ $unreadCount }})" @keydown.escape="open = false" @click.outside="open = false">
    <button type="button" class="dd-header-icon-btn relative" @click="open = ! open"
            :aria-expanded="open.toString()" aria-controls="notification-menu">
        <x-icon name="bell" class="size-5" />
        <template x-if="unreadCount > 0">
            <span class="dd-notify-badge" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
        </template>
        @if($unreadCount > 0)
            <span x-show="unreadCount > 0" class="dd-notify-ping"></span>
        @endif
        <span class="sr-only">Notifications</span>
    </button>

    <div id="notification-menu" x-show="open" x-cloak x-transition.opacity.duration.120ms
         class="dd-notify-popover">
        <div class="dd-notify-head">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-xs tracking-tight text-[var(--dd-ink)]">Notifications</span>
                <template x-if="unreadCount > 0">
                    <span class="dd-search-pill" x-text="unreadCount + ' new'"></span>
                </template>
            </div>
            <template x-if="unreadCount > 0">
                <button type="button" @click="markAllRead()" class="uh-btn-ghost uh-btn-sm text-[0.6875rem] text-[var(--dd-blue-light)] hover:text-blue-700 py-0.5 px-2">
                    Mark all read
                </button>
            </template>
        </div>

        <div class="dd-notify-list">
            <template x-if="notifications.length > 0">
                <div>
                    <template x-for="item in notifications" :key="item.id">
                        <div class="dd-notify-item-wrap">
                            <a :href="item.url" class="dd-notify-item">
                                <span class="dd-notify-icon-tile" :class="'dd-notify-icon-' + (item.icon || 'bell')">
                                    <template x-if="item.icon === 'inbox'"><x-icon name="inbox" class="size-4" /></template>
                                    <template x-if="item.icon === 'calendar'"><x-icon name="calendar" class="size-4" /></template>
                                    <template x-if="item.icon === 'download'"><x-icon name="download" class="size-4" /></template>
                                    <template x-if="item.icon === 'building'"><x-icon name="building" class="size-4" /></template>
                                    <template x-if="item.icon === 'user'"><x-icon name="user" class="size-4" /></template>
                                    <template x-if="!['inbox', 'calendar', 'download', 'building', 'user'].includes(item.icon)"><x-icon name="bell" class="size-4" /></template>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-[var(--dd-ink)] leading-snug line-clamp-2" x-text="item.message"></p>
                                    <p class="text-[0.6875rem] text-[var(--dd-muted)] truncate mt-0.5" x-show="item.subtext" x-text="item.subtext"></p>
                                    <span class="text-[0.625rem] text-[var(--dd-faint)] mt-1 block" x-text="item.time"></span>
                                </div>
                                <button type="button" @click.prevent.stop="markRead(item.id, $event)" title="Mark as read"
                                        class="dd-notify-check-btn">
                                    <x-icon name="check" class="size-3" />
                                </button>
                            </a>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="notifications.length === 0">
                <div class="dd-notify-empty">
                    <span class="dd-notify-empty-icon">
                        <x-icon name="bell" class="size-5 text-[var(--dd-faint)]" />
                    </span>
                    <p class="text-xs font-medium text-[var(--dd-ink)] mt-2">All caught up!</p>
                    <p class="text-[0.6875rem] text-[var(--dd-muted)] mt-0.5">No unread notifications right now.</p>
                </div>
            </template>
        </div>

        <a href="{{ route('admin.notifications.index') }}" class="dd-notify-foot">
            View all alerts & history →
        </a>
    </div>
</div>
