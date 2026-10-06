<?php

namespace App\Models;

use App\Support\TaggedCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CmsBlock extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'label',
        'content',
        'draft_content',
        'is_visible',
        'sort_order',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'draft_content' => 'array',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Published content of a visible block, or null when the block is missing or hidden.
     *
     * @return array<string, mixed>|null
     */
    public static function contentFor(string $key): ?array
    {
        return TaggedCache::remember(['cms', 'homepage'], 'block:'.$key, 3600, function () use ($key): ?array {
            $block = self::query()->where('key', $key)->where('is_visible', true)->first();

            return $block?->content ?: null;
        });
    }

    public function hasDraft(): bool
    {
        return $this->draft_content !== null;
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Blocks that carry uploaded images (the homepage hero background).
     */
    public function acceptsImage(): bool
    {
        return $this->key === 'hero';
    }

    /**
     * @return Collection<int, Media>
     */
    public function images(): Collection
    {
        return $this->media->filter(fn (Media $media): bool => $media->collection === 'gallery' && $media->is_public)->values();
    }

    /**
     * Public images of a visible block in display order, empty when none have been uploaded.
     *
     * @return Collection<int, Media>
     */
    public static function imagesFor(string $key): Collection
    {
        return self::query()->with('media')->where('key', $key)->where('is_visible', true)->first()?->images() ?? new Collection;
    }
}
