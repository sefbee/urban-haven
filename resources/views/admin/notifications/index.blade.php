@extends('layouts.admin')
@section('title', 'Notifications')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <x-ui.page-header compact title="Notifications"
                          description="Every alert raised for your desk, newest first.">
            <x-slot:eyebrow>Dashboard</x-slot:eyebrow>
        </x-ui.page-header>

        @if($notifications->isNotEmpty() && auth()->user()->unreadNotifications()->count() > 0)
            <div>
                <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="uh-btn-outline uh-btn-sm">
                        <x-icon name="check" class="size-3.5" />
                        Mark all as read
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if($notifications->isNotEmpty())
        <div class="uh-panel-flush divide-y divide-[var(--color-line)]">
            @foreach($notifications as $notification)
                @php
                    $isUnread = ! $notification->read_at;
                    $url = $notification->data['url'] ?? (
                        ! empty($notification->data['lead_id']) ? route('admin.leads.show', $notification->data['lead_id']) : (
                            ! empty($notification->data['visit_id']) ? route('admin.visits.index') : (
                                ! empty($notification->data['property_id']) ? route('admin.properties.edit', $notification->data['property_id']) : null
                            )
                        )
                    );
                    $icon = $notification->data['icon'] ?? 'bell';
                @endphp
                <div @class(['flex flex-wrap items-start gap-x-4 gap-y-2 px-5 py-4 transition-colors', 'bg-blue-50/40' => $isUnread])>
                    <div class="dd-notify-icon-tile dd-notify-icon-{{ $icon }}">
                        <x-icon :name="$icon" class="size-4" />
                    </div>
                    <div class="min-w-0 flex-1">
                        @if($url)
                            <a href="{{ route('admin.notifications.go', $notification->id) }}" class="text-sm font-medium leading-snug text-[var(--dd-ink)] hover:text-blue-600 transition-colors">
                                {{ $notification->data['message'] ?? 'Update' }}
                            </a>
                        @else
                            <p class="text-sm font-medium leading-snug text-[var(--dd-ink)]">{{ $notification->data['message'] ?? 'Update' }}</p>
                        @endif

                        @if(! empty($notification->data['property']) || ! empty($notification->data['name']))
                            <p class="text-xs text-[var(--dd-muted)] mt-0.5">
                                {{ $notification->data['property'] ?? $notification->data['name'] }}
                            </p>
                        @endif

                        <p class="mt-1 text-xs text-[var(--color-muted)]">
                            {{ \App\Support\DisplayTimezone::format($notification->created_at) }}
                            ({{ $notification->created_at?->diffForHumans() }})
                            @if($notification->read_at)
                                · <span class="text-slate-400">read</span>
                            @else
                                · <span class="font-semibold text-blue-600">new</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($url)
                            <a href="{{ route('admin.notifications.go', $notification->id) }}" class="uh-btn-ghost uh-btn-sm text-xs">
                                Open
                                <x-icon name="arrow-right" class="size-3" />
                            </a>
                        @endif
                        @unless($notification->read_at)
                            <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="uh-btn-outline uh-btn-sm text-xs" title="Mark as read">
                                    <x-icon name="check" class="size-3" />
                                    Mark read
                                </button>
                            </form>
                        @endunless
                    </div>
                </div>
            @endforeach
        </div>

        @if($notifications->hasPages())
            <div class="mt-6">{{ $notifications->links() }}</div>
        @endif
    @else
        <x-ui.empty class="mt-6" icon="bell" title="No notifications yet"
                    description="New enquiries and visit requests assigned to you will appear here." />
    @endif
@endsection
