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
     * @return array{brand: array{primary_1: string, primary_2: string, secondary_1: string, secondary_2: string, accent_1: string, accent_2: string, dominant: string, secondary: string, accent: string}, advanced: array<string, array<string, string>>}
     */
    public static function current(): array
    {
        $saved = Setting::get('theme_colors');

        if (! is_array($saved)) {
            $legacy = Setting::allValues();
            $saved = [
                'brand' => [
                    'primary_1' => $legacy['theme_surface'] ?? null,
                    'primary_2' => '#ffffff',
                    'secondary_1' => $legacy['theme_dark'] ?? null,
                    'secondary_2' => '#6e675f',
                    'accent_1' => $legacy['theme_primary'] ?? null,
                    'accent_2' => $legacy['theme_accent'] ?? '#c3a06a',
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
     * @return array{brand: array{primary_1: string, primary_2: string, secondary_1: string, secondary_2: string, accent_1: string, accent_2: string, dominant: string, secondary: string, accent: string}, advanced: array<string, array<string, string>>}
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

        $primary1 = self::color($brand['primary_1'] ?? $brand['dominant'] ?? null, '#f3f0ea');
        $primary2 = self::color($brand['primary_2'] ?? null, '#ffffff');
        $secondary1 = self::color($brand['secondary_1'] ?? $brand['secondary'] ?? null, '#0d1110');
        $secondary2 = self::color($brand['secondary_2'] ?? null, '#6e675f');
        $accent1 = self::color($brand['accent_1'] ?? $brand['accent'] ?? null, '#1a3328');
        $accent2 = self::color($brand['accent_2'] ?? null, '#c3a06a');

        return [
            'brand' => [
                'primary_1' => $primary1,
                'primary_2' => $primary2,
                'secondary_1' => $secondary1,
                'secondary_2' => $secondary2,
                'accent_1' => $accent1,
                'accent_2' => $accent2,
                // Backward-compatibility aliases
                'dominant' => $primary1,
                'secondary' => $secondary1,
                'accent' => $accent1,
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

        $primary1 = $brand['primary_1'];
        $primary2 = $brand['primary_2'];
        $secondary1 = $brand['secondary_1'];
        $secondary2 = $brand['secondary_2'];
        $accent1 = $brand['accent_1'];
        $accent2 = $brand['accent_2'];

        return [
            // 6-Color Palette Tokens (Abi Connick 60-30-10 Rule)
            '--color-primary-1' => $primary1,
            '--color-primary-2' => $primary2,
            '--color-secondary-1' => $secondary1,
            '--color-secondary-2' => $secondary2,
            '--color-accent-1' => $accent1,
            '--color-accent-2' => $accent2,

            // Core semantic mappings
            '--color-dominant' => $primary1,
            '--color-secondary' => $secondary1,
            '--color-accent' => $accent1,
            '--color-dominant-text' => '#1e293b',
            '--color-secondary-text' => '#ffffff',
            '--color-accent-hover' => 'color-mix(in srgb, var(--color-accent-1) 85%, black)',
            '--color-accent-2-hover' => 'color-mix(in srgb, var(--color-accent-2) 85%, black)',
            '--color-page-background' => $advanced['surfaces_text']['page_background'] ?: $primary1,
            '--color-card-surface' => $advanced['surfaces_text']['card_surface'] ?: $primary2,
            '--color-body-text' => $advanced['surfaces_text']['primary_text'] ?: $secondary1,
            '--color-muted-text' => $advanced['surfaces_text']['muted_text'] ?: $secondary2,
            '--color-divider' => $advanced['surfaces_text']['border'] ?: 'color-mix(in srgb, var(--color-secondary-2) 30%, var(--color-primary-1))',

            // Brand palette mappings
            '--color-brass' => $accent2,
            '--color-brass-ink' => 'color-mix(in srgb, var(--color-accent-2) 80%, black)',
            '--color-brass-soft' => 'color-mix(in srgb, var(--color-accent-2) 15%, var(--color-primary-2))',
            '--color-gold' => $accent2,
            '--color-gold-ink' => 'color-mix(in srgb, var(--color-accent-2) 80%, black)',
            '--color-gold-soft' => 'color-mix(in srgb, var(--color-accent-2) 15%, var(--color-primary-1))',
            '--color-forest' => $accent1,
            '--color-moss' => $accent1,
            '--color-emerald' => $accent1,
            '--color-emerald-bright' => 'var(--color-accent-hover)',
            '--color-sand' => $primary1,
            '--color-cream' => $primary1,
            '--color-paper' => $primary2,
            '--color-limestone' => $primary2,
            '--color-night' => $secondary1,
            '--color-ink' => $secondary1,
            '--color-line' => 'var(--color-divider)',
            '--color-line-strong' => 'color-mix(in srgb, var(--color-divider) 65%, var(--color-secondary-1))',
            '--color-muted' => $secondary2,

            // Hero & Slider
            '--color-hero-overlay' => $advanced['hero_slider']['overlay_color'] ?: $secondary1,
            '--color-hero-overlay-opacity' => $advanced['hero_slider']['overlay_opacity'] !== '' ? $advanced['hero_slider']['overlay_opacity'] : '0.3',
            '--color-slider-title' => $advanced['hero_slider']['slider_title'] ?: 'var(--color-body-text)',
            '--color-slider-subtitle' => $advanced['hero_slider']['slider_subtitle'] ?: 'var(--color-muted-text)',
            '--color-carousel-arrow' => $advanced['hero_slider']['carousel_arrow'] ?: $accent1,
            '--color-carousel-dot' => $advanced['hero_slider']['carousel_dot'] ?: $accent1,

            // Mobile Navigation
            '--color-mobile-nav-background' => $advanced['mobile_nav']['drawer_background'] ?: $primary2,
            '--color-mobile-active-highlight' => $advanced['mobile_nav']['active_tab_highlight'] ?: $accent1,
            '--color-notification-badge' => $advanced['mobile_nav']['badge_fill'] ?: $accent2,
        ];
    }

    private static function color(mixed $value, string $fallback): string
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)
            ? strtolower($value)
            : $fallback;
    }
}
