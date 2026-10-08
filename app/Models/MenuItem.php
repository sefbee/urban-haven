<?php

namespace App\Models;

use App\Support\TaggedCache;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class MenuItem extends Model
{
    public const LOCATIONS = ['header' => 'Header navigation', 'footer' => 'Footer links'];

    protected $fillable = ['location', 'parent_id', 'label', 'url', 'opens_new_tab', 'sort_order', 'is_visible'];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'opens_new_tab' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::saved(fn () => TaggedCache::flush(['menus']));
        static::deleted(fn () => TaggedCache::flush(['menus']));
    }

    /**
     * Visible top-level links of a menu, in order.
     *
     * @return Collection<int, MenuItem>
     */
    public static function forLocation(string $location): Collection
    {
        return self::visibleRows($location)->whereNull('parent_id')->values();
    }

    /**
     * Visible top-level links with their visible submenu links attached as `children`.
     *
     * @return Collection<int, MenuItem>
     */
    public static function treeFor(string $location): Collection
    {
        $rows = self::visibleRows($location);
        $children = $rows->whereNotNull('parent_id')->groupBy('parent_id');

        return $rows->whereNull('parent_id')->values()->each(
            fn (self $item) => $item->setRelation('children', $children->get($item->id, new EloquentCollection)->values())
        );
    }

    /**
     * @return EloquentCollection<int, MenuItem>
     */
    private static function visibleRows(string $location): EloquentCollection
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
