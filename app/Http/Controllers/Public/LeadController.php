<?php

namespace App\Http\Controllers\Public;

use App\Contracts\LeadService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\LeadCaptureRequest;
use App\Http\Requests\Public\SiteVisitRequestForm;
use App\Models\Lead;
use App\Services\Lead\SiteVisitService;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class LeadController extends Controller
{
    public const CONFIRMATION_KEY = 'uh_lead_confirmation';

    public function store(LeadCaptureRequest $request, LeadService $leads): JsonResponse|RedirectResponse
    {
        try {
            $lead = $leads->capture($request->validated(), $request);
        } catch (ThrottleRequestsException $exception) {
            return $this->throttled($request, $exception->getMessage());
        } catch (Throwable $exception) {
            return $this->failed($request, $exception);
        }

        return $this->confirmed($request, $lead, 'inquiry_success', 'Thank you. Our sales team will contact you shortly.', $lead->wasRecentlyCreated);
    }

    public function visit(SiteVisitRequestForm $request, SiteVisitService $visits): JsonResponse|RedirectResponse
    {
        try {
            $visit = $visits->create($request->validated(), $request);
        } catch (ThrottleRequestsException $exception) {
            return $this->throttled($request, $exception->getMessage());
        } catch (Throwable $exception) {
            return $this->failed($request, $exception);
        }

        return $this->confirmed($request, $visit->lead, 'visit_request_success', 'Your visit request is received and pending confirmation. We will call you to confirm a time.', $visit->wasRecentlyCreated);
    }

    public function thankYou(Request $request): View
    {
        $confirmation = $request->session()->get(self::CONFIRMATION_KEY);

        return view('public.thank-you', [
            'confirmation' => $confirmation,
            'analyticsEvents' => $confirmation ? [['event' => $confirmation['event'], 'lead_type' => $confirmation['lead_type']]] : [],
        ]);
    }

    private function confirmed(Request $request, Lead $lead, string $event, string $message, bool $created): JsonResponse|RedirectResponse
    {
        $payload = [
            'event' => $event,
            'lead_type' => $lead->type,
            'message' => $message,
            'subject' => $lead->property?->title ?? $lead->project?->name,
            'subject_url' => $lead->property ? route('properties.show', $lead->property->slug) : ($lead->project ? route('projects.show', $lead->project->slug) : null),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'event' => ['event' => $event, 'lead_type' => $lead->type],
            ], $created ? 201 : 200);
        }

        return redirect()->route('thank-you')->with(self::CONFIRMATION_KEY, $payload);
    }

    private function throttled(Request $request, string $message): JsonResponse|RedirectResponse
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message], 429)
            : back()->withInput()->withErrors(['form' => $message]);
    }

    private function failed(Request $request, Throwable $exception): JsonResponse|RedirectResponse
    {
        Log::error('Lead capture failed', ['error' => class_basename($exception).': '.mb_strimwidth($exception->getMessage(), 0, 200)]);
        $message = 'We could not save your request. Please try again or call us.';

        return $request->expectsJson()
            ? response()->json(['message' => $message], 500)
            : back()->withInput()->withErrors(['form' => $message]);
    }
}
