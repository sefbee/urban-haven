<?php

namespace App\Models\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasMedia
{
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Images cleared for public display: gallery collection, public, with alt text.
     *
     * @return Collection<int, Media>
     */
    public function galleryImages(): Collection
    {
        return $this->publicCollection('gallery');
    }

    /**
     * @return Collection<int, Media>
     */
    public function floorPlans(): Collection
    {
        return $this->publicCollection('floor_plan');
    }

    /**
     * @return Collection<int, Media>
     */
    public function brochures(): Collection
    {
        return $this->publicCollection('brochure');
    }

    public function featuredImage(): ?Media
    {
        $images = $this->galleryImages();

        if ($this->featured_media_id) {
            return $images->firstWhere('id', $this->featured_media_id) ?? $images->first();
        }

        return $images->first();
    }

    /**
     * @return Collection<int, Media>
     */
    private function publicCollection(string $collection): Collection
    {
        return $this->media
            ->filter(fn (Media $media): bool => $media->collection === $collection && $media->is_public)
            ->values();
    }
}
