<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\LeadService;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);
        $user = $request->user();

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Lead::STATUSES)],
            'priority' => ['nullable', Rule::in(Lead::PRIORITIES)],
            'type' => ['nullable', Rule::in(Lead::TYPES)],
            'source' => ['nullable', 'string', 'max:120'],
            'utm_campaign' => ['nullable', 'string', 'max:120'],
            'assigned_to' => ['nullable', 'string', 'max:20'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'view' => ['nullable', Rule::in(['overdue', 'unread', 'open'])],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $query = Lead::query()
            ->visibleTo($user)
            ->with(['property:id,title,reference', 'project:id,name', 'assignee:id,name']);

        $this->applyFilters($query, $filters, $user);

        $countQuery = Lead::query()->visibleTo($user);
        $this->applyFilters($countQuery, $filters, $user);

        return view('admin.leads.index', [
            'leads' => $query->salesQueueOrder()->paginate(25)->withQueryString(),
            'filters' => $filters,
            'salesUsers' => $user->canSeeAllLeads() ? User::query()->salesStaff()->orderBy('name')->get(['id', 'name']) : collect(),
            'overdueCount' => (clone $countQuery)->whereNotIn('status', Lead::CLOSED_STATUSES)->where('next_action_at', '<', now())->count(),
        ]);
    }

    public function show(Request $request, Lead $lead, LeadService $leads): View
    {
        $this->authorize('view', $lead);
        $leads->markOpened($lead, $request->user());

        $history = Lead::query()
            ->visibleTo($request->user())
            ->where('phone_hash', $lead->phone_hash)
            ->whereKeyNot($lead->id)
            ->with(['property:id,title', 'project:id,name'])
            ->latest('id')
            ->limit(20)
            ->get();

        return view('admin.leads.show', [
            'lead' => $lead->load(['property', 'project', 'assignee', 'notes.user', 'followUps.user', 'siteVisits', 'activities.actor', 'repeatOf']),
            'history' => $history,
            'salesUsers' => User::query()->salesStaff()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function assign(Request $request, Lead $lead, LeadService $leads): RedirectResponse
    {
        $this->authorize('assign', $lead);
        $validated = $request->validate([
            'assigned_to' => ['required', Rule::exists('users', 'id')->where('is_active', true)],
        ]);
        $assignee = User::query()->salesStaff()->find($validated['assigned_to']);

        if ($assignee === null) {
            return back()->withErrors(['assigned_to' => 'Leads can only be assigned to active sales staff.']);
        }

        $leads->assign($lead, $assignee, $request->user());

        return back()->with('status', 'Lead assigned to '.$assignee->name.'.');
    }

    public function updateStatus(Request $request, Lead $lead, LeadService $leads): RedirectResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validate([
            'status' => ['required', Rule::in(Lead::STATUSES)],
            'loss_reason' => ['nullable', 'required_if:status,lost', Rule::in(array_keys(Lead::LOSS_REASONS))],
        ], [
            'loss_reason.required_if' => 'Choose why this lead was lost.',
        ]);

        $allowed = Lead::TRANSITIONS[$lead->status] ?? [];
        if (! in_array($validated['status'], $allowed, true)) {
            return back()->withErrors(['status' => 'That stage transition is not allowed from '.Lead::STATUS_LABELS[$lead->status].'.']);
        }

        $leads->updateStatus($lead, $validated['status'], $request->user(), $validated['loss_reason'] ?? null);

        return back()->with('status', 'Stage updated to '.Lead::STATUS_LABELS[$validated['status']].'.');
    }

    public function updatePriority(Request $request, Lead $lead, LeadService $leads): RedirectResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validate([
            'priority' => ['required', Rule::in(Lead::PRIORITIES)],
            'next_action' => ['nullable', 'string', 'max:255'],
            'next_action_at' => ['nullable', 'date'],
        ]);
        $leads->updatePriority($lead, $validated['priority'], $validated['next_action'] ?? null, $validated['next_action_at'] ?? null, $request->user());

        return back()->with('status', 'Priority and next action saved.');
    }

    public function addNote(Request $request, Lead $lead, LeadService $leads): RedirectResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validate(['body' => ['required', 'string', 'min:2', 'max:5000']]);
        $leads->addNote($lead, $validated['body'], $request->user());

        return back()->with('status', 'Note added.');
    }

    public function addFollowUp(Request $request, Lead $lead, LeadService $leads): RedirectResponse
    {
        $this->authorize('update', $lead);

        if ($request->input('quick') === 'call_tomorrow') {
            $request->merge([
                'action_type' => 'call',
                'scheduled_at' => now(config('urbanhaven.display_timezone'))->addDay()->setTime(10, 0)->toDateTimeString(),
                'priority' => $request->input('priority', $lead->priority),
            ]);
        }

        $validated = $request->validate([
            'action_type' => ['required', Rule::in(LeadFollowUp::ACTION_TYPES)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['required', 'date'],
            'priority' => ['nullable', Rule::in(Lead::PRIORITIES)],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
        ]);

        if (! $request->user()->canSeeAllLeads()) {
            $validated['user_id'] = $request->user()->id;
        }

        $validated['scheduled_at'] = Carbon::parse($validated['scheduled_at'], config('urbanhaven.display_timezone'))->utc();
        $leads->scheduleFollowUp($lead, $validated, $request->user());

        return back()->with('status', 'Follow-up scheduled.');
    }

    public function completeFollowUp(Request $request, LeadFollowUp $followUp, LeadService $leads): RedirectResponse
    {
        $this->authorize('update', $followUp->lead);
        $leads->completeFollowUp($followUp, $request->user());

        return back()->with('status', 'Follow-up completed.');
    }

    public function cancelFollowUp(Request $request, LeadFollowUp $followUp, LeadService $leads): RedirectResponse
    {
        $this->authorize('update', $followUp->lead);
        $leads->cancelFollowUp($followUp, $request->user());

        return back()->with('status', 'Follow-up cancelled.');
    }

    public function followUps(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);
        $user = $request->user();

        $followUps = LeadFollowUp::query()
            ->where('status', LeadFollowUp::OPEN)
            ->whereHas('lead', fn (Builder $lead) => $lead->visibleTo($user))
            ->when(! $user->canSeeAllLeads(), fn (Builder $query) => $query->where('user_id', $user->id))
            ->when($request->query('view', 'overdue') === 'overdue', fn (Builder $query) => $query->where('scheduled_at', '<', now()))
            ->with(['lead:id,name,priority,status', 'user:id,name'])
            ->orderByRaw("case priority when 'high' then 0 when 'medium' then 1 else 2 end")
            ->orderBy('scheduled_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.leads.follow-ups', [
            'followUps' => $followUps,
            'view' => $request->query('view', 'overdue'),
        ]);
    }

    /**
     * @param  Builder<Lead>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters, User $user): void
    {
        $query
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn (Builder $q, string $priority) => $q->where('priority', $priority))
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->where('type', $type))
            ->when($filters['source'] ?? null, fn (Builder $q, string $source) => $q->where(fn (Builder $s) => $s->where('utm_source', $source)->orWhere('source', $source)))
            ->when($filters['utm_campaign'] ?? null, fn (Builder $q, string $campaign) => $q->where('utm_campaign', $campaign))
            ->when($filters['from'] ?? null, fn (Builder $q, string $from) => $q->where('created_at', '>=', Carbon::parse($from, config('urbanhaven.display_timezone'))->startOfDay()->utc()))
            ->when($filters['to'] ?? null, fn (Builder $q, string $to) => $q->where('created_at', '<=', Carbon::parse($to, config('urbanhaven.display_timezone'))->endOfDay()->utc()))
            ->when(($filters['view'] ?? null) === 'overdue', fn (Builder $q) => $q->whereNotIn('status', Lead::CLOSED_STATUSES)->where('next_action_at', '<', now()))
            ->when(($filters['view'] ?? null) === 'unread', fn (Builder $q) => $q->where('is_unread', true))
            ->when(($filters['view'] ?? null) === 'open', fn (Builder $q) => $q->whereNotIn('status', Lead::CLOSED_STATUSES))
            ->when($filters['q'] ?? null, function (Builder $q, string $term): void {
                $q->where(fn (Builder $s) => $s->where('name', 'like', '%'.$term.'%')->orWhere('phone', 'like', '%'.preg_replace('/\D+/', '', $term).'%')->orWhere('email', 'like', '%'.$term.'%'));
            });

        if ($user->canSeeAllLeads() && filled($filters['assigned_to'] ?? null)) {
            $filters['assigned_to'] === 'unassigned'
                ? $query->whereNull('assigned_to')
                : $query->where('assigned_to', (int) $filters['assigned_to']);
        }
    }
}
