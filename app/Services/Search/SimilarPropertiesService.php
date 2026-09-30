<?php

namespace App\Services\Search;

use App\Contracts\SimilarPropertiesService as SimilarPropertiesServiceContract;
use App\Models\Property;
use Illuminate\Support\Collection;

class SimilarPropertiesService implements SimilarPropertiesServiceContract
{
    /**
     * @return Collection<int, Property>
     */
    public function similar(Property $property, int $limit = 6): Collection
    {
        $limit = min($limit, (int) config('urbanhaven.search.similar_limit', 6));
        $band = (int) config('urbanhaven.search.price_band_percent', 25) / 100;

        $tier1 = Property::query()
            ->published()
            ->with(['propertyType', 'locationArea', 'media'])
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
            ->get();

        if ($tier1->count() >= $limit) {
            return $tier1;
        }

        $exclude = $tier1->pluck('id')->push($property->id);
        $city = $property->locationArea?->city;

        $tier2 = Property::query()
            ->published()
            ->with(['propertyType', 'locationArea', 'media'])
            ->whereNotIn('id', $exclude)
            ->where('property_type_id', $property->property_type_id)
            ->when($city, fn ($query) => $query->whereHas('locationArea', fn ($builder) => $builder->where('city', $city)))
            ->limit($limit - $tier1->count())
            ->get();

        return $tier1->concat($tier2)->unique('id')->values();
    }
}
