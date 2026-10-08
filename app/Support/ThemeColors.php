<?php

namespace App\Support;

use App\Models\Setting;

final class ThemeColors
{
    /**
     * The keys available in each advanced theme group.
     *
     * @var array<string, array<string, array{label: string, type: string}>>
     */
    public const ADVANCED_FIELDS = [
        'surfaces_text' => [
            'page_background' => ['label' => 'Page background', 'type' => 'color'],
            'card_surface' => ['label' => 'Card surface', 'type' => 'color'],
            'primary_text' => ['label' => 'Primary text', 'type' => 'color'],
            'muted_text' => ['label' => 'Muted text', 'type' => 'color'],
            'border' => ['label' => 'Borders and dividers', 'type' => 'color'],
        ],
        'hero_slider' => [
            'overlay_color' => ['label' => 'Hero overlay', 'type' => 'color'],
            'overlay_opacity' => ['label' => 'Overlay opacity', 'type' => 'opacity'],
            'slider_title' => ['label' => 'Slider title', 'type' => 'color'],
            'slider_subtitle' => ['label' => 'Slider subtitle', 'type' => 'color'],
            'carousel_arrow' => ['label' => 'Carousel arrows', 'type' => 'color'],
            'carousel_dot' => ['label' => 'Carousel dots', 'type' => 'color'],
        ],
        'mobile_nav' => [
            'drawer_background' => ['label' => 'Mobile menu background', 'type' => 'color'],
            'active_tab_highlight' => ['label' => 'Active navigation highlight', 'type' => 'color'],
            'badge_fill' => ['label' => 'Notification badge', 'type' => 'color'],
        ],
    ];

    /**
     * @return array{brand: array{dominant: string, secondary: string, accent: string}, advanced: array<string, array<string, string>>}
     */
    public static function current(): array
    {
        $saved = Setting::get('theme_colors');

        if (! is_array($saved)) {
            $legacy = Setting::allValues();
            $saved = [
                'brand' => [
                    'dominant' => $legacy['theme_surface'] ?? null,
                    'secondary' => $legacy['theme_dark'] ?? null,
                    'accent' => $legacy['theme_primary'] ?? null,
                ],
            ];
        }

        return self::normalize($saved);
    }

    /**
     * @param  array<string, mixed>  $colors
     * @return array{brand: array{dominant: string, secondary: string, accent: string}, advanced: array<string, array<string, string>>}
     */
    public static function normalize(array $colors): array
    {
        $brand = is_array($colors['brand'] ?? null) ? $colors['brand'] : [];
        $advanced = is_array($colors['advanced'] ?? null) ? $colors['advanced'] : [];
        $normalizedAdvanced = [];

        foreach (self::ADVANCED_FIELDS as $group => $fields) {
            $values = is_array($advanced[$group] ?? null) ? $advanced[$group] : [];

            foreach ($fields as $key => $definition) {
                $value = $values[$key] ?? '';
                $normalizedAdvanced[$group][$key] = $definition['type'] === 'opacity'
                    ? (is_numeric($value) && (float) $value >= 0 && (float) $value <= 1 ? (string) $value : '')
                    : (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : '');
            }
        }

        return [
            'brand' => [
                'dominant' => self::color($brand['dominant'] ?? null, '#f3f0ea'),
                'secondary' => self::color($brand['secondary'] ?? null, '#0d1110'),
                'accent' => self::color($brand['accent'] ?? null, '#1a3328'),
            ],
            'advanced' => $normalizedAdvanced,
        ];
    }

    /**
     * Keep cleared override groups as JSON objects in storage.
     *
     * @param  array<string, mixed>  $colors
     * @return array<string, mixed>
     */
    public static function forStorage(array $colors): array
    {
        $theme = self::normalize($colors);

        foreach ($theme['advanced'] as $group => $fields) {
            $overrides = array_filter($fields, fn (string $value): bool => $value !== '');
            $theme['advanced'][$group] = $overrides === [] ? (object) [] : $overrides;
        }

        return $theme;
    }

    /**
     * @param  array<string, mixed>|null  $colors
     * @return array<string, string>
     */
    public static function cssVariables(?array $colors = null): array
    {
        $theme = self::normalize($colors ?? self::current());
        $brand = $theme['brand'];
        $advanced = $theme['advanced'];

        return [
            '--color-dominant' => $brand['dominant'],
            '--color-secondary' => $brand['secondary'],
            '--color-accent' => $brand['accent'],
            '--color-dominant-text' => '#1e293b',
            '--color-secondary-text' => '#ffffff',
            '--color-accent-hover' => 'color-mix(in srgb, var(--color-accent) 85%, black)',
            '--color-page-background' => $advanced['surfaces_text']['page_background'] ?: $brand['dominant'],
            '--color-card-surface' => $advanced['surfaces_text']['card_surface'] ?: $brand['dominant'],
            '--color-body-text' => $advanced['surfaces_text']['primary_text'] ?: $brand['secondary'],
            '--color-muted-text' => $advanced['surfaces_text']['muted_text'] ?: 'color-mix(in srgb, var(--color-body-text) 58%, white)',
            '--color-divider' => $advanced['surfaces_text']['border'] ?: 'color-mix(in srgb, var(--color-secondary) 12%, var(--color-dominant))',
            '--color-hero-overlay' => $advanced['hero_slider']['overlay_color'] ?: $brand['secondary'],
            '--color-hero-overlay-opacity' => $advanced['hero_slider']['overlay_opacity'] !== '' ? $advanced['hero_slider']['overlay_opacity'] : '0.3',
            '--color-slider-title' => $advanced['hero_slider']['slider_title'] ?: 'var(--color-body-text)',
            '--color-slider-subtitle' => $advanced['hero_slider']['slider_subtitle'] ?: 'var(--color-muted-text)',
            '--color-carousel-arrow' => $advanced['hero_slider']['carousel_arrow'] ?: $brand['accent'],
            '--color-carousel-dot' => $advanced['hero_slider']['carousel_dot'] ?: $brand['accent'],
            '--color-mobile-nav-background' => $advanced['mobile_nav']['drawer_background'] ?: $brand['dominant'],
            '--color-mobile-active-highlight' => $advanced['mobile_nav']['active_tab_highlight'] ?: $brand['accent'],
            '--color-notification-badge' => $advanced['mobile_nav']['badge_fill'] ?: $brand['accent'],
        ];
    }

    private static function color(mixed $value, string $fallback): string
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)
            ? strtolower($value)
            : $fallback;
    }
}
