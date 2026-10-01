<?php

namespace App\Models;

use App\Support\TaggedCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MenuItem extends Model
{
    public const LOCATIONS = ['header' => 'Header navigation', 'footer' => 'Footer links'];

    protected $fillable = ['location', 'label', 'url', 'sort_order', 'is_visible'];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => TaggedCache::flush(['menus']));
        static::deleted(fn () => TaggedCache::flush(['menus']));
    }

    /**
     * @return Collection<int, MenuItem>
     */
    public static function forLocation(string $location): Collection
    {
        $rows = TaggedCache::remember(['menus'], 'menu.rows:'.$location, 3600, fn (): array => self::query()
            ->where('location', $location)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (self $item): array => $item->getAttributes())
            ->all());

        return self::hydrate($rows);
    }

    public function isInternal(): bool
    {
        return str_starts_with($this->url, '/') && ! str_starts_with($this->url, '//');
    }
}
