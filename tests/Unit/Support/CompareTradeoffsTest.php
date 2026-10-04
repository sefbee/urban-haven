<?php

namespace Tests\Unit\Support;

use App\Models\Property;
use App\Support\CompareTradeoffs;
use Tests\TestCase;

class CompareTradeoffsTest extends TestCase
{
    public function test_states_the_trade_off_between_two_sale_listings(): void
    {
        $baseline = $this->sale(28_000_000, 1850, 3);
        $other = $this->sale(31_000_000, 2050, 4);

        $result = CompareTradeoffs::against($baseline, $other);

        $this->assertSame('200 sq ft more space for BDT 30 lakh more', $result['summary']);
        $this->assertSame([
            ['label' => 'BDT 30 lakh more', 'tone' => CompareTradeoffs::TONE_PLUS],
            ['label' => '200 sq ft more space', 'tone' => CompareTradeoffs::TONE_PLUS],
            ['label' => '1 more bedroom', 'tone' => CompareTradeoffs::TONE_PLUS],
            ['label' => 'BDT 13 less per sq ft', 'tone' => CompareTradeoffs::TONE_MINUS],
        ], $result['deltas']);
    }

    public function test_never_compares_prices_measured_differently(): void
    {
        $sale = $this->sale(28_000_000, 1850, 3);
        $rent = new Property(['listing_type' => 'rent', 'price_basis' => 'monthly_rent', 'price' => 85_000, 'area_value' => 1250, 'area_unit' => 'sqft', 'bedrooms' => 2]);

        $result = CompareTradeoffs::against($sale, $rent);

        $this->assertNull($result['summary']);
        $this->assertSame(['600 sq ft less space', '1 fewer bedroom'], array_column($result['deltas'], 'label'));
    }

    public function test_price_per_square_foot_needs_a_price_and_a_size(): void
    {
        $this->assertSame('BDT 15,405 per sq ft', CompareTradeoffs::formattedPricePerSqft($this->sale(28_500_000, 1850, 3)));
        $this->assertNull(CompareTradeoffs::formattedPricePerSqft(new Property(['price_mode' => 'on_request', 'area_value' => 1850, 'area_unit' => 'sqft'])));
        $this->assertNull(CompareTradeoffs::formattedPricePerSqft(new Property(['price' => 1_000_000, 'area_value' => null, 'area_unit' => 'sqft'])));
    }

    private function sale(int $price, int $sqft, int $bedrooms): Property
    {
        return new Property([
            'listing_type' => 'sale',
            'price_basis' => 'total_sale',
            'price' => $price,
            'area_value' => $sqft,
            'area_unit' => 'sqft',
            'bedrooms' => $bedrooms,
        ]);
    }
}
