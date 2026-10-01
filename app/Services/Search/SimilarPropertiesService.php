<?php

namespace App\Services\Search;

use App\Contracts\SimilarPropertiesService as SimilarPropertiesServiceContract;
use App\Models\Property;
use App\Support\TaggedCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SimilarPropertiesService implements SimilarPropertiesServiceContract
{
    /**
     * @return Collection<int, Property>
     */
    public function similar(Property $property, int $limit = 6): Collection
    {
        $limit = min($limit, (int) config('urbanhaven.search.similar_limit', 6));

        $ids = TaggedCache::remember(['properties', 'property:'.$property->id], 'similar:'.$property->id.':'.$limit, 900, fn (): array => $this->similarIds($property, $limit));

        if ($ids === []) {
            return collect();
        }

        return $this->base()->whereKey($ids)->get()->sortBy(fn (Property $item): int|false => array_search($item->id, $ids, true))->values();
    }

    /**
     * Same purpose and type, available listings only: first the same area within the price band, then the same city.
     *
     * @return list<int>
     */
    private function similarIds(Property $property, int $limit): array
    {
        $band = (int) config('urbanhaven.search.price_band_percent', 25) / 100;

        $tier1 = $this->base()
            ->where('listing_type', $property->listing_type)
            ->whereKeyNot($property->id)
            ->where('location_area_id', $property->location_area_id)
            ->where('property_type_id', $property->property_type_id)
            ->when($property->price, function ($query) use ($property, $band): void {
                $query->whereBetween('price', [
                    $property->price * (1 - $band),
                    $property->price * (1 + $band),
                ]);
            })
            ->limit($limit)
            ->pluck('id');

        if ($tier1->count() >= $limit) {
            return $tier1->all();
        }

        $exclude = $tier1->concat([$property->id]);
        $city = $property->locationArea?->city;

        $tier2 = $this->base()
            ->where('listing_type', $property->listing_type)
            ->whereNotIn('id', $exclude)
            ->where('property_type_id', $property->property_type_id)
            ->when($city, fn ($query) => $query->whereHas('locationArea', fn ($builder) => $builder->where('city', $city)))
            ->limit($limit - $tier1->count())
            ->pluck('id');

        return $tier1->concat($tier2)->unique()->map(fn ($id): int => (int) $id)->values()->all();
    }

    /**
     * @return Builder<Property>
     */
    private function base(): Builder
    {
        return Property::query()
            ->published()
            ->whereNotIn('availability', Property::UNAVAILABLE)
            ->with(['propertyType', 'locationArea', 'media']);
    }
}
