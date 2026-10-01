<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class LeadAttribution
{
    public const SESSION_KEY = 'uh_attr';

    private const UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign'];

    /**
     * Stores first-touch campaign data for the visit; a new tagged campaign click replaces it.
     */
    public static function remember(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $hasCampaign = collect(self::UTM_KEYS)->contains(fn (string $key): bool => $request->filled($key));

        if ($request->session()->has(self::SESSION_KEY) && ! $hasCampaign) {
            return;
        }

        $request->session()->put(self::SESSION_KEY, [
            'utm_source' => self::clean($request->query('utm_source')),
            'utm_medium' => self::clean($request->query('utm_medium')),
            'utm_campaign' => self::clean($request->query('utm_campaign')),
            'landing_url' => Str::limit($request->fullUrl(), 500, ''),
            'referrer' => self::externalReferrer($request),
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{utm_source: ?string, utm_medium: ?string, utm_campaign: ?string, landing_url: ?string, referrer: ?string}
     */
    public static function fromRequest(Request $request, array $validated = []): array
    {
        $stored = $request->hasSession() ? (array) $request->session()->get(self::SESSION_KEY, []) : [];

        $value = fn (string $key): ?string => self::clean($stored[$key] ?? null) ?? self::clean($validated[$key] ?? null);

        return [
            'utm_source' => $value('utm_source'),
            'utm_medium' => $value('utm_medium'),
            'utm_campaign' => $value('utm_campaign'),
            'landing_url' => isset($stored['landing_url']) ? Str::limit((string) $stored['landing_url'], 500, '') : self::clean($validated['landing_url'] ?? null, 500),
            'referrer' => isset($stored['referrer']) ? self::clean($stored['referrer'], 500) : self::clean($validated['referrer'] ?? null, 500),
        ];
    }

    private static function externalReferrer(Request $request): ?string
    {
        $referrer = (string) $request->headers->get('referer', '');

        if ($referrer === '' || parse_url($referrer, PHP_URL_HOST) === $request->getHost()) {
            return null;
        }

        return Str::limit($referrer, 500, '');
    }

    private static function clean(mixed $value, int $max = 120): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(strip_tags($value));

        return $value === '' ? null : Str::limit($value, $max, '');
    }
}
