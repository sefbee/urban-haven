<?php

namespace App\Support;

use App\Models\PropertyMetricDaily;
use Illuminate\Http\Request;
use Throwable;

/**
 * First-party view counts for the dashboard. Counts at most one view per listing per
 * session per day and ignores signed-in staff and obvious crawlers.
 */
final class ViewCounter
{
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|preview|facebookexternalhit|headless|lighthouse|curl|wget|python/i';

    public static function countable(Request $request): bool
    {
        if ($request->user() !== null) {
            return false;
        }

        return preg_match(self::BOT_PATTERN, (string) $request->userAgent()) !== 1;
    }

    public static function recordPropertyView(Request $request, int $propertyId): void
    {
        if (! self::countable($request) || ! $request->hasSession()) {
            return;
        }

        $key = 'uh_viewed.'.now()->format('Ymd').'.'.$propertyId;

        if ($request->session()->has($key)) {
            return;
        }

        $request->session()->put($key, true);

        try {
            PropertyMetricDaily::record($propertyId, 'views');
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
