<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Structured social profiles. The public site and structured data read the flat
 * `social_links` list, so every save mirrors the active profiles into it.
 */
final class SocialProfiles
{
    public const MAX = 12;

    /**
     * @var array<string, string>
     */
    public const PLATFORMS = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
        'x' => 'X (Twitter)',
        'tiktok' => 'TikTok',
        'pinterest' => 'Pinterest',
        'website' => 'Other link',
    ];

    /**
     * @return list<array{platform: string, label: string, url: string, active: bool, new_tab: bool}>
     */
    public static function all(): array
    {
        $stored = Setting::get('social_profiles');

        if (is_array($stored)) {
            return array_values(array_map(fn (array $profile): array => [
                'platform' => (string) ($profile['platform'] ?? 'website'),
                'label' => (string) ($profile['label'] ?? ''),
                'url' => (string) ($profile['url'] ?? ''),
                'active' => (bool) ($profile['active'] ?? true),
                'new_tab' => (bool) ($profile['new_tab'] ?? true),
            ], array_filter($stored, 'is_array')));
        }

        return array_map(fn (string $url): array => [
            'platform' => self::detect($url),
            'label' => '',
            'url' => $url,
            'active' => true,
            'new_tab' => true,
        ], array_values(array_filter((array) Setting::get('social_links', []), 'is_string')));
    }

    /**
     * Active profiles in display order, for the public site.
     *
     * @return list<array{platform: string, label: string, url: string, active: bool, new_tab: bool}>
     */
    public static function active(): array
    {
        return array_values(array_filter(self::all(), fn (array $profile): bool => $profile['active']));
    }

    /**
     * @param  list<array{platform: string, label?: string|null, url: string, active?: bool|string|null, new_tab?: bool|string|null}>  $profiles
     */
    public static function save(array $profiles): void
    {
        $clean = array_values(array_map(fn (array $profile): array => [
            'platform' => $profile['platform'],
            'label' => trim((string) ($profile['label'] ?? '')),
            'url' => trim($profile['url']),
            'active' => filter_var($profile['active'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'new_tab' => filter_var($profile['new_tab'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ], $profiles));

        Setting::set('social_profiles', $clean, 'social', 'json');
        Setting::set('social_links', array_values(array_unique(array_column(array_filter($clean, fn (array $profile): bool => $profile['active']), 'url'))), 'social', 'json');
    }

    public static function detect(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return match (true) {
            str_contains($host, 'facebook.') || str_contains($host, 'fb.') => 'facebook',
            str_contains($host, 'instagram.') => 'instagram',
            str_contains($host, 'linkedin.') => 'linkedin',
            str_contains($host, 'youtube.') || str_contains($host, 'youtu.be') => 'youtube',
            $host === 'x.com' || str_contains($host, 'twitter.') => 'x',
            str_contains($host, 'tiktok.') => 'tiktok',
            str_contains($host, 'pinterest.') => 'pinterest',
            default => 'website',
        };
    }
}
