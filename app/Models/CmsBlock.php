<?php

namespace App\Models;

use App\Support\TaggedCache;
use Illuminate\Database\Eloquent\Model;

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
}
