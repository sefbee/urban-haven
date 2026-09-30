<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteVisitRequest;
use App\Services\Lead\SiteVisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteVisitController extends Controller
{
    public function index(): View
    {
        return view('admin.visits.index', [
            'visits' => SiteVisitRequest::query()->with(['lead', 'property', 'project'])->latest('id')->paginate(20),
        ]);
    }

    public function updateStatus(Request $request, SiteVisitRequest $visit, SiteVisitService $visits): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'string']]);
        $visits->updateStatus($visit, $validated['status'], $request->user());

        return back()->with('status', 'Visit updated.');
    }
}
