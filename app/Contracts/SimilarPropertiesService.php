<?php

namespace App\Contracts;

use App\Models\Property;
use Illuminate\Support\Collection;

interface SimilarPropertiesService
{
    /**
     * @return Collection<int, Property>
     */
    public function similar(Property $property, int $limit = 6): Collection;
}
