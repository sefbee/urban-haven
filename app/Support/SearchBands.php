<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class SearchBands
{
    /**
     * @return array<string, array{label: string, min: int|null, max: int|null}>
     */
    public static function prices(): array
    {
        $bands = [];

        foreach (config('urbanhaven.price_bands', []) as $group) {
            foreach ($group['options'] ?? [] as $key => $band) {
                $bands[$key] = $band;
            }
        }

        return $bands;
    }

    /**
     * @return array<string, array{label: string, min: int|null, max: int|null}>
     */
    public static function areas(): array
    {
        return config('urbanhaven.area_bands', []);
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, array{label: string, min: int|null, max: int|null}>  $bands
     */
    public static function apply(Builder $query, ?string $key, string $column, array $bands): void
    {
        if ($key === null || $key === '' || ! isset($bands[$key])) {
            return;
        }

        $band = $bands[$key];

        if ($band['min'] !== null) {
            $query->where($column, '>=', $band['min']);
        }

        if ($band['max'] !== null) {
            $query->where($column, '<=', $band['max']);
        }
    }
}
