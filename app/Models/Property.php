<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\Publishable;
use App\Support\AreaConverter;
use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Property extends Model
{
    use HasMedia, HasSlug, Publishable;

    public const AVAILABILITIES = ['available', 'reserved', 'sold', 'rented', 'off-market'];

    public const AVAILABILITY_LABELS = [
        'available' => 'Available',
        'reserved' => 'Reserved',
        'sold' => 'Sold',
        'rented' => 'Rented',
        'off-market' => 'Off market',
    ];

    public const UNAVAILABLE = ['sold', 'rented', 'off-market'];

    public const PRICE_FIXED = 'fixed';

    public const PRICE_ON_REQUEST = 'on_request';

    protected $fillable = [
        'slug',
        'reference',
        'project_id',
        'property_type_id',
        'location_area_id',
        'address',
        'title',
        'description',
        'availability',
        'reserved_at',
        'reserved_by',
        'reservation_expires_at',
        'listing_type',
        'price_mode',
        'price',
        'price_basis',
        'currency',
        'area_value',
        'area_unit',
        'area_sqft',
        'bedrooms',
        'bathrooms',
        'balconies',
        'parking_spaces',
        'floor_number',
        'is_furnished',
        'facing',
        'road_width_ft',
        'amenity_ids',
        'lat',
        'lng',
        'map_approximation',
        'video_url',
        'virtual_tour_url',
        'trust_label',
        'assigned_contact_id',
        'is_featured',
        'display_priority',
        'version',
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
            'road_width_ft' => 'integer',
            'is_featured' => 'boolean',
            'amenity_ids' => 'array',
            'last_updated_at' => 'datetime',
            'reserved_at' => 'datetime',
            'reservation_expires_at' => 'datetime',
            'version' => 'integer',
            'display_priority' => 'integer',
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

            if ($property->price === null && $property->price_mode !== self::PRICE_ON_REQUEST) {
                $property->price_mode = self::PRICE_ON_REQUEST;
            }

            if ($property->price_mode === self::PRICE_ON_REQUEST) {
                $property->price = null;
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

    public function assignedContact(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_contact_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(PropertyStatusHistory::class)->latest('id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function isUnavailable(): bool
    {
        return in_array($this->availability, self::UNAVAILABLE, true);
    }

    public function isPriceOnRequest(): bool
    {
        return $this->price_mode === self::PRICE_ON_REQUEST || $this->price === null;
    }

    public function availabilityLabel(): string
    {
        return self::AVAILABILITY_LABELS[$this->availability] ?? ucfirst(str_replace('_', ' ', (string) $this->availability));
    }

    public function fieldProfile(): string
    {
        return $this->propertyType?->field_profile ?? PropertyType::PROFILE_APARTMENT;
    }

    /**
     * Coordinates safe to publish, honouring the owner's address display policy.
     *
     * @return array{lat: float, lng: float, approximate: bool}|null
     */
    public function publicCoordinates(): ?array
    {
        if ($this->lat === null || $this->lng === null) {
            return null;
        }

        $lat = (float) $this->lat;
        $lng = (float) $this->lng;

        if (! self::coordinatesWithinServiceArea($lat, $lng)) {
            return null;
        }

        $mode = (string) Setting::get('address_display_mode', 'approximate');

        if ($mode === 'hidden') {
            return null;
        }

        $approximate = $mode === 'approximate' || (bool) $this->map_approximation;

        if ($approximate) {
            $precision = max(1, min(4, (int) Setting::get('coordinate_precision', 2)));
            $lat = round($lat, $precision);
            $lng = round($lng, $precision);
        }

        return ['lat' => $lat, 'lng' => $lng, 'approximate' => $approximate];
    }

    public static function coordinatesWithinServiceArea(float $lat, float $lng): bool
    {
        $bounds = config('urbanhaven.maps.bounds');

        return $lat >= $bounds['south'] && $lat <= $bounds['north']
            && $lng >= $bounds['west'] && $lng <= $bounds['east'];
    }

    public function publicAddress(): ?string
    {
        return match ((string) Setting::get('address_display_mode', 'approximate')) {
            'exact' => $this->address ?: $this->locationArea?->name,
            'hidden' => null,
            default => $this->locationArea?->name,
        };
    }

    public function amenities(): Collection
    {
        $ids = $this->amenity_ids ?? [];

        if ($ids === []) {
            return collect();
        }

        return Amenity::query()->whereIn('id', $ids)->get();
    }

    /**
     * Prefilled WhatsApp chat for this listing, or null when no desk number is configured.
     */
    public function whatsappEnquiryUrl(): ?string
    {
        $base = PhoneNumber::whatsappHref(Setting::get('whatsapp') ?: config('urbanhaven.whatsapp.number'));

        if ($base === null) {
            return null;
        }

        $text = rawurlencode(__('Hello Urban Haven, I am interested in :title (:reference).', [
            'title' => $this->title,
            'reference' => $this->reference ?: $this->slug,
        ]));

        return $base.'?text='.$text;
    }
}
