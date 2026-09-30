<?php

namespace App\Http\Controllers\Public;

use App\Contracts\LeadService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\LeadCaptureRequest;
use App\Services\Lead\SiteVisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(LeadCaptureRequest $request, LeadService $leads): JsonResponse|RedirectResponse
    {
        try {
            $lead = $leads->capture($request->validated(), $request);
        } catch (\Throwable) {
            $message = 'We could not save your enquiry. Please call us or try again.';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 500)
                : back()->withErrors(['form' => $message]);
        }

        if ($request->expectsJson()) {
            return response()->json(['id' => $lead->id, 'message' => 'Thank you. Our sales desk will be in touch.'], 201);
        }

        return back()->with('status', 'Thank you. Our sales desk will be in touch.');
    }

    public function visit(Request $request, SiteVisitService $visits): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'email' => ['nullable', 'email'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'preferred_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
        $visits->create($validated, $request);

        return back()->with('status', 'Visit request received.');
    }
}
