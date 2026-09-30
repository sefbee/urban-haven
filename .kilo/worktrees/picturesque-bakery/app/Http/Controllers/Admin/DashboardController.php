<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Property;
use App\Models\PublicationState;
use App\Models\Role;
use App\Models\SiteVisitRequest;
use App\Support\DisplayTimezone;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $leadQuery = Lead::query();
        if ($user->hasRole(Role::SALES_USER) && ! $user->isOwnerAdmin()) {
            $leadQuery->where('assigned_to', $user->id);
        }

        return view('admin.dashboard', [
            'newLeads' => (clone $leadQuery)->where('status', 'new')->count(),
            'visits' => SiteVisitRequest::query()->when(
                $user->hasRole(Role::SALES_USER) && ! $user->isOwnerAdmin(),
                fn ($query) => $query->where('assigned_to', $user->id),
            )->whereDate('preferred_at', '>=', now()->toDateString())->count(),
            'pendingReview' => Property::query()->whereHas('publicationState', fn ($query) => $query->where('status', PublicationState::PENDING_REVIEW))->count(),
            'recentLeads' => (clone $leadQuery)->with(['property', 'assignee'])->latest('id')->limit(8)->get(),
            'unread' => $user->unreadNotifications()->count(),
            'displayTimezone' => DisplayTimezone::class,
        ]);
    }
}
