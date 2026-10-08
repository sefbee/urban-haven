<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets settings saved in the admin override the matching environment values for this
 * request, so tracking IDs and verification codes can change without a deploy.
 */
class ApplySiteSettings
{
    /**
     * @var array<string, string>
     */
    private const OVERRIDES = [
        'analytics_gtm_id' => 'urbanhaven.analytics.gtm_id',
        'analytics_ga4_id' => 'urbanhaven.analytics.ga4_id',
        'analytics_meta_pixel_id' => 'urbanhaven.analytics.meta_pixel_id',
        'analytics_google_ads_id' => 'urbanhaven.analytics.google_ads_id',
        'seo_google_verification' => 'urbanhaven.seo.google_site_verification',
    ];

    private const TRACKING_KEYS = ['urbanhaven.analytics.gtm_id', 'urbanhaven.analytics.ga4_id', 'urbanhaven.analytics.meta_pixel_id', 'urbanhaven.analytics.google_ads_id'];

    public function handle(Request $request, Closure $next): Response
    {
        $settings = rescue(fn (): array => Setting::allValues(), [], report: false);

        foreach (self::OVERRIDES as $key => $configKey) {
            if (filled($settings[$key] ?? null)) {
                config([$configKey => $settings[$key]]);
            }
        }

        if (array_key_exists('analytics_enabled', $settings) && ! $settings['analytics_enabled']) {
            config(array_fill_keys(self::TRACKING_KEYS, null));
        }

        return $next($request);
    }
}
