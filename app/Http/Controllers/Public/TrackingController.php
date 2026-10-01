<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyMetricDaily;
use App\Support\ViewCounter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackingController extends Controller
{
    private const CTA_EVENTS = ['phone_click', 'whatsapp_click', 'property_cta_click'];

    /**
     * First-party CTA click counter for the KPI view. A click is a click, not a confirmed lead.
     */
    public function __invoke(Request $request): Response
    {
        $validated = $request->validate([
            'event' => ['required', 'string', 'in:'.implode(',', self::CTA_EVENTS)],
            'property_id' => ['nullable', 'integer'],
        ]);

        if (! empty($validated['property_id']) && ViewCounter::countable($request) && Property::query()->published()->whereKey($validated['property_id'])->exists()) {
            PropertyMetricDaily::record((int) $validated['property_id'], 'cta_clicks');
        }

        return response()->noContent();
    }
}
