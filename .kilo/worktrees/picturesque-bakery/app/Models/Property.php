<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\Publishable;
use App\Support\AreaConverter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Property extends Model
{
    use HasMedia, HasSlug, Publishable;

    protected $fillable = [
        'slug',
        'reference',
        'project_id',
        'property_type_id',
        'location_area_id',
        'title',
        'description',
        'availability',
        'listing_type',
        'price',
        'price_basis',
        'area_value',
        'area_unit',
        'area_sqft',
        'bedrooms',
        'bathrooms',
        'floor_number',
        'is_furnished',
        'amenity_ids',
        'lat',
        'lng',
        'map_approximation',
        'video_url',
        'virtual_tour_url',
        'trust_label',
        'is_featured',
        'last_updated_at',
        'featured_media_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'area_value' => 'decimal:2',
            'area_sqft' => 'decimal:2',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'is_furnished' => 'boolean',
            'is_featured' => 'boolean',
            'amenity_ids' => 'array',
            'last_updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Property $property): void {
            if ($property->area_value !== null && filled($property->area_unit)) {
                $property->area_sqft = AreaConverter::toSqft($property->area_value, $property->area_unit);
            }

            if (is_array($property->amenity_ids)) {
                $property->amenity_ids = array_values(array_map('intval', $property->amenity_ids));
            }

            $property->last_updated_at = now();
        });

        static::created(function (Property $property): void {
            $property->publicationState()->create(['status' => PublicationState::DRAFT]);
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->preventOverwrite();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function locationArea(): BelongsTo
    {
        return $this->belongsTo(LocationArea::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function amenities(): Collection
    {
        $ids = $this->amenity_ids ?? [];

        if ($ids === []) {
            return collect();
        }

        return Amenity::query()->whereIn('id', $ids)->get();
    }
}
