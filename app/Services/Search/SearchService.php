<?php

namespace App\Services\Search;

use App\Contracts\SearchService as SearchServiceContract;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Setting;
use App\Support\SearchBands;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchService implements SearchServiceContract
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, (int) config('urbanhaven.search.max_page_size', 24)));

        return $this->filtered($filters)
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();
    }

    /**
     * Map pins for the current filter set, capped so the tile layer stays light.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Property>
     */
    public function mapListings(array $filters, int $limit = 150): Collection
    {
        $bounds = config('urbanhaven.maps.bounds');

        return $this->filtered($filters, withRelations: false)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->whereBetween('lat', [$bounds['south'], $bounds['north']])
            ->whereBetween('lng', [$bounds['west'], $bounds['east']])
            ->with(['locationArea', 'propertyType', 'media'])
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'lat', 'lng', 'price', 'price_mode', 'price_basis', 'map_approximation', 'listing_type',
                'location_area_id', 'property_type_id', 'featured_media_id', 'bedrooms', 'bathrooms', 'area_value', 'area_unit', 'is_furnished']);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Property>
     */
    private function filtered(array $filters, bool $withRelations = true): Builder
    {
        $query = Property::query()->published()->whereIn('listing_type', Setting::enabledPurposes() ?: ['__none__']);

        if (empty($filters['availability'])) {
            $query->whereNotIn('availability', Property::UNAVAILABLE);
        }

        if ($withRelations) {
            $query->with(['propertyType', 'locationArea', 'media', 'publicationState']);
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderByRaw('price is null, price asc')->orderByDesc('id'),
            'price_desc' => $query->orderByRaw('price is null, price desc')->orderByDesc('id'),
            'area_desc' => $query->orderByRaw('area_sqft is null, area_sqft desc')->orderByDesc('id'),
            'beds_desc' => $query->orderByRaw('bedrooms is null, bedrooms desc')->orderByDesc('id'),
            default => $query->orderByRaw('display_priority is null, display_priority asc')->orderByDesc('id'),
        };

        if (! empty($filters['listing_type'])) {
            $query->where('listing_type', $filters['listing_type']);
        }
        $typeIds = array_values(array_unique(array_filter(array_map('intval', [
            ...(array) ($filters['property_type_ids'] ?? []),
            $filters['property_type_id'] ?? 0,
        ]))));
        if ($typeIds !== []) {
            $query->whereIn('property_type_id', $typeIds);
        }
        if (! empty($filters['category'])) {
            $query->whereIn('property_type_id', PropertyType::query()->select('id')->where('category', $filters['category']));
        }
        $areaIds = array_values(array_filter(array_map('intval', (array) ($filters['location_area_ids'] ?? []))));
        if ($areaIds === [] && ! empty($filters['location_area_id'])) {
            $areaIds = [(int) $filters['location_area_id']];
        }
        if ($areaIds !== []) {
            $query->whereIn('location_area_id', $areaIds);
        }
        if (! empty($filters['city'])) {
            $query->whereHas('locationArea', fn ($builder) => $builder->where('city', $filters['city']));
        }
        if (! empty($filters['q'])) {
            $term = '%'.addcslashes((string) $filters['q'], '%_\\').'%';
            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('title', 'like', $term)
                    ->orWhereHas('locationArea', function (Builder $area) use ($term): void {
                        $area->where('name', 'like', $term)
                            ->orWhere('city', 'like', $term);
                    });
            });
        }
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $query->where('price', '<=', $filters['max_price']);
        }
        SearchBands::apply($query, isset($filters['price_band']) ? (string) $filters['price_band'] : null, 'price', SearchBands::prices());
        SearchBands::apply($query, isset($filters['area_band']) ? (string) $filters['area_band'] : null, 'area_sqft', SearchBands::areas());
        if (isset($filters['min_beds']) && $filters['min_beds'] !== '') {
            $query->where('bedrooms', '>=', $filters['min_beds']);
        }
        if (isset($filters['max_beds']) && $filters['max_beds'] !== '') {
            $query->where('bedrooms', '<=', $filters['max_beds']);
        }
        if (isset($filters['min_baths']) && $filters['min_baths'] !== '') {
            $query->where('bathrooms', '>=', $filters['min_baths']);
        }
        if (! empty($filters['availability'])) {
            $query->where('availability', $filters['availability']);
        }
        $furnishing = array_values(array_unique(array_filter((array) ($filters['furnishing'] ?? []))));
        if ($furnishing === ['full']) {
            $query->where('is_furnished', true);
        } elseif ($furnishing === ['unfurnished']) {
            $query->where('is_furnished', false);
        } elseif ($furnishing === [] && array_key_exists('is_furnished', $filters) && $filters['is_furnished'] !== null && $filters['is_furnished'] !== '') {
            $query->where('is_furnished', (bool) $filters['is_furnished']);
        }
        $verification = array_values(array_unique(array_filter((array) ($filters['verification'] ?? []))));
        if ($verification === [] && array_key_exists('is_verified', $filters) && $filters['is_verified'] !== null && $filters['is_verified'] !== '' && (bool) $filters['is_verified']) {
            $verification = ['verified'];
        }
        if ($verification === ['verified']) {
            $query->whereNotNull('trust_label')->where('trust_label', '!=', '');
        } elseif ($verification === ['unverified']) {
            $query->where(function (Builder $builder): void {
                $builder->whereNull('trust_label')->orWhere('trust_label', '');
            });
        }
        if (isset($filters['lat'], $filters['lng']) && $filters['lat'] !== '' && $filters['lng'] !== '') {
            $latitude = (float) $filters['lat'];
            $longitude = (float) $filters['lng'];
            $radiusKm = (float) ($filters['radius_km'] ?? 3);
            $query->whereNotNull('lat')
                ->whereNotNull('lng')
                ->whereRaw(
                    '(6371 * acos(least(1, cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat))))) <= ?',
                    [$latitude, $longitude, $latitude, $radiusKm],
                );
        }
        if (! empty($filters['facing'])) {
            $query->where('facing', $filters['facing']);
        }
        if (isset($filters['min_road_width']) && $filters['min_road_width'] !== '') {
            $query->where('road_width_ft', '>=', $filters['min_road_width']);
        }
        if (isset($filters['max_road_width']) && $filters['max_road_width'] !== '') {
            $query->where('road_width_ft', '<=', $filters['max_road_width']);
        }
        if (! empty($filters['amenities']) && is_array($filters['amenities'])) {
            foreach ($filters['amenities'] as $amenityId) {
                $query->where(function (Builder $builder) use ($amenityId): void {
                    $builder->whereJsonContains('amenity_ids', (int) $amenityId)
                        ->orWhereJsonContains('amenity_ids', (string) (int) $amenityId);
                });
            }
        }

        return $query;
    }

    /**
     * @return Collection<int, Property>
     */
    public function similar(Property $property, int $limit): Collection
    {
        return app(SimilarPropertiesService::class)->similar($property, $limit);
    }
}
