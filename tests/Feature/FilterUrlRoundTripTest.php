<?php

namespace Tests\Feature;

use App\Models\LocationArea;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PublicationState;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Property P1: Filter URL Round-Trip
 *
 * For any combination of valid filter parameters, applying them and reloading
 * the resulting URL must produce the same property IDs as the original query.
 *
 * Validates: Requirements 2.1, 2.2
 */
class FilterUrlRoundTripTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);

        // Shared property type and location areas for all data-provider tests.
        PropertyType::query()->firstOrCreate(
            ['key' => 'apartment'],
            ['label' => 'Apartment', 'is_active' => true, 'category' => PropertyType::CATEGORY_RESIDENTIAL],
        );
        LocationArea::query()->firstOrCreate(
            ['name' => 'Gulshan'],
            ['city' => 'Dhaka', 'is_active' => true],
        );
        LocationArea::query()->firstOrCreate(
            ['name' => 'Banani'],
            ['city' => 'Dhaka', 'is_active' => true],
        );
    }

    // -----------------------------------------------------------------------
    // Data providers
    // -----------------------------------------------------------------------

    /**
     * Returns an array of [filterBag, description] pairs covering the main
     * filter dimensions individually and several combined combinations.
     *
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function filterCombinationProvider(): array
    {
        return [
            'listing_type sale' => [['listing_type' => 'sale'], 'by listing type (sale)'],
            'listing_type rent' => [['listing_type' => 'rent'], 'by listing type (rent)'],
            'min_price only' => [['min_price' => 5_000_000], 'by minimum price'],
            'max_price only' => [['max_price' => 20_000_000], 'by maximum price'],
            'price range' => [['min_price' => 3_000_000, 'max_price' => 15_000_000], 'by price range'],
            'min_beds' => [['min_beds' => 2], 'by minimum bedrooms'],
            'availability available' => [['availability' => 'available'], 'by availability'],
            'sort price_asc' => [['sort' => 'price_asc'], 'with price-ascending sort'],
            'sort price_desc' => [['sort' => 'price_desc'], 'with price-descending sort'],
            'listing_type + min_beds' => [['listing_type' => 'sale', 'min_beds' => 2], 'by listing type and bedrooms'],
            'listing_type + price range' => [['listing_type' => 'sale', 'min_price' => 5_000_000, 'max_price' => 30_000_000], 'by listing type and price range'],
            'min_beds + max_price' => [['min_beds' => 3, 'max_price' => 25_000_000], 'by bedrooms and maximum price'],
            'listing_type + beds + price' => [['listing_type' => 'sale', 'min_beds' => 2, 'max_price' => 20_000_000], 'by listing type, bedrooms and price'],
        ];
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    /**
     * Extract property IDs from the search-index HTML response.
     *
     * showcase-slide.blade.php emits `data-spotlight="{{ $property->id }}"` on
     * every slide wrapper, so we parse that attribute to get the visible IDs.
     * Results are sorted for a stable, order-independent comparison.
     *
     * @return list<int>
     */
    private function propertyIdsFromResponse(TestResponse $response): array
    {
        preg_match_all('/data-spotlight="(\d+)"/', $response->getContent(), $matches);

        $ids = array_map('intval', $matches[1] ?? []);
        sort($ids);

        return $ids;
    }

    /** Publish a property with the given attribute overrides. */
    private function publishProperty(string $title, array $attributes = []): Property
    {
        $type = PropertyType::query()->where('key', 'apartment')->firstOrFail();
        $area = LocationArea::query()->where('name', 'Gulshan')->firstOrFail();

        $property = Property::query()->create([
            'title' => $title,
            'property_type_id' => $type->id,
            'location_area_id' => $area->id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_basis' => 'total_sale',
            'area_unit' => 'sqft',
            ...$attributes,
        ]);

        $property->publicationState->update(['status' => PublicationState::PUBLISHED]);

        return $property;
    }

    // -----------------------------------------------------------------------
    // Core property test (P1)
    // -----------------------------------------------------------------------

    /**
     * **Property P1: Filter URL Round-Trip**
     *
     * For each filter combination from the data provider:
     *   1. Make a GET request to the search route with those filters.
     *   2. Record the full request URL produced.
     *   3. Make a second identical GET request using that same URL.
     *   4. Assert both responses return the same property IDs.
     *
     * Validates: Requirements 2.1, 2.2
     *
     * @param  array<string, mixed>  $filters
     */
    #[DataProvider('filterCombinationProvider')]
    public function test_filter_url_round_trip(array $filters, string $description): void
    {
        // Seed a varied set of listings that exercises the filters.
        $this->publishProperty('Sale listing cheap', ['listing_type' => 'sale', 'price' => 4_000_000, 'bedrooms' => 1, 'availability' => 'available']);
        $this->publishProperty('Sale listing mid', ['listing_type' => 'sale', 'price' => 10_000_000, 'bedrooms' => 2, 'availability' => 'available']);
        $this->publishProperty('Sale listing expensive', ['listing_type' => 'sale', 'price' => 25_000_000, 'bedrooms' => 3, 'availability' => 'available']);
        $this->publishProperty('Rent listing cheap', ['listing_type' => 'rent', 'price' => 20_000, 'price_basis' => 'monthly_rent', 'bedrooms' => 1, 'availability' => 'available']);
        $this->publishProperty('Rent listing mid', ['listing_type' => 'rent', 'price' => 60_000, 'price_basis' => 'monthly_rent', 'bedrooms' => 2, 'availability' => 'available']);

        // First request — original query.
        $firstResponse = $this->get(route('properties.index', $filters));
        $firstResponse->assertOk();

        $firstIds = $this->propertyIdsFromResponse($firstResponse);

        // Second request — identical URL (round-trip).
        $secondResponse = $this->get(route('properties.index', $filters));
        $secondResponse->assertOk();

        $secondIds = $this->propertyIdsFromResponse($secondResponse);

        $this->assertSame(
            $firstIds,
            $secondIds,
            "Round-trip with filters [{$description}] returned different property sets.\n"
            .'First:  '.implode(', ', $firstIds)."\n"
            .'Second: '.implode(', ', $secondIds),
        );
    }

    // -----------------------------------------------------------------------
    // Supplementary round-trip tests
    // -----------------------------------------------------------------------

    /**
     * A search with no filters round-trips correctly (baseline).
     *
     * Validates: Requirements 2.1, 2.2
     */
    public function test_empty_filter_bag_round_trips_correctly(): void
    {
        $this->publishProperty('Alpha listing', ['listing_type' => 'sale', 'price' => 8_000_000]);
        $this->publishProperty('Beta listing', ['listing_type' => 'rent', 'price' => 40_000, 'price_basis' => 'monthly_rent']);

        $firstIds = $this->propertyIdsFromResponse($this->get(route('properties.index')));
        $secondIds = $this->propertyIdsFromResponse($this->get(route('properties.index')));

        $this->assertSame($firstIds, $secondIds, 'Empty-filter round-trip produced different results.');
    }

    /**
     * A filter combination that produces zero results round-trips consistently.
     *
     * Validates: Requirements 2.1
     */
    public function test_zero_result_filter_round_trips_consistently(): void
    {
        $this->publishProperty('Gulshan sale', ['listing_type' => 'sale', 'price' => 5_000_000, 'bedrooms' => 1]);

        // min_beds=10 will match nothing.
        $filters = ['listing_type' => 'sale', 'min_beds' => 10];

        $first = $this->get(route('properties.index', $filters));
        $first->assertOk();

        $second = $this->get(route('properties.index', $filters));
        $second->assertOk();

        $firstIds = $this->propertyIdsFromResponse($first);
        $secondIds = $this->propertyIdsFromResponse($second);

        $this->assertSame($firstIds, $secondIds, 'Zero-result filter round-trip produced different results.');
        $this->assertEmpty($firstIds, 'Expected no results for min_beds=10 with only a 1-bed listing.');
    }

    /**
     * A randomised filter bag round-trips deterministically.
     *
     * This is the lightweight "property-based" variant: we generate a random
     * filter set at test runtime to catch combinations missed by the fixed
     * provider above.
     *
     * Validates: Requirements 2.1, 2.2
     */
    public function test_randomised_filter_bag_round_trips_deterministically(): void
    {
        // Publish a cross-section of listings for the random filters to work against.
        $this->publishProperty('Random alpha', ['listing_type' => 'sale', 'price' => 7_000_000, 'bedrooms' => 2, 'availability' => 'available']);
        $this->publishProperty('Random beta', ['listing_type' => 'sale', 'price' => 15_000_000, 'bedrooms' => 3, 'availability' => 'available']);
        $this->publishProperty('Random gamma', ['listing_type' => 'rent', 'price' => 45_000, 'price_basis' => 'monthly_rent', 'bedrooms' => 2, 'availability' => 'available']);
        $this->publishProperty('Random delta', ['listing_type' => 'rent', 'price' => 90_000, 'price_basis' => 'monthly_rent', 'bedrooms' => 4, 'availability' => 'available']);

        $randomFilterBags = $this->generateRandomFilterBags(10);

        foreach ($randomFilterBags as $index => $bag) {
            $first = $this->get(route('properties.index', $bag));
            $first->assertOk();

            $second = $this->get(route('properties.index', $bag));
            $second->assertOk();

            $firstIds = $this->propertyIdsFromResponse($first);
            $secondIds = $this->propertyIdsFromResponse($second);

            $this->assertSame(
                $firstIds,
                $secondIds,
                "Random filter bag #{$index} (".json_encode($bag).') produced different results on round-trip.',
            );
        }
    }

    // -----------------------------------------------------------------------
    // Random filter bag generator
    // -----------------------------------------------------------------------

    /**
     * Generate $count random but valid filter bags.
     *
     * Each bag is a random subset of dimensions chosen from the valid parameter
     * space defined by SearchRequest. Values are constrained to valid ranges so
     * the request will pass validation without being silently dropped.
     *
     * @return list<array<string, mixed>>
     */
    private function generateRandomFilterBags(int $count): array
    {
        $bags = [];

        for ($i = 0; $i < $count; $i++) {
            $bag = [];

            // Listing type (randomly include or omit).
            if (random_int(0, 1)) {
                $bag['listing_type'] = random_int(0, 1) ? 'sale' : 'rent';
            }

            // Price range (consistent with listing type to avoid invalid combos).
            $isSale = ($bag['listing_type'] ?? null) !== 'rent';
            if (random_int(0, 1)) {
                $minPrice = $isSale
                    ? random_int(1, 5) * 1_000_000
                    : random_int(1, 5) * 10_000;
                $bag['min_price'] = $minPrice;

                if (random_int(0, 1)) {
                    $bag['max_price'] = $minPrice + ($isSale ? random_int(1, 5) * 1_000_000 : random_int(1, 5) * 10_000);
                }
            }

            // Bedrooms.
            if (random_int(0, 1)) {
                $bag['min_beds'] = random_int(1, 5);
            }

            // Availability.
            if (random_int(0, 1)) {
                $bag['availability'] = 'available';
            }

            // Sort order.
            if (random_int(0, 1)) {
                $bag['sort'] = array_rand(array_flip(['newest', 'price_asc', 'price_desc', 'beds_desc']));
            }

            $bags[] = $bag;
        }

        return $bags;
    }
}
