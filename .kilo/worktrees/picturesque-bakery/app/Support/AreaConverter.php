<?php

namespace App\Support;

use InvalidArgumentException;

final class AreaConverter
{
    public static function toSqft(float|int|string $value, string $unit): float
    {
        $unitKey = strtolower(trim($unit));
        $table = config('urbanhaven.area_units', []);

        if (! isset($table[$unitKey]['to_sqft'])) {
            throw new InvalidArgumentException("No conversion entry exists for area unit [{$unit}].");
        }

        return round((float) $value * (float) $table[$unitKey]['to_sqft'], 4);
    }

    public static function format(float|int|string $originalValue, string $unit): string
    {
        $unitKey = strtolower(trim($unit));
        $label = config("urbanhaven.area_units.{$unitKey}.label", strtoupper($unit));

        return rtrim(rtrim(number_format((float) $originalValue, 2, '.', ','), '0'), '.').' '.$label;
    }
}
