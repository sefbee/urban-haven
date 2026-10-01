<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Project;
use App\Models\Property;
use App\Models\PublicationState;
use App\Models\SiteVisitRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $seesLeads = $user->hasPermission('lead.view');
        $seesInventory = $user->hasPermission('property.view');
        $canReview = $user->hasPermission('property.publish') || $user->hasPermission('project.publish');
        $since = now()->subDays(30);

        $leads = fn (): Builder => Lead::query()->visibleTo($user);

        return view('admin.dashboard', [
            'seesLeads' => $seesLeads,
            'seesInventory' => $seesInventory,
            'canReview' => $canReview,
            'leadStats' => $seesLeads ? [
                'new' => $leads()->where('status', 'new')->count(),
                'unread' => $leads()->where('is_unread', true)->count(),
                'overdue' => LeadFollowUp::query()->overdue()
                    ->whereHas('lead', fn (Builder $lead) => $lead->visibleTo($user))
                    ->when(! $user->canSeeAllLeads(), fn (Builder $query) => $query->where('user_id', $user->id))
                    ->count(),
                'visits' => SiteVisitRequest::query()->visibleTo($user)
                    ->whereIn('status', [SiteVisitRequest::REQUESTED, SiteVisitRequest::CONFIRMED])
                    ->where(fn (Builder $query) => $query->where('confirmed_at', '>=', now())->orWhere(fn (Builder $q) => $q->whereNull('confirmed_at')->where('preferred_at', '>=', now())))
                    ->count(),
                'received30' => $leads()->where('created_at', '>=', $since)->count(),
                'won30' => $leads()->where('status', 'won')->where('updated_at', '>=', $since)->count(),
                'lost30' => $leads()->where('status', 'lost')->where('updated_at', '>=', $since)->count(),
            ] : null,
            'stageCounts' => $seesLeads
                ? $leads()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')
                : collect(),
            'sourceCounts' => $seesLeads
                ? $leads()->where('created_at', '>=', $since)
                    ->selectRaw("coalesce(nullif(utm_source, ''), nullif(source, ''), 'direct') as channel, count(*) as total")
                    ->groupBy('channel')->orderByDesc('total')->limit(6)->pluck('total', 'channel')
                : collect(),
            'recentLeads' => $seesLeads ? $leads()->with(['property:id,title', 'project:id,name', 'assignee:id,name'])->latest('id')->limit(8)->get() : collect(),
            'inventory' => $seesInventory
                ? Property::query()->published()->selectRaw('availability, count(*) as total')->groupBy('availability')->pluck('total', 'availability')
                : collect(),
            'expiringReservations' => $seesInventory
                ? Property::query()->where('availability', 'reserved')->whereBetween('reservation_expires_at', [now(), now()->addDays(3)])->orderBy('reservation_expires_at')->limit(5)->get(['id', 'title', 'reference', 'reservation_expires_at'])
                : collect(),
            'drafts' => $seesInventory
                ? Property::query()->where(fn (Builder $query) => $query->whereDoesntHave('publicationState')->orWhereHas('publicationState', fn (Builder $state) => $state->where('status', PublicationState::DRAFT)))->count()
                : 0,
            'pendingReview' => $canReview
                ? Property::query()->whereHas('publicationState', fn (Builder $state) => $state->whereIn('status', [PublicationState::PENDING_REVIEW, PublicationState::APPROVED]))->count()
                    + Project::query()->whereHas('publicationState', fn (Builder $state) => $state->whereIn('status', [PublicationState::PENDING_REVIEW, PublicationState::APPROVED]))->count()
                : 0,
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }
}
