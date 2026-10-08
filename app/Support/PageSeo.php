<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Validation\Rule;

/**
 * Search engine settings for the website's fixed pages, which have no database record of
 * their own. Stored as one JSON setting keyed by route name.
 */
final class PageSeo
{
    public const SETTING = 'page_seo';

    /**
     * Route name => label shown in the admin, plus the public path for the preview.
     *
     * @var array<string, array{label: string, path: string, hint: string, title: string}>
     */
    public const PAGES = [
        'home' => ['label' => 'Home page', 'path' => '/', 'title' => '', 'hint' => 'The most important page for Google. Use your main keyword, e.g. “apartments for sale in Dhaka”.'],
        'properties.index' => ['label' => 'Property search', 'path' => '/properties', 'title' => 'Properties for sale and rent', 'hint' => 'Applies to the unfiltered search page. Filtered searches build their own titles.'],
        'projects.index' => ['label' => 'Property projects', 'path' => '/projects', 'title' => 'Property projects', 'hint' => 'The list of published property developments.'],
        'map' => ['label' => 'Map of properties', 'path' => '/map', 'title' => 'Map of properties', 'hint' => 'The full-screen map of listings.'],
        'articles.index' => ['label' => 'Articles', 'path' => '/articles', 'title' => 'Guides and articles', 'hint' => 'The list of all guides and articles. Category pages use the category’s own settings.'],
        'faq' => ['label' => 'Frequently asked questions', 'path' => '/faq', 'title' => 'Frequently asked questions', 'hint' => 'The FAQ page.'],
        'tools' => ['label' => 'Tools', 'path' => '/tools', 'title' => 'Tools', 'hint' => 'Valuation request and EMI calculator.'],
        'legal' => ['label' => 'Legal services', 'path' => '/legal', 'title' => 'Legal Services', 'hint' => 'Transaction support page.'],
    ];

    /**
     * @return array<string, array{focus_keyword: ?string, meta_title: ?string, meta_description: ?string, og_image_path: ?string, gsc_code: ?string, noindex: bool}>
     */
    public static function all(): array
    {
        $stored = Setting::get(self::SETTING, []);
        $stored = is_array($stored) ? $stored : [];

        $pages = [];

        foreach (array_keys(self::PAGES) as $route) {
            $values = is_array($stored[$route] ?? null) ? $stored[$route] : [];
            $pages[$route] = [
                'focus_keyword' => self::text($values['focus_keyword'] ?? null),
                'meta_title' => self::text($values['meta_title'] ?? null),
                'meta_description' => self::text($values['meta_description'] ?? null),
                'og_image_path' => self::text($values['og_image_path'] ?? null),
                'gsc_code' => self::text($values['gsc_code'] ?? null),
                'noindex' => (bool) ($values['noindex'] ?? false),
            ];
        }

        return $pages;
    }

    /**
     * Settings for the page being rendered, or null when it is not a fixed page or the URL
     * carries filters (a filtered search is a different page for search engines).
     *
     * @return array{focus_keyword: ?string, meta_title: ?string, meta_description: ?string, og_image_path: ?string, gsc_code: ?string, noindex: bool}|null
     */
    public static function current(): ?array
    {
        $route = request()->route()?->getName();

        if (! is_string($route) || ! isset(self::PAGES[$route])) {
            return null;
        }

        $filters = collect(request()->query())->except(['page', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid']);

        if ($filters->isNotEmpty()) {
            return null;
        }

        return self::all()[$route];
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function rules(): array
    {
        $rules = ['pages' => ['required', 'array'], 'pages.*' => ['array']];

        foreach (SeoFields::rules() as $input => $fieldRules) {
            $rules['pages.*.'.$input] = $fieldRules;
        }

        $rules['pages'][] = function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_array($value) && array_diff(array_keys($value), array_keys(self::PAGES)) !== []) {
                $fail('Unknown page.');
            }
        };

        return $rules + ['route' => ['nullable', Rule::in(array_keys(self::PAGES))]];
    }

    /**
     * @param  array<string, array<string, mixed>>  $pages  keyed by route name, using the seo_* input names
     */
    public static function save(array $pages): void
    {
        $current = self::all();

        foreach ($pages as $route => $inputs) {
            if (! isset(self::PAGES[$route])) {
                continue;
            }

            foreach (SeoFields::INPUTS as $input => $column) {
                $current[$route][$column] = $column === 'noindex'
                    ? (bool) ($inputs[$input] ?? false)
                    : self::text($inputs[$input] ?? null);
            }
        }

        Setting::set(self::SETTING, $current, 'seo', 'json');
    }

    private static function text(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim(strip_tags($value)) : null;
    }
}
