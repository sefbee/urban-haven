<?php

namespace App\Support;

use InvalidArgumentException;

final class MoneyFormatter
{
    public static function formatBdt(string|int|float|null $amount, string $basis = 'total_sale'): string
    {
        if ($amount === null || $amount === '') {
            return __('Price on request');
        }

        $numeric = self::toNumericString($amount);
        $formatted = number_format((float) $numeric, 0, '.', ',');
        $suffix = $basis === 'monthly_rent' ? __(' /month') : '';

        return 'BDT '.$formatted.$suffix;
    }

    /**
     * Human-readable gloss for large amounts using the lakh/crore scale that
     * buyers in Bangladesh read prices in.
     */
    public static function compactBdt(string|int|float|null $amount): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        $value = (float) self::toNumericString($amount);

        return match (true) {
            $value >= 10000000 => self::trimZeros($value / 10000000).' '.__('crore'),
            $value >= 100000 => self::trimZeros($value / 100000).' '.__('lakh'),
            default => null,
        };
    }

    private static function trimZeros(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.');
    }

    public static function toNumericString(string|int|float $amount): string
    {
        if (is_float($amount) && (is_nan($amount) || is_infinite($amount))) {
            throw new InvalidArgumentException('Money amount must be a finite number.');
        }

        return is_string($amount) ? $amount : (string) $amount;
    }
}
