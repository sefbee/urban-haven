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

    public function test_listing_page_names_the_place_and_offers_a_quick_view(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        config(['urbanhaven.whatsapp.number' => '8801711000000']);
        $property = $this->publishProperty('Gulshan home', ['price' => 7_600_000, 'reference' => 'UH-76']);

        $this->get(route('properties.index', [
            'listing_type' => 'sale',
            'location_area_id' => $property->location_area_id,
        ]))
            ->assertOk()
            ->assertSee('Homes for sale in Gulshan')
            ->assertSee('Quick view')
            ->assertSee('View details')
            ->assertSee('BDT 76 lakh')
            ->assertSee('wa.me/8801711000000', false)
            ->assertSee('UH-76');
    }

    public function test_property_page_embeds_an_allowed_video_and_ignores_other_hosts(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $allowed = $this->publishProperty('Home with a tour', [
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'lat' => 23.78,
            'lng' => 90.41,
        ]);
        $blocked = $this->publishProperty('Home with a bad link', [
            'video_url' => 'https://evil.example/watch?v=dQw4w9WgXcQ',
        ]);

        $this->get(route('properties.show', $allowed->slug))
            ->assertOk()
            ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('Video')
            ->assertSee('Map')
            ->assertDontSee('evil.example', false);

        $this->get(route('properties.show', $blocked->slug))
            ->assertOk()
            ->assertDontSee('evil.example', false)
            ->assertDontSee('youtube-nocookie.com', false);
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
