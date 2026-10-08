<?php

namespace App\Support;

use App\Models\Post;
use App\Models\Project;
use App\Models\Property;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class SeoMeta
{
    /**
     * @param  array<string, mixed>  $options  canonical, type, noindex, json_ld (list of schema arrays)
     * @return array{title: string, description: ?string, image: ?string, noindex: bool, canonical: string, type: string, json_ld: list<array<string, mixed>>, verification: ?string, focus_keyword: ?string}
     */
    public static function for(?Model $model, string $fallbackTitle, ?string $fallbackDescription = null, array $options = []): array
    {
        $override = $model && method_exists($model, 'seoOverride') ? $model->seoOverride : null;
        $page = $model === null ? PageSeo::current() : null;

        $title = $override?->meta_title
            ?: ($page['meta_title'] ?? null)
            ?: ($model->meta_title ?? null)
            ?: $fallbackTitle;

        if ($title === config('app.name') && filled($defaultTitle = Setting::get('seo_default_title'))) {
            $title = $defaultTitle;
        }

        $description = $override?->meta_description
            ?: ($page['meta_description'] ?? null)
            ?: ($model->meta_description ?? null)
            ?: $fallbackDescription
            ?: Setting::get('seo_default_description');

        $image = $override?->og_image_path ?: ($page['og_image_path'] ?? null);
        if (! $image && ($model instanceof Property || $model instanceof Project || $model instanceof Post)) {
            $image = $model->featuredImage()?->url(1280);
        }
        if (! $image && filled($brandImage = Setting::get('brand_og_image'))) {
            $image = Storage::disk('public')->url($brandImage);
        }

        return [
            'title' => (string) $title,
            'description' => $description ? mb_strimwidth(trim(preg_replace('/\s+/', ' ', strip_tags($description)) ?? ''), 0, 160, '…') : null,
            'image' => $image,
            'noindex' => (bool) ($options['noindex'] ?? false) || (bool) ($override?->noindex) || (bool) ($page['noindex'] ?? false),
            'canonical' => $options['canonical'] ?? self::canonical(),
            'type' => $options['type'] ?? ($model instanceof Post ? 'article' : 'website'),
            'json_ld' => array_values(array_filter($options['json_ld'] ?? [])),
            'verification' => $override?->gsc_code ?: ($page['gsc_code'] ?? null),
            'focus_keyword' => $override?->focus_keyword ?: ($page['focus_keyword'] ?? null),
        ];
    }

    /**
     * Canonical URLs never carry tracking or filter parameters, except the allowlisted keys.
     *
     * @param  list<string>  $keep
     */
    public static function canonical(array $keep = []): string
    {
        $query = collect(request()->query())
            ->only($keep)
            ->filter(fn (mixed $value): bool => is_scalar($value) && $value !== '')
            ->reject(fn (mixed $value, string $key): bool => $key === 'page' && (int) $value <= 1)
            ->sortKeys()
            ->all();

        return url()->current().($query === [] ? '' : '?'.http_build_query($query));
    }
}
