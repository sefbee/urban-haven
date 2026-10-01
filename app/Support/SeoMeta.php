<?php

namespace App\Support;

use App\Models\CmsPage;
use App\Models\Post;
use App\Models\Project;
use App\Models\Property;

final class SeoMeta
{
    /**
     * @param  array<string, mixed>  $options  canonical, type, noindex, json_ld (list of schema arrays)
     * @return array{title: string, description: ?string, image: ?string, noindex: bool, canonical: string, type: string, json_ld: list<array<string, mixed>>}
     */
    public static function for(Property|Project|CmsPage|Post|null $model, string $fallbackTitle, ?string $fallbackDescription = null, array $options = []): array
    {
        $override = $model?->seoOverride;

        $title = $override?->meta_title
            ?: ($model->meta_title ?? null)
            ?: ($model->title ?? $model->name ?? $fallbackTitle);

        $description = $override?->meta_description
            ?: ($model->meta_description ?? null)
            ?: $fallbackDescription;

        $image = $override?->og_image_path;
        if (! $image && ($model instanceof Property || $model instanceof Project || $model instanceof Post)) {
            $image = $model->featuredImage()?->url(1280);
        }

        return [
            'title' => (string) $title,
            'description' => $description ? mb_strimwidth(trim(preg_replace('/\s+/', ' ', strip_tags($description)) ?? ''), 0, 160, '…') : null,
            'image' => $image,
            'noindex' => (bool) ($options['noindex'] ?? false) || (bool) ($override?->noindex),
            'canonical' => $options['canonical'] ?? self::canonical(),
            'type' => $options['type'] ?? ($model instanceof Post ? 'article' : 'website'),
            'json_ld' => array_values(array_filter($options['json_ld'] ?? [])),
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
