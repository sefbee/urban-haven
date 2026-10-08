<?php

namespace Tests\Feature\Public;

use App\Models\Amenity;
use App\Models\CmsPage;
use App\Models\Faq;
use App\Models\LocationArea;
use App\Models\Post;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyMetricDaily;
use App\Models\PropertyType;
use App\Models\PublicationState;
use App\Models\Setting;
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
        $live->publicationState->update(['status' => PublicationState::PUBLISHED]);

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

    public function test_list_page_links_to_the_dedicated_map_instead_of_embedding_one(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $this->publishProperty('Sale home', ['lat' => 23.81, 'lng' => 90.41]);

        $this->get(route('properties.index', ['listing_type' => 'sale']))
            ->assertOk()
            ->assertDontSee('data-uh-map', false)
            ->assertSee(route('map', ['listing_type' => 'sale']), false);
    }

    public function test_map_page_shows_the_filtered_homes_beside_the_map(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $this->publishProperty('Sale home', ['listing_type' => 'sale', 'lat' => 23.81, 'lng' => 90.41]);
        $this->publishProperty('Rent home', ['listing_type' => 'rent', 'price_basis' => 'monthly_rent', 'lat' => 23.75, 'lng' => 90.37]);

        $this->get(route('map', ['listing_type' => 'sale']))
            ->assertOk()
            ->assertSee('data-uh-map', false)
            ->assertSee('data-src=', false)
            ->assertSee('id="property-filters"', false)
            ->assertSee('Sale home')
            ->assertDontSee('Rent home');
    }

    public function test_map_pins_carry_the_details_shown_in_the_popup_card(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $this->publishProperty('Pinned home', ['lat' => 23.81, 'lng' => 90.41, 'bedrooms' => 3, 'bathrooms' => 2, 'area_value' => 1850]);

        $this->getJson(route('properties.map'))
            ->assertOk()
            ->assertJsonPath('points.0.title', 'Pinned home')
            ->assertJsonPath('points.0.place', 'Gulshan, Dhaka')
            ->assertJsonPath('points.0.beds', 3)
            ->assertJsonPath('points.0.baths', 2)
            ->assertJsonPath('points.0.furnishing', 'Unfurnished');
    }

    public function test_location_suggestions_match_active_areas_with_live_counts(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $gulshan = LocationArea::query()->create(['name' => 'Gulshan', 'city' => 'Dhaka', 'is_active' => true]);
        LocationArea::query()->create(['name' => 'Gulshan Lake', 'city' => 'Dhaka', 'is_active' => false]);
        LocationArea::query()->create(['name' => 'Badda', 'city' => 'Dhaka', 'is_active' => true]);
        $this->publishProperty('Gulshan listing', ['location_area_id' => $gulshan->id]);

        $this->getJson(route('search.locations', ['q' => 'gul']))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.id', $gulshan->id)
            ->assertJsonPath('results.0.text', 'Gulshan')
            ->assertJsonPath('results.0.count', 1);

        $this->getJson(route('search.locations', ['q' => '%']))->assertOk()->assertJsonCount(0, 'results');
    }

    public function test_home_search_suggestions_include_matching_live_properties_on_request(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $gulshan = LocationArea::query()->create(['name' => 'Gulshan', 'city' => 'Dhaka', 'is_active' => true]);
        $match = $this->publishProperty('Lake view duplex', ['location_area_id' => $gulshan->id]);
        $this->publishProperty('Corner office floor');
        $this->publishProperty('Lake view sold flat', ['availability' => 'sold']);

        $this->getJson(route('search.locations', ['q' => 'gul']))->assertOk()->assertJsonMissingPath('properties');

        $this->getJson(route('search.locations', ['q' => 'lake view', 'include' => 'properties']))
            ->assertOk()
            ->assertJsonCount(1, 'properties')
            ->assertJsonPath('properties.0.title', 'Lake view duplex')
            ->assertJsonPath('properties.0.url', route('properties.show', $match->slug))
            ->assertJsonPath('properties.0.place', 'Gulshan, Dhaka');

        $this->getJson(route('search.locations', ['include' => 'everything']))->assertUnprocessable();
    }

    public function test_search_narrows_by_main_type_and_several_sub_types(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $apartment = PropertyType::query()->create(['key' => 'apartment', 'label' => 'Apartment', 'category' => 'residential', 'is_active' => true]);
        $duplex = PropertyType::query()->create(['key' => 'duplex', 'label' => 'Duplex', 'category' => 'residential', 'is_active' => true]);
        $office = PropertyType::query()->create(['key' => 'office', 'label' => 'Office', 'category' => 'commercial', 'field_profile' => 'commercial', 'is_active' => true]);
        $this->publishProperty('Garden apartment', ['property_type_id' => $apartment->id]);
        $this->publishProperty('Corner duplex', ['property_type_id' => $duplex->id]);
        $this->publishProperty('Glass office floor', ['property_type_id' => $office->id]);

        $this->get(route('properties.index', ['category' => 'commercial']))
            ->assertOk()
            ->assertSee('Glass office floor')
            ->assertDontSee('Garden apartment')
            ->assertDontSee('Corner duplex');

        $this->get(route('properties.index', ['property_type_ids' => [$apartment->id, $duplex->id]]))
            ->assertOk()
            ->assertSee('Garden apartment')
            ->assertSee('Corner duplex')
            ->assertDontSee('Glass office floor');

        $this->get(route('properties.index', ['property_type_id' => $duplex->id]))
            ->assertOk()
            ->assertSee('Corner duplex')
            ->assertDontSee('Garden apartment');

        $this->getJson(route('properties.count', ['category' => 'residential']))->assertOk()->assertJsonPath('total', 2);
        $this->getJson(route('properties.count', ['category' => 'commercial', 'property_type_ids' => [$apartment->id]]))->assertOk()->assertJsonPath('total', 0);
        $this->getJson(route('properties.count', ['category' => 'land']))->assertUnprocessable();
    }

    public function test_property_page_switches_between_photos_video_and_map(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $property = $this->publishProperty('Media home', [
            'lat' => 23.81,
            'lng' => 90.41,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->get(route('properties.show', $property->slug))
            ->assertOk()
            ->assertSee('uh-stage-switch', false)
            ->assertSee("view = 'video'", false)
            ->assertSee("view = 'map'", false);

        $plain = $this->publishProperty('Plain home');

        $this->get(route('properties.show', $plain->slug))
            ->assertOk()
            ->assertDontSee('uh-stage-switch', false);
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

    public function test_header_exposes_primary_navigation_without_customer_accounts(): void
    {
        $page = CmsPage::query()->create([
            'slug' => 'about',
            'title' => 'About Urban Haven',
            'body' => '<p>We sell and rent our own properties in Dhaka.</p>',
            'status' => 'published',
        ]);
        $page->publicationState->update(['status' => PublicationState::PUBLISHED]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Buy')
            ->assertSee('Rent')
            ->assertDontSee(url('/projects'))
            ->assertSee('Articles')
            ->assertSee('About Us')
            ->assertSee('Contact')
            ->assertDontSee('Sign In / Sign Up')
            ->assertDontSee('Post Property');

        $this->get(route('cms.show', 'about'))
            ->assertOk()
            ->assertSee('About Urban Haven')
            ->assertSee('We sell and rent our own properties in Dhaka.');
    }

    public function test_home_hero_offers_sale_rent_search_without_statement_section(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Buy')
            ->assertSee('Rent')
            ->assertSee('Location')
            ->assertSee('Property type')
            ->assertSee('Price range')
            ->assertDontSee('Bedrooms')
            ->assertSee('Popular categories')
            ->assertSee('Explore properties')
            ->assertDontSee('No marketplace. No unknown sellers.')
            ->assertSee('Why Urban Haven?')
            ->assertDontSee('10,000+')
            ->assertDontSee('Post Property');
    }

    public function test_home_hero_counts_category_suggestions_per_purpose(): void
    {
        $apartment = PropertyType::query()->create(['key' => 'apartment', 'label' => 'Apartment', 'is_active' => true]);
        $office = PropertyType::query()->create(['key' => 'office', 'label' => 'Office', 'is_active' => true]);
        $this->publishProperty('Sale apartment one', ['property_type_id' => $apartment->id]);
        $this->publishProperty('Sale apartment two', ['property_type_id' => $apartment->id]);
        $this->publishProperty('Rented office', ['property_type_id' => $office->id, 'listing_type' => 'rent', 'price_basis' => 'monthly_rent']);

        $this->get('/')
            ->assertOk()
            ->assertViewHas('types', fn ($types): bool => $types->firstWhere('id', $apartment->id)->sale_listings_count === 2
                && $types->firstWhere('id', $apartment->id)->rent_listings_count === 0
                && $types->firstWhere('id', $office->id)->rent_listings_count === 1);
    }

    public function test_home_arranges_sections_in_order(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        Setting::set('phone', '+8801711000000', 'contact');
        Setting::set('whatsapp', '+8801711000000', 'contact');
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
        $project->publicationState->update(['status' => PublicationState::PUBLISHED]);

        $post = Post::factory()->create(['title' => 'How to inspect a flat before buying']);
        $post->publicationState->update(['status' => PublicationState::PUBLISHED, 'published_at' => now()]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder([
                'Explore properties',
                'Housing & Apartment Projects in Bangladesh',
                'Featured Real Estate in Bangladesh',
                'Trending Properties in Bangladesh',
                'Explore Real Estate in Bangladesh',
                'Explore Real Estate in Bangladesh type wise',
                'Latest Property Listings in Bangladesh',
                'Can’t find what you’re looking for?',
                'News & Blog Updates',
                'How to inspect a flat before buying',
                'asked questions.',
            ])
            ->assertSee('Gulshan featured sale')
            ->assertSee('Quiet featured rental')
            ->assertSee('Banani latest sale')
            ->assertSee('Newest rental floor')
            ->assertDontSee('Not sure where to start?')
            ->assertDontSee('Explore Urban Haven')
            ->assertSee('Apartment')
            ->assertSee('Explore this home')
            ->assertSee('aria-label="Call"', false)
            ->assertSee('aria-label="WhatsApp"', false)
            ->assertSee('aria-label="Share"', false)
            ->assertSee('tel:+8801711000000', false)
            ->assertSee('wa.me/8801711000000', false)
            ->assertDontSee('<span class="mt-5 block text-lg font-semibold tracking-tight">Unused Land Type</span>', false)
            ->assertDontSee('Featured Projects')
            ->assertDontSee('View Project')
            ->assertDontSee('Completed Projects')
            ->assertDontSee('Post Property');
    }

    public function test_home_trending_ranks_listings_by_recent_views(): void
    {
        $quiet = $this->publishProperty('Quiet listing');
        $popular = $this->publishProperty('Popular listing');
        $stale = $this->publishProperty('Once popular listing');
        PropertyMetricDaily::query()->insert([
            ['property_id' => $popular->id, 'date' => now()->toDateString(), 'views' => 40, 'cta_clicks' => 0],
            ['property_id' => $quiet->id, 'date' => now()->subDays(2)->toDateString(), 'views' => 5, 'cta_clicks' => 0],
            ['property_id' => $stale->id, 'date' => now()->subDays(45)->toDateString(), 'views' => 900, 'cta_clicks' => 0],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertViewHas('trending', fn ($trending): bool => $trending->pluck('id')->all() === [$popular->id, $quiet->id, $stale->id])
            ->assertSee('uh-trending-grid', false)
            ->assertSee('uh-trending-details', false)
            ->assertSee('View Details')
            ->assertDontSee('aria-label="Trending property"', false);
    }

    public function test_home_housing_section_shows_only_live_residential_homes_and_apartments(): void
    {
        $apartment = PropertyType::query()->create([
            'key' => 'home-apartment',
            'label' => 'Apartment',
            'category' => PropertyType::CATEGORY_RESIDENTIAL,
            'field_profile' => PropertyType::PROFILE_APARTMENT,
            'is_active' => true,
        ]);
        $plot = PropertyType::query()->create([
            'key' => 'home-plot',
            'label' => 'Plot',
            'category' => PropertyType::CATEGORY_RESIDENTIAL,
            'field_profile' => PropertyType::PROFILE_PLOT,
            'is_active' => true,
        ]);
        $office = PropertyType::query()->create([
            'key' => 'home-office',
            'label' => 'Office',
            'category' => PropertyType::CATEGORY_COMMERCIAL,
            'field_profile' => PropertyType::PROFILE_COMMERCIAL,
            'is_active' => true,
        ]);

        $apartmentListing = $this->publishProperty('Residential apartment', ['property_type_id' => $apartment->id]);
        $this->publishProperty('Residential plot', ['property_type_id' => $plot->id]);
        $this->publishProperty('Commercial office', ['property_type_id' => $office->id]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Housing & Apartment Projects in Bangladesh')
            ->assertViewHas('housingListings', fn ($listings): bool => $listings->pluck('id')->all() === [$apartmentListing->id]);
    }

    public function test_home_types_are_grouped_under_main_type_tabs(): void
    {
        PropertyType::query()->create(['key' => 'apartment', 'label' => 'Apartment', 'category' => PropertyType::CATEGORY_RESIDENTIAL, 'is_active' => true]);
        PropertyType::query()->create(['key' => 'office', 'label' => 'Office', 'category' => PropertyType::CATEGORY_COMMERCIAL, 'is_active' => true]);
        PropertyType::query()->create(['key' => 'hidden-type', 'label' => 'Retired type', 'category' => PropertyType::CATEGORY_COMMERCIAL, 'is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertViewHas('typeGroups', fn ($groups): bool => $groups->keys()->all() === [PropertyType::CATEGORY_RESIDENTIAL, PropertyType::CATEGORY_COMMERCIAL]
                && $groups[PropertyType::CATEGORY_COMMERCIAL]->pluck('label')->all() === ['Office'])
            ->assertSeeInOrder(['Explore Real Estate in Bangladesh type wise', 'Residential', 'Commercial', 'View all', 'Apartment', 'Office'])
            ->assertSee(route('properties.index', ['category' => PropertyType::CATEGORY_RESIDENTIAL]), false)
            ->assertDontSee('Retired type');
    }

    public function test_home_previews_the_first_visible_faqs(): void
    {
        foreach (range(1, 6) as $number) {
            Faq::query()->create(['question' => "Question number {$number}?", 'answer' => "<p>Answer {$number}.</p>", 'group' => 'General', 'sort_order' => $number, 'is_visible' => true]);
        }
        Faq::query()->create(['question' => 'Hidden question?', 'answer' => '<p>Hidden.</p>', 'group' => 'General', 'sort_order' => 0, 'is_visible' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['asked questions.', 'Get in touch', 'Question number 1?', 'Question number 5?', 'View all'])
            ->assertSee(route('faq'), false)
            ->assertDontSee('Question number 6?')
            ->assertDontSee('Hidden question?');
    }

    public function test_home_hides_featured_and_latest_sections_when_those_collections_are_empty(): void
    {
        $this->seed(RolesPermissionsSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Housing & Apartment Projects in Bangladesh')
            ->assertDontSee('Featured Projects')
            ->assertDontSee('Featured Real Estate in Bangladesh')
            ->assertDontSee('Latest Property Listings in Bangladesh')
            ->assertDontSee('Explore Real Estate in Bangladesh type wise')
            ->assertDontSee('Explore Real Estate in Bangladesh');

        $this->publishProperty('Only a featured sale', ['is_featured' => true, 'listing_type' => 'sale']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Housing & Apartment Projects in Bangladesh')
            ->assertSee('Featured Real Estate in Bangladesh')
            ->assertSee('Only a featured sale')
            ->assertDontSee('Latest Property Listings in Bangladesh');
    }

    public function test_projects_are_not_part_of_the_public_site(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $area = LocationArea::query()->create(['name' => 'Gulshan', 'city' => 'Dhaka', 'is_active' => true, 'intro' => 'Leafy diplomatic quarter.']);
        $project = Project::query()->create([
            'name' => 'Haven Court Residences',
            'development_stage' => 'ongoing',
            'city' => 'Dhaka',
            'location_area_id' => $area->id,
            'developer_name' => 'Urban Haven Properties Ltd.',
        ]);
        $project->publicationState->update(['status' => PublicationState::PUBLISHED]);
        $property = $this->publishProperty('Gulshan lake flat', ['location_area_id' => $area->id, 'project_id' => $project->id]);

        $this->get('/projects')->assertStatus(301)->assertRedirect(route('properties.index'));
        $this->get('/projects/'.$project->slug)->assertStatus(301)->assertRedirect(route('properties.index'));
        $this->get(route('properties.show', $property->slug))->assertOk()->assertDontSee('Haven Court Residences');
        $this->get(route('locations.show', $area->slug))->assertOk()->assertSee('Gulshan lake flat')->assertDontSee('Haven Court Residences');
        $this->get(route('sitemap'))->assertOk()->assertDontSee('projects.xml');
        $this->get(route('sitemap.section', 'pages'))->assertOk()->assertDontSee(url('/projects'));
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
            ->assertDontSee('id="emi"', false)
            ->assertDontSee('id="location"', false)
            ->assertSee('href="#contact"', false)
            ->assertSee('id="contact"', false)
            ->assertSee('Enquire')
            ->assertSee('Book a visit');
    }

    public function test_listing_page_names_the_place_and_offers_a_quick_view(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        config(['urbanhaven.whatsapp.number' => '8801711000000']);
        $property = $this->publishProperty('Gulshan home', ['price' => 7_600_000, 'reference' => 'UH-76']);

        Setting::set('phone', '+8801711000000', 'contact');
        Setting::set('whatsapp', '+8801711000000', 'contact');

        $this->get(route('properties.index', [
            'listing_type' => 'sale',
            'location_area_id' => $property->location_area_id,
        ]))
            ->assertOk()
            ->assertSee('Homes for sale in Gulshan')
            ->assertSee('Quick view')
            ->assertSee('Explore this home')
            ->assertSee('aria-label="Call"', false)
            ->assertSee('aria-label="WhatsApp"', false)
            ->assertSee('aria-label="Share"', false)
            ->assertSee('BDT 76 lakh')
            ->assertSee('wa.me/8801711000000', false)
            ->assertSee('tel:+8801711000000', false)
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

        $property->publicationState->update(['status' => PublicationState::PUBLISHED]);

        return $property;
    }
}
