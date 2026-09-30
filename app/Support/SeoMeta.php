<?php

namespace App\Support;

use App\Models\CmsPage;
use App\Models\Project;
use App\Models\Property;

final class SeoMeta
{
    /**
     * @return array{title: string, description: ?string, image: ?string, noindex: bool}
     */
    public static function for(Property|Project|CmsPage|null $model, string $fallbackTitle, ?string $fallbackDescription = null): array
    {
        $override = $model?->seoOverride;

        $title = $override?->meta_title
            ?: ($model->meta_title ?? null)
            ?: ($model->title ?? $model->name ?? $fallbackTitle);

        $description = $override?->meta_description
            ?: ($model->meta_description ?? null)
            ?: $fallbackDescription;

        $image = $override?->og_image_path;
        if (! $image && ($model instanceof Property || $model instanceof Project)) {
            $image = $model->featuredImage()?->url(1280);
        }

        return [
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'noindex' => (bool) ($override?->noindex),
        ];
    }
}
