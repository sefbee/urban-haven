<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * The search engine fields every public record shares: focus keyword, meta title,
 * meta description, permalink (slug), Search Console code and the noindex switch.
 */
final class SeoFields
{
    public const TITLE_MAX = 70;

    public const DESCRIPTION_MAX = 170;

    public const SLUG_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /**
     * Paths the CMS catch-all route must never claim.
     */
    public const RESERVED_SLUGS = ['admin', 'properties', 'projects', 'locations', 'articles', 'faq', 'shortlist', 'compare', 'map', 'tools', 'legal', 'search', 'saved', 'media', 'thank-you', 'sitemap', 'sitemaps', 'robots', 'login', 'logout', 'storage', 'build', 'up'];

    /**
     * Form input name => seo_overrides column.
     *
     * @var array<string, string>
     */
    public const INPUTS = [
        'seo_focus_keyword' => 'focus_keyword',
        'seo_meta_title' => 'meta_title',
        'seo_meta_description' => 'meta_description',
        'seo_gsc_code' => 'gsc_code',
        'seo_noindex' => 'noindex',
    ];

    /**
     * @return array<string, list<mixed>>
     */
    public static function rules(): array
    {
        return [
            'seo_focus_keyword' => ['nullable', 'string', 'max:80'],
            'seo_meta_title' => ['nullable', 'string', 'max:'.self::TITLE_MAX],
            'seo_meta_description' => ['nullable', 'string', 'max:'.self::DESCRIPTION_MAX],
            'seo_gsc_code' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'seo_noindex' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  list<string>  $reserved
     * @return list<mixed>
     */
    public static function slugRules(string $table, ?Model $ignore = null, array $reserved = [], string $column = 'slug'): array
    {
        return array_values(array_filter([
            'nullable',
            'string',
            'max:120',
            'regex:'.self::SLUG_PATTERN,
            Rule::unique($table, $column)->ignore($ignore?->getKey()),
            $reserved !== [] ? Rule::notIn($reserved) : null,
        ]));
    }

    /**
     * Turns whatever was typed into a clean permalink, or null so one is generated from the title.
     */
    public static function normaliseSlug(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $slug = Str::slug(Str::afterLast(trim($value, " /\t\n"), '/'));

        return $slug === '' ? null : Str::limit($slug, 120, '');
    }

    /**
     * A slug from the given text that is not yet used in the table column, e.g. "lift", "lift-2".
     */
    public static function uniqueSlug(string $table, string $column, string $source, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($source), 45, '') ?: 'item';
        $candidate = $base;
        $suffix = 2;

        while (DB::table($table)->where($column, $candidate)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    /**
     * @return list<string>
     */
    public static function inputNames(): array
    {
        return array_keys(self::INPUTS);
    }

    /**
     * Saves the SEO inputs found in the validated data. Records without any SEO input are left alone.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function sync(Model $model, array $validated): void
    {
        if (array_intersect_key($validated, self::INPUTS) === [] || ! method_exists($model, 'seoOverride')) {
            return;
        }

        $values = [];

        foreach (self::INPUTS as $input => $column) {
            if ($column === 'noindex') {
                $values[$column] = (bool) ($validated[$input] ?? false);

                continue;
            }

            $value = $validated[$input] ?? null;
            $values[$column] = is_string($value) && trim($value) !== '' ? trim(strip_tags($value)) : null;
        }

        $model->seoOverride()->updateOrCreate([], [...$values, 'updated_at' => now()]);
    }
}
