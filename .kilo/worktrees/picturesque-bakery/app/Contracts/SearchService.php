<?php

namespace App\Contracts;

use App\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SearchService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters, int $page, int $perPage): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Property>
     */
    public function mapListings(array $filters, int $limit = 150): Collection;

    /**
     * @return Collection<int, Property>
     */
    public function similar(Property $property, int $limit): Collection;
}
