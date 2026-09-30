<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\LeadService;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);

        $query = Lead::query()->with(['property', 'project', 'assignee'])->latest('id');

        if ($request->user()->hasRole(Role::SALES_USER) && ! $request->user()->isOwnerAdmin()) {
            $query->where('assigned_to', $request->user()->id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('utm_source')) {
            $query->where('utm_source', $request->string('utm_source'));
        }
        if ($request->filled('utm_campaign')) {
            $query->where('utm_campaign', $request->string('utm_campaign'));
        }

        return view('admin.leads.index', [
            'leads' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['status', 'utm_source', 'utm_campaign']),
        ]);
    }

    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        return view('admin.leads.show', [
            'lead' => $lead->load(['property', 'project', 'assignee', 'notes.user', 'followUps.user', 'siteVisits']),
            'salesUsers' => User::query()->whereHas('roles', fn ($query) => $query->whereIn('key', [Role::SALES_USER, Role::OWNER_ADMIN]))->orderBy('name')->get(),
        ]);
    }

    public function assign(Request $request, Lead $lead, LeadService $leads): RedirectResponse
    {
        $this->authorize('assign', $lead);
        $validated = $request->validate(['assigned_to' => ['required', 'exists:users,id']]);
        $leads->assign($lead, User::query()->findOrFail($validated['assigned_to']), $request->user());

        return back()->with('status', 'Lead assigned.');
    }

    public function updateStatus(Request $request, Lead $lead, LeadService $leads): RedirectResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validate(['status' => ['required', 'in:'.implode(',', Lead::STATUSES)]]);
        $leads->updateStatus($lead, $validated['status'], $request->user());

        return back()->with('status', 'Status updated.');
    }

    public function addNote(Request $request, Lead $lead, LeadService $leads): RedirectResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validate(['body' => ['required', 'string', 'min:2']]);
        $leads->addNote($lead, $validated['body'], $request->user());

        return back()->with('status', 'Note added.');
    }

    public function addFollowUp(Request $request, Lead $lead, LeadService $leads): RedirectResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validate([
            'action_type' => ['required', 'in:'.implode(',', LeadFollowUp::ACTION_TYPES)],
            'notes' => ['nullable', 'string'],
            'scheduled_at' => ['required', 'date'],
        ]);
        $leads->scheduleFollowUp($lead, $validated, $request->user());

        return back()->with('status', 'Follow-up scheduled.');
    }

    public function completeFollowUp(LeadFollowUp $followUp, LeadService $leads): RedirectResponse
    {
        $this->authorize('update', $followUp->lead);
        $leads->completeFollowUp($followUp, request()->user());

        return back()->with('status', 'Follow-up completed.');
    }
}
