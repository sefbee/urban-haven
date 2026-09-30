<?php

namespace App\Models\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMedia
{
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    public function featuredImage(): ?Media
    {
        if ($this->featured_media_id) {
            return $this->media->firstWhere('id', $this->featured_media_id) ?? $this->media->first();
        }

        return $this->media->first();
    }
}
