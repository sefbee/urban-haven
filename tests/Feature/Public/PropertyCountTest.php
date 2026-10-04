<?php

namespace Tests\Feature\Public;

use App\Models\LocationArea;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PublicationState;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PropertyCountTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_counts_published_listings_matching_the_same_filters_as_search(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $gulshan = LocationArea::query()->create(['name' => 'Gulshan', 'city' => 'Dhaka', 'is_active' => true]);
        $banani = LocationArea::query()->create(['name' => 'Banani', 'city' => 'Dhaka', 'is_active' => true]);
        $this->publish(['location_area_id' => $gulshan->id, 'bedrooms' => 3]);
        $this->publish(['location_area_id' => $gulshan->id, 'bedrooms' => 2]);
        $this->publish(['location_area_id' => $banani->id, 'bedrooms' => 4]);
        $this->publish(['location_area_id' => $gulshan->id], published: false);

        $this->getJson(route('properties.count'))->assertOk()->assertExactJson(['total' => 3]);
        $this->getJson(route('properties.count', ['location_area_id' => $gulshan->id]))->assertOk()->assertExactJson(['total' => 2]);
        $this->getJson(route('properties.count', ['location_area_id' => $gulshan->id, 'min_beds' => 3]))->assertOk()->assertExactJson(['total' => 1]);
        $this->getJson(route('properties.count', ['listing_type' => 'rent']))->assertOk()->assertExactJson(['total' => 0]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function publish(array $attributes, bool $published = true): Property
    {
        $property = Property::query()->create([
            'title' => fake()->unique()->sentence(3),
            'property_type_id' => PropertyType::query()->firstOrCreate(['key' => 'apt'], ['label' => 'Apartment', 'is_active' => true])->id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_basis' => 'total_sale',
            'price' => 10_000_000,
            'area_unit' => 'sqft',
            ...$attributes,
        ]);

        if ($published) {
            $property->publicationState->update(['status' => PublicationState::PUBLISHED]);
        }

        return $property;
    }
}
