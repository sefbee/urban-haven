@extends('layouts.admin')
@section('title', 'Notifications')

@section('content')
    <x-ui.page-header compact title="Notifications"
                      description="Every alert raised for your desk, newest first." />

    @if($notifications->isNotEmpty())
        <div class="uh-panel-flush mt-6 divide-y divide-[var(--color-line)]">
            @foreach($notifications as $notification)
                <div @class(['flex flex-wrap items-start gap-x-4 gap-y-2 px-4 py-3.5', 'bg-sand/40' => ! $notification->read_at])>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm leading-snug">{{ $notification->data['message'] ?? 'Update' }}</p>
                        <p class="mt-1 text-xs text-[var(--color-muted)]">
                            {{ \App\Support\DisplayTimezone::format($notification->created_at) }}
                            @if($notification->read_at)
                                · read
                            @endif
                        </p>
                    </div>
                    @unless($notification->read_at)
                        <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="uh-btn-outline uh-btn-sm">
                                <x-icon name="check" class="size-3.5" />
                                Mark read
                            </button>
                        </form>
                    @endunless
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
