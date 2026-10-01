<?php

namespace Tests\Feature\Public;

use App\Models\Amenity;
use App\Models\CmsPage;
use App\Models\LocationArea;
use App\Models\Project;
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

    public function test_search_filters_by_multiple_location_areas(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $gulshan = LocationArea::query()->create(['name' => 'Gulshan', 'city' => 'Dhaka', 'is_active' => true]);
        $badda = LocationArea::query()->create(['name' => 'Badda', 'city' => 'Dhaka', 'is_active' => true]);
        $this->publishProperty('Gulshan listing', ['location_area_id' => $gulshan->id]);
        $this->publishProperty('Badda listing', ['location_area_id' => $badda->id]);

        $this->get(route('properties.index', ['location_area_ids' => [$gulshan->id, $badda->id]]))
            ->assertOk()
            ->assertSee('Gulshan listing')
            ->assertSee('Badda listing');

        $this->get(route('properties.index', ['location_area_ids' => [$badda->id]]))
            ->assertOk()
            ->assertSee('Badda listing')
            ->assertDontSee('Gulshan listing');
    }

    public function test_tools_and_legal_pages_render(): void
    {
        $this->get(route('tools'))
            ->assertOk()
            ->assertSee('Valuation Tool')
            ->assertSee('EMI Loan Calculator')
            ->assertSee('Contact Urban Haven Agent')
            ->assertDontSee('Post Property');

        $this->get(route('legal'))
            ->assertOk()
            ->assertSee('Legal Services');
    }

    public function test_header_exposes_contact_blog_and_account_entry(): void
    {
        $page = CmsPage::query()->create([
            'slug' => 'blog',
            'title' => 'Blog',
            'body' => '<p>Dhaka buying notes from the desk.</p>',
            'status' => 'published',
        ]);
        $page->publicationState()->update(['status' => PublicationState::PUBLISHED]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Home Loan')
            ->assertSee('About Us')
            ->assertSee('Blog')
            ->assertSee('Valuation Tool')
            ->assertSee('Sign In / Sign Up')
            ->assertSee('Contact Urban Haven')
            ->assertDontSee('Post Property')
            ->assertDontSee('Login / Register');

        $this->get(route('cms.show', 'blog'))
            ->assertOk()
            ->assertSee('Blog')
            ->assertSee('Dhaka buying notes from the desk.');
    }

    public function test_home_hero_offers_sale_rent_search_and_trust_strip(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Find a place that feels like home.')
            ->assertSee('Explore verified properties and carefully selected projects from Urban Haven.')
            ->assertSee('Buy')
            ->assertSee('Rent')
            ->assertSee('Location')
            ->assertSee('Property type')
            ->assertSee('Bedrooms')
            ->assertSee('Price range')
            ->assertSee('Explore Properties')
            ->assertSee('Explore Projects')
            ->assertSee('Verified Properties')
            ->assertSee('Carefully Selected Listings')
            ->assertSee('Prime Locations')
            ->assertSee('Dedicated Support')
            ->assertSee('Why Urban Haven?')
            ->assertSee('Find a Property')
            ->assertSee('Contact Urban Haven')
            ->assertSee('Properties for Sale')
            ->assertSee('All Projects')
            ->assertDontSee('10,000+')
            ->assertDontSee('Post Property')
            ->assertDontSee('Calculate Now')
            ->assertDontSee('Your Perfect Property Awaits');
    }

    public function test_home_arranges_projects_featured_latest_types_and_locations(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $gulshan = LocationArea::query()->create(['name' => 'Gulshan', 'city' => 'Dhaka', 'is_active' => true]);
        $apartment = PropertyType::query()->create(['key' => 'apartment', 'label' => 'Apartment', 'is_active' => true]);
        PropertyType::query()->create(['key' => 'land', 'label' => 'Unused Land Type', 'is_active' => true]);

        $this->publishProperty('Gulshan featured sale', [
            'property_type_id' => $apartment->id,
            'location_area_id' => $gulshan->id,
            'listing_type' => 'sale',
            'price' => 28_500_000,
            'is_featured' => true,
        ]);
        $this->publishProperty('Banani latest sale', [
            'property_type_id' => $apartment->id,
            'listing_type' => 'sale',
            'price' => 18_000_000,
        ]);
        $this->publishProperty('Quiet featured rental', [
            'listing_type' => 'rent',
            'price_basis' => 'monthly_rent',
            'price' => 85_000,
            'is_featured' => true,
        ]);
        $this->publishProperty('Newest rental floor', [
            'listing_type' => 'rent',
            'price_basis' => 'monthly_rent',
            'price' => 70_000,
        ]);

        $project = Project::query()->create([
            'name' => 'Haven Residences Gulshan',
            'development_stage' => 'ongoing',
            'city' => 'Dhaka',
            'location_area_id' => $gulshan->id,
            'developer_name' => 'Urban Haven Properties Ltd.',
            'is_featured' => true,
        ]);
        $project->publicationState()->update(['status' => PublicationState::PUBLISHED]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder([
                'Find a place that feels like home.',
                'Verified Properties',
                'Featured Projects',
                'Haven Residences Gulshan',
                'View Project',
                'Featured Properties',
                'Gulshan featured sale',
                'Quiet featured rental',
                'Latest Properties',
                'Banani latest sale',
                'Newest rental floor',
                'Explore by Property Type',
                'Explore Properties by Location',
                'Why Urban Haven?',
                'Looking for the right property?',
                'Find a Property',
                'Explore Urban Haven',
                'Available Projects',
            ])
            ->assertSee('Under Construction')
            ->assertSee('Apartment')
            ->assertDontSee('<span class="mt-5 block text-lg font-semibold tracking-tight">Unused Land Type</span>', false)
            ->assertDontSee('Completed Projects')
            ->assertDontSee('Post Property');
    }

    public function test_home_hides_featured_and_latest_sections_when_those_collections_are_empty(): void
    {
        $this->seed(RolesPermissionsSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Featured Projects')
            ->assertDontSee('Featured Properties')
            ->assertDontSee('Latest Properties')
            ->assertDontSee('Explore by Property Type')
            ->assertDontSee('Explore Properties by Location');

        $this->publishProperty('Only a featured sale', ['is_featured' => true, 'listing_type' => 'sale']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Featured Properties')
            ->assertSee('Only a featured sale')
            ->assertDontSee('Latest Properties');
    }

    public function test_projects_index_filters_by_development_stage(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $area = LocationArea::query()->create(['name' => 'Gulshan', 'city' => 'Dhaka', 'is_active' => true]);

        $ongoing = Project::query()->create([
            'name' => 'Ongoing Haven Court',
            'development_stage' => 'ongoing',
            'city' => 'Dhaka',
            'location_area_id' => $area->id,
            'developer_name' => 'Urban Haven Properties Ltd.',
        ]);
        $ongoing->publicationState()->update(['status' => PublicationState::PUBLISHED]);

        $completed = Project::query()->create([
            'name' => 'Completed Haven House',
            'development_stage' => 'completed',
            'city' => 'Dhaka',
            'location_area_id' => $area->id,
            'developer_name' => 'Urban Haven Properties Ltd.',
        ]);
        $completed->publicationState()->update(['status' => PublicationState::PUBLISHED]);

        $this->get(route('projects.index', ['development_stage' => 'available']))
            ->assertOk()
            ->assertSee('Ongoing Haven Court')
            ->assertDontSee('Completed Haven House');

        $this->get(route('projects.index', ['development_stage' => 'completed']))
            ->assertOk()
            ->assertSee('Completed Haven House')
            ->assertDontSee('Ongoing Haven Court');
    }

    public function test_search_matches_keyword_against_title_and_area_name(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $banani = LocationArea::query()->create(['name' => 'Banani', 'city' => 'Dhaka', 'is_active' => true]);
        $this->publishProperty('Lakeview duplex', ['location_area_id' => $banani->id]);
        $this->publishProperty('Corner plot in Banani');

        $this->get(route('properties.index', ['q' => 'Lakeview']))
            ->assertOk()
            ->assertSee('Lakeview duplex')
            ->assertDontSee('Corner plot in Banani')
            ->assertSee('Search: Lakeview');

        $this->get(route('properties.index', ['q' => 'Banani']))
            ->assertOk()
            ->assertSee('Lakeview duplex')
            ->assertSee('Corner plot in Banani');
    }

    public function test_search_page_exposes_near_me_and_verification_filters(): void
    {
        $this->get(route('properties.index'))
            ->assertOk()
            ->assertSee('Properties Near Me')
            ->assertSee('More Filters')
            ->assertSee('Verified Listings')
            ->assertSee('Unverified')
            ->assertSee('Semi Furnished')
            ->assertSee('Gravelled')
            ->assertSee('10+ Years');
    }

    public function test_search_filters_by_verification_and_nearby_radius(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $this->publishProperty('Verified Gulshan', [
            'trust_label' => 'Verified',
            'lat' => 23.81,
            'lng' => 90.41,
        ]);
        $this->publishProperty('Plain Badda', [
            'trust_label' => null,
            'lat' => 23.78,
            'lng' => 90.43,
        ]);
        $this->publishProperty('Faraway home', [
            'trust_label' => 'Verified',
            'lat' => 22.35,
            'lng' => 91.83,
        ]);

        $this->get(route('properties.index', ['verification' => ['verified']]))
            ->assertOk()
            ->assertSee('Verified Gulshan')
            ->assertDontSee('Plain Badda');

        $this->get(route('properties.index', ['verification' => ['unverified']]))
            ->assertOk()
            ->assertSee('Plain Badda')
            ->assertDontSee('Verified Gulshan');

        $this->get(route('properties.index', [
            'lat' => 23.81,
            'lng' => 90.41,
            'radius_km' => 3,
        ]))
            ->assertOk()
            ->assertSee('Verified Gulshan')
            ->assertDontSee('Faraway home');
    }

    public function test_property_page_uses_jump_navigation(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $property = $this->publishProperty('Gulshan home', [
            'price' => 12_000_000,
            'description' => 'South facing apartment.',
            'lat' => 23.78,
            'lng' => 90.41,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->get(route('properties.show', $property->slug))
            ->assertOk()
            ->assertSee('href="#overview"', false)
            ->assertSee('href="#description"', false)
            ->assertSee('href="#emi"', false)
            ->assertSee('href="#location"', false)
            ->assertSee('href="#contact"', false)
            ->assertSee('EMI Calculator')
            ->assertSee('Location')
            ->assertSee('Contact')
            ->assertSee('Contact Urban Haven Agent')
            ->assertSee('Inquire Now')
            ->assertSee('Schedule a Viewing');
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
            ->assertSee('Inquire Now')
            ->assertSee('WhatsApp Us')
            ->assertSee('Contact Urban Haven Agent')
            ->assertSee('BDT 76 lakh')
            ->assertSee('wa.me/8801711000000', false)
            ->assertSee('UH-76');
    }

    public function test_search_filters_by_price_band_facing_road_and_area(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $this->publishProperty('Wide south home', [
            'price' => 15_000_000,
            'facing' => 'south',
            'road_width_ft' => 30,
            'area_value' => 1800,
            'area_unit' => 'sqft',
        ]);
        $this->publishProperty('Narrow north home', [
            'price' => 15_000_000,
            'facing' => 'north',
            'road_width_ft' => 8,
            'area_value' => 900,
            'area_unit' => 'sqft',
        ]);

        $this->get(route('properties.index', [
            'price_band' => 'sale-1-2cr',
            'facing' => 'south',
            'min_road_width' => 20,
            'area_band' => '1500-2500',
        ]))
            ->assertOk()
            ->assertSee('Wide south home')
            ->assertDontSee('Narrow north home')
            ->assertSee('More Filters');
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
