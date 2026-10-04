<?php

namespace App\Support;

use App\Models\Property;
use InvalidArgumentException;

/**
 * Spells out the differences between two compared listings so visitors never have to calculate.
 *
 * A difference is only stated when both sides carry the field and the values are measured the
 * same way: prices must share a purpose and basis, and sizes must convert to square feet.
 */
final class CompareTradeoffs
{
    public const TONE_PLUS = 'plus';

    public const TONE_MINUS = 'minus';

    public const TONE_SAME = 'same';

    public static function pricePerSqft(Property $property): ?float
    {
        $sqft = self::sqft($property);

        if ($property->isPriceOnRequest() || ! is_numeric($property->price) || $sqft === null) {
            return null;
        }

        return (float) $property->price / $sqft;
    }

    public static function formattedPricePerSqft(Property $property): ?string
    {
        $rate = self::pricePerSqft($property);

        if ($rate === null) {
            return null;
        }

        return $property->price_basis === 'monthly_rent'
            ? __('BDT :rate per sq ft a month', ['rate' => number_format($rate, $rate < 100 ? 1 : 0)])
            : __('BDT :rate per sq ft', ['rate' => number_format($rate)]);
    }

    /**
     * @return array{deltas: list<array{label: string, tone: string}>, summary: string|null}
     */
    public static function against(Property $baseline, Property $other): array
    {
        $deltas = [];
        $priceDiff = self::pricesComparable($baseline, $other) ? (float) $other->price - (float) $baseline->price : null;
        $baseSqft = self::sqft($baseline);
        $otherSqft = self::sqft($other);
        $areaDiff = $baseSqft !== null && $otherSqft !== null ? round($otherSqft - $baseSqft) : null;

        if ($priceDiff !== null) {
            $deltas[] = abs($priceDiff) < 1
                ? ['label' => __('Same price'), 'tone' => self::TONE_SAME]
                : [
                    'label' => $priceDiff > 0
                        ? __(':amount more', ['amount' => self::money(abs($priceDiff), $other->price_basis)])
                        : __(':amount less', ['amount' => self::money(abs($priceDiff), $other->price_basis)]),
                    'tone' => $priceDiff > 0 ? self::TONE_PLUS : self::TONE_MINUS,
                ];
        }

        if ($areaDiff !== null) {
            $deltas[] = $areaDiff == 0
                ? ['label' => __('Same size'), 'tone' => self::TONE_SAME]
                : [
                    'label' => $areaDiff > 0
                        ? __(':area more space', ['area' => self::sqftLabel($areaDiff)])
                        : __(':area less space', ['area' => self::sqftLabel($areaDiff)]),
                    'tone' => $areaDiff > 0 ? self::TONE_PLUS : self::TONE_MINUS,
                ];
        }

        foreach (['bedrooms' => [':count more bedroom|:count more bedrooms', ':count fewer bedroom|:count fewer bedrooms'], 'bathrooms' => [':count more bathroom|:count more bathrooms', ':count fewer bathroom|:count fewer bathrooms']] as $field => [$more, $fewer]) {
            if (! is_numeric($baseline->{$field}) || ! is_numeric($other->{$field})) {
                continue;
            }

            $difference = (int) $other->{$field} - (int) $baseline->{$field};

            if ($difference !== 0) {
                $deltas[] = [
                    'label' => trans_choice($difference > 0 ? $more : $fewer, abs($difference), ['count' => abs($difference)]),
                    'tone' => $difference > 0 ? self::TONE_PLUS : self::TONE_MINUS,
                ];
            }
        }

        $baseRate = self::pricePerSqft($baseline);
        $otherRate = self::pricePerSqft($other);

        if ($priceDiff !== null && $baseRate !== null && $otherRate !== null && abs($otherRate - $baseRate) >= 1) {
            $rateDiff = number_format(abs($otherRate - $baseRate));
            $deltas[] = [
                'label' => $otherRate > $baseRate
                    ? __('BDT :rate more per sq ft', ['rate' => $rateDiff])
                    : __('BDT :rate less per sq ft', ['rate' => $rateDiff]),
                'tone' => $otherRate > $baseRate ? self::TONE_PLUS : self::TONE_MINUS,
            ];
        }

        return ['deltas' => $deltas, 'summary' => self::summary($priceDiff, $areaDiff, $other->price_basis)];
    }

    private static function summary(?float $priceDiff, ?float $areaDiff, ?string $basis): ?string
    {
        if ($priceDiff === null || abs($priceDiff) < 1 || $areaDiff === null || $areaDiff == 0) {
            return null;
        }

        $price = self::money(abs($priceDiff), $basis);
        $area = self::sqftLabel($areaDiff);

        return match (true) {
            $areaDiff > 0 && $priceDiff > 0 => __(':area more space for :price more', ['area' => $area, 'price' => $price]),
            $areaDiff > 0 && $priceDiff < 0 => __(':area more space for :price less', ['area' => $area, 'price' => $price]),
            $areaDiff < 0 && $priceDiff < 0 => __(':price less for :area less space', ['area' => $area, 'price' => $price]),
            default => __(':price more for :area less space', ['area' => $area, 'price' => $price]),
        };
    }

    private static function pricesComparable(Property $a, Property $b): bool
    {
        return ! $a->isPriceOnRequest() && ! $b->isPriceOnRequest()
            && is_numeric($a->price) && is_numeric($b->price)
            && $a->listing_type === $b->listing_type
            && $a->price_basis === $b->price_basis;
    }

    private static function sqft(Property $property): ?float
    {
        if (! is_numeric($property->area_value) || (float) $property->area_value <= 0 || blank($property->area_unit)) {
            return null;
        }

        try {
            return AreaConverter::toSqft($property->area_value, (string) $property->area_unit);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    private static function sqftLabel(float $difference): string
    {
        return number_format(abs($difference)).' '.__('sq ft');
    }

    private static function money(float $amount, ?string $basis): string
    {
        $compact = MoneyFormatter::compactBdt($amount);
        $label = 'BDT '.($compact ?? number_format($amount));

        return $basis === 'monthly_rent' ? __(':amount a month', ['amount' => $label]) : $label;
    }
}
