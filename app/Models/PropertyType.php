<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyType extends Model
{
    public const PROFILE_APARTMENT = 'apartment';

    public const PROFILE_PLOT = 'plot';

    public const PROFILE_COMMERCIAL = 'commercial';

    public const PROFILES = [self::PROFILE_APARTMENT, self::PROFILE_PLOT, self::PROFILE_COMMERCIAL];

    public const CATEGORY_RESIDENTIAL = 'residential';

    public const CATEGORY_COMMERCIAL = 'commercial';

    /**
     * The two main types. Every property type is a sub-type of one of them.
     */
    public const CATEGORIES = [self::CATEGORY_RESIDENTIAL, self::CATEGORY_COMMERCIAL];

    protected $fillable = ['key', 'label', 'category', 'field_profile', 'is_active'];

    /**
     * @return array<string, string>
     */
    public static function categoryLabels(): array
    {
        return [
            self::CATEGORY_RESIDENTIAL => __('Residential'),
            self::CATEGORY_COMMERCIAL => __('Commercial'),
        ];
    }

    public function categoryLabel(): string
    {
        return self::categoryLabels()[$this->category] ?? ucfirst((string) $this->category);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function hasResidentialFields(): bool
    {
        return $this->field_profile !== self::PROFILE_PLOT;
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
