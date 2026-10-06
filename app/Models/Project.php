<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\Publishable;
use App\Support\PhoneNumber;
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
        'address',
        'property_category',
        'lat',
        'lng',
        'video_url',
        'developer_name',
        'completion_date',
        'handover_info',
        'amenity_ids',
        'highlights',
        'trust_label',
        'is_featured',
        'featured_media_id',
        'last_updated_at',
    ];

    public const STAGES = ['upcoming', 'ongoing', 'completed'];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'completion_date' => 'date',
            'amenity_ids' => 'array',
            'highlights' => 'array',
            'is_featured' => 'boolean',
            'last_updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Project $project): void {
            $project->last_updated_at = now();
        });

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

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Availability is derived from the project's linked listings and their units so
     * the public page never shows a hand-typed count that has drifted.
     *
     * @return array{available: int, reserved: int, sold: int, total: int}
     */
    public function availabilitySummary(): array
    {
        $summary = ['available' => 0, 'reserved' => 0, 'sold' => 0, 'total' => 0];

        $properties = $this->relationLoaded('properties')
            ? $this->properties
            : $this->properties()->published()->with('units')->get();

        foreach ($properties as $property) {
            $units = $property->relationLoaded('units') ? $property->units : $property->units()->get();
            $statuses = $units->isNotEmpty() ? $units->pluck('status')->all() : [$property->availability];

            foreach ($statuses as $status) {
                $bucket = match ($status) {
                    'available' => 'available',
                    'reserved', 'booked' => 'reserved',
                    default => 'sold',
                };
                $summary[$bucket]++;
                $summary['total']++;
            }
        }

        return $summary;
    }

    /**
     * Prefilled WhatsApp chat for this project, or null when no desk number is configured.
     */
    public function whatsappEnquiryUrl(): ?string
    {
        $base = PhoneNumber::whatsappHref(Setting::get('whatsapp') ?: config('urbanhaven.whatsapp.number'));

        if ($base === null) {
            return null;
        }

        $text = rawurlencode(__('Hello Urban Haven, I would like to know more about the :name project.', [
            'name' => $this->name,
        ]));

        return $base.'?text='.$text;
    }
}
