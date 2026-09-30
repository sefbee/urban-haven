<?php

namespace Tests\Feature\Public;

use App\Models\Amenity;
use App\Models\LocationArea;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PublicationState;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PropertyDiscoveryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_search_returns_only_published_properties(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $area = LocationArea::query()->create(['name' => 'Gulshan', 'city' => 'Dhaka', 'is_active' => true]);
        $type = PropertyType::query()->create(['key' => 'apt', 'label' => 'Apartment', 'is_active' => true]);

        $live = Property::query()->create([
            'title' => 'Published home',
            'property_type_id' => $type->id,
            'location_area_id' => $area->id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_basis' => 'total_sale',
            'area_unit' => 'sqft',
        ]);
        $live->publicationState()->update(['status' => PublicationState::PUBLISHED]);

        $draft = Property::query()->create([
            'title' => 'Hidden draft',
            'property_type_id' => $type->id,
            'location_area_id' => $area->id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_basis' => 'total_sale',
            'area_unit' => 'sqft',
        ]);

        $this->get(route('properties.index'))
            ->assertOk()
            ->assertSee('Published home')
            ->assertDontSee('Hidden draft');

        $this->get(route('properties.show', $draft->slug))->assertNotFound();
        $this->get(route('properties.show', $live->slug))->assertOk()->assertSee('Published home');
    }

    public function test_search_filters_by_amenity(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $parking = Amenity::query()->create(['key' => 'parking', 'label' => 'Parking', 'is_active' => true]);
        $gym = Amenity::query()->create(['key' => 'gym', 'label' => 'Gym', 'is_active' => true]);

        $this->publishProperty('Home with parking', ['amenity_ids' => [$parking->id]]);
        $this->publishProperty('Home with gym', ['amenity_ids' => [$gym->id]]);

        $this->get(route('properties.index', ['amenities' => [$parking->id]]))
            ->assertOk()
            ->assertSee('Home with parking')
            ->assertDontSee('Home with gym');
    }

    public function test_search_sorts_by_price_and_keeps_unpriced_listings_last(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $this->publishProperty('Cheaper home', ['price' => 5_000_000]);
        $this->publishProperty('Pricier home', ['price' => 9_000_000]);
        $this->publishProperty('Price on request', ['price' => null]);

        $this->get(route('properties.index', ['sort' => 'price_asc']))
            ->assertOk()
            ->assertSeeInOrder(['Cheaper home', 'Pricier home', 'Price on request']);
    }

    public function test_search_map_follows_the_active_filters(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $this->publishProperty('Sale home', ['listing_type' => 'sale', 'lat' => 23.81, 'lng' => 90.41]);
        $this->publishProperty('Rent home', ['listing_type' => 'rent', 'price_basis' => 'monthly_rent', 'lat' => 23.75, 'lng' => 90.37]);

        $this->get(route('properties.index', ['listing_type' => 'sale']))
            ->assertOk()
            ->assertSee('Sale home')
            ->assertDontSee('Rent home');
    }

    public function test_homepage_renders(): void
    {
        $this->get('/')->assertOk();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function publishProperty(string $title, array $attributes = []): Property
    {
        $property = Property::query()->create([
            'title' => $title,
            'property_type_id' => PropertyType::query()->firstOrCreate(
                ['key' => 'apt'],
                ['label' => 'Apartment', 'is_active' => true],
            )->id,
            'location_area_id' => LocationArea::query()->firstOrCreate(
                ['name' => 'Gulshan'],
                ['city' => 'Dhaka', 'is_active' => true],
            )->id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_basis' => 'total_sale',
            'area_unit' => 'sqft',
            ...$attributes,
        ]);

        $property->publicationState()->update(['status' => PublicationState::PUBLISHED]);

        return $property;
    }
}
