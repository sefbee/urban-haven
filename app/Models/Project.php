<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Project extends Model
{
    use HasMedia, HasSlug, Publishable;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'development_stage',
        'city',
        'location_area_id',
        'lat',
        'lng',
        'developer_name',
        'completion_date',
        'handover_info',
        'amenity_ids',
        'highlights',
        'trust_label',
        'is_featured',
        'featured_media_id',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'completion_date' => 'date',
            'amenity_ids' => 'array',
            'highlights' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Project $project): void {
            $project->publicationState()->create(['status' => PublicationState::DRAFT]);
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->preventOverwrite();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function locationArea(): BelongsTo
    {
        return $this->belongsTo(LocationArea::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
