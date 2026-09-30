<?php

namespace App\Services\Search;

use App\Contracts\SearchService as SearchServiceContract;
use App\Models\Property;
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
        return $this->filtered($filters, withRelations: false)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'lat', 'lng', 'price', 'price_basis']);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Property>
     */
    private function filtered(array $filters, bool $withRelations = true): Builder
    {
        $query = Property::query()->published();

        if ($withRelations) {
            $query->with(['propertyType', 'locationArea', 'media', 'publicationState']);
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderByRaw('price is null, price asc')->orderByDesc('id'),
            'price_desc' => $query->orderByRaw('price is null, price desc')->orderByDesc('id'),
            'area_desc' => $query->orderByRaw('area_sqft is null, area_sqft desc')->orderByDesc('id'),
            'beds_desc' => $query->orderByRaw('bedrooms is null, bedrooms desc')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        if (! empty($filters['listing_type'])) {
            $query->where('listing_type', $filters['listing_type']);
        }
        if (! empty($filters['property_type_id'])) {
            $query->where('property_type_id', $filters['property_type_id']);
        }
        if (! empty($filters['location_area_id'])) {
            $query->where('location_area_id', $filters['location_area_id']);
        }
        if (! empty($filters['city'])) {
            $query->whereHas('locationArea', fn ($builder) => $builder->where('city', $filters['city']));
        }
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $query->where('price', '<=', $filters['max_price']);
        }
        if (isset($filters['min_beds']) && $filters['min_beds'] !== '') {
            $query->where('bedrooms', '>=', $filters['min_beds']);
        }
        if (isset($filters['max_beds']) && $filters['max_beds'] !== '') {
            $query->where('bedrooms', '<=', $filters['max_beds']);
        }
        if (! empty($filters['availability'])) {
            $query->where('availability', $filters['availability']);
        }
        if (array_key_exists('is_furnished', $filters) && $filters['is_furnished'] !== null && $filters['is_furnished'] !== '') {
            $query->where('is_furnished', (bool) $filters['is_furnished']);
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
