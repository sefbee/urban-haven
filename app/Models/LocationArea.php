<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class LocationArea extends Model
{
    use HasSeo, HasSlug;

    public const DEFAULT_COUNTRY = 'Bangladesh';

    protected $fillable = ['name', 'slug', 'country', 'city', 'intro', 'lat', 'lng', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }

    /**
     * Location landing pages exist only when the owner has written useful local copy.
     */
    public function hasLandingPage(): bool
    {
        return $this->is_active && filled($this->intro);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->preventOverwrite();
    }

    public function label(): string
    {
        return $this->name.' — '.$this->city;
    }

    /**
     * Countries and their cities, for the country → city → area pickers.
     *
     * @param  iterable<LocationArea>  $areas
     * @return array<string, list<string>>
     */
    public static function placeTree(iterable $areas): array
    {
        return Collection::make($areas)
            ->groupBy(fn (LocationArea $area): string => $area->country ?: self::DEFAULT_COUNTRY)
            ->sortKeys()
            ->map(fn (Collection $group): array => $group->pluck('city')->filter()->unique()->sort()->values()->all())
            ->all();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
