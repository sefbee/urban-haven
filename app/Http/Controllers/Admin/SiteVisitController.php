<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteVisitRequest;
use App\Services\Lead\SiteVisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteVisitController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SiteVisitRequest::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(SiteVisitRequest::STATUSES)],
            'view' => ['nullable', Rule::in(['upcoming', 'all'])],
        ]);

        $visits = SiteVisitRequest::query()
            ->visibleTo($request->user())
            ->with(['lead:id,name,phone,assigned_to', 'lead.assignee:id,name', 'property:id,title,slug', 'project:id,name,slug'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when(($filters['view'] ?? null) === 'upcoming', fn ($query) => $query
                ->whereIn('status', [SiteVisitRequest::REQUESTED, SiteVisitRequest::CONFIRMED])
                ->orderByRaw('coalesce(confirmed_at, preferred_at) is null, coalesce(confirmed_at, preferred_at) asc'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.visits.index', [
            'visits' => $visits,
            'filters' => $filters,
        ]);
    }

    public function updateStatus(Request $request, SiteVisitRequest $visit, SiteVisitService $visits): RedirectResponse
    {
        $this->authorize('update', $visit);

        $validated = $request->validate([
            'status' => ['required', Rule::in(SiteVisitRequest::STATUSES)],
            'confirmed_at' => ['nullable', 'required_if:status,confirmed', 'date'],
            'outcome_note' => ['nullable', 'required_if:status,completed', 'string', 'max:2000'],
        ], [
            'confirmed_at.required_if' => 'Enter the confirmed visit time.',
            'outcome_note.required_if' => 'Record the visit outcome.',
        ]);

        $visits->updateStatus($visit, $validated['status'], $request->user(), $validated);

        return back()->with('status', 'Visit marked '.strtolower(SiteVisitRequest::STATUS_LABELS[$validated['status']]).'.');
    }
}
