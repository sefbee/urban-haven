<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(30),
        ]);
    }

    public function unread(Request $request): JsonResponse
    {
        $unread = $request->user()->unreadNotifications()->latest()->limit(8)->get();

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'notifications' => $unread->map(function ($n) {
                return [
                    'id' => $n->id,
                    'message' => $n->data['message'] ?? 'Notification',
                    'subtext' => $n->data['property'] ?? $n->data['name'] ?? null,
                    'url' => route('admin.notifications.go', $n->id),
                    'icon' => $n->data['icon'] ?? 'bell',
                    'time' => $n->created_at?->diffForHumans() ?? 'Just now',
                ];
            })->values()->all(),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse|JsonResponse
    {
        $request->user()->notifications()->whereKey($notification)->update(['read_at' => now()]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('status', 'All notifications marked as read.');
    }

    public function go(Request $request, string $notification): RedirectResponse
    {
        $notif = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $notif->markAsRead();

        $url = $notif->data['url'] ?? null;

        if (! $url) {
            if (! empty($notif->data['lead_id'])) {
                $url = route('admin.leads.show', $notif->data['lead_id']);
            } elseif (! empty($notif->data['visit_id'])) {
                $url = route('admin.visits.index');
            } elseif (! empty($notif->data['property_id'])) {
                $url = route('admin.properties.edit', $notif->data['property_id']);
            } else {
                $url = route('admin.notifications.index');
            }
        }

        return redirect($url);
    }
}
