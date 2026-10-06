<?php

namespace Tests\Feature\Public;

use App\Models\CmsPage;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\LocationArea;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\PublicationState;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\SiteVisitRequest;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cached_menus_and_faqs_survive_a_serializing_cache_store(): void
    {
        config(['cache.stores.array.serialize' => true, 'cache.serializable_classes' => false]);
        Cache::forgetDriver('array');

        MenuItem::query()->create(['location' => 'header', 'label' => 'Our projects', 'url' => '/projects', 'sort_order' => 1, 'is_visible' => true]);
        Faq::query()->create(['question' => 'Can I book a weekend visit?', 'answer' => 'Yes, on Fridays and Saturdays.', 'group' => 'Visits', 'sort_order' => 1, 'is_visible' => true]);

        foreach ([1, 2] as $attempt) {
            $this->get(route('home'))->assertOk()->assertSee('Our projects');
            $this->get(route('faq'))->assertOk()->assertSee('Can I book a weekend visit?');
        }
    }

    public function test_contact_page_offers_a_form_call_and_whatsapp_even_with_the_default_template(): void
    {
        Setting::query()->updateOrCreate(['key' => 'phone'], ['value' => '+8801711000000', 'cast' => 'string', 'group' => 'contact']);
        Setting::query()->updateOrCreate(['key' => 'whatsapp'], ['value' => '+8801711000000', 'cast' => 'string', 'group' => 'contact']);
        $page = CmsPage::query()->create(['slug' => 'contact', 'title' => 'Contact', 'template' => 'default', 'body' => '<p>Say hello.</p>', 'status' => 'published']);
        $page->publicationState()->updateOrCreate([], ['status' => PublicationState::PUBLISHED]);

        $this->get(route('cms.show', 'contact'))
            ->assertOk()
            ->assertSee('Chat on WhatsApp')
            ->assertSee('href="https://wa.me/', false)
            ->assertSee('href="tel:', false)
            ->assertSee('name="consent_given"', false)
            ->assertSee('value="general_contact"', false);
    }

    public function test_shortlist_and_compare_pages_render_without_an_account(): void
    {
        $this->get(route('shortlist'))->assertOk()->assertSee('Your shortlist')->assertSee('noindex', false);
        $this->get(route('compare'))->assertOk()->assertSee('Compare properties');
    }

    public function test_saved_cards_endpoint_returns_only_published_listings_in_requested_order(): void
    {
        $first = $this->makeProperty(['title' => 'First saved'], published: true);
        $second = $this->makeProperty(['title' => 'Second saved'], published: true);
        $draft = $this->makeProperty(['title' => 'Secret draft']);

        $response = $this->getJson(route('saved.cards', ['ids' => "{$second->id},{$draft->id},{$first->id}", 'view' => 'compare']))
            ->assertOk()
            ->assertJsonPath('ids', [$second->id, $first->id]);

        $html = $response->json('html');
        $this->assertStringNotContainsString('Secret draft', $html);
        $this->assertLessThan(strpos($html, 'First saved'), strpos($html, 'Second saved'));
    }

    public function test_compare_view_spells_out_trade_offs_between_listings(): void
    {
        $smaller = $this->makeProperty(['title' => 'Smaller home', 'price' => 25_500_000, 'area_value' => 1650, 'bedrooms' => 3], published: true);
        $larger = $this->makeProperty(['title' => 'Larger home', 'price' => 28_500_000, 'area_value' => 1850, 'bedrooms' => 3], published: true);

        $html = $this->getJson(route('saved.cards', ['ids' => "{$smaller->id},{$larger->id}", 'view' => 'compare']))
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('What changes if you choose another', $html);
        $this->assertStringContainsString('200 sq ft more space for BDT 30 lakh more', $html);
        $this->assertStringContainsString('Price per sq ft', $html);
        $this->assertStringContainsString('Best value', $html);
    }

    public function test_saved_cards_endpoint_validates_input(): void
    {
        $this->getJson(route('saved.cards', ['ids' => 'abc', 'view' => 'card']))->assertUnprocessable();
        $this->getJson(route('saved.cards', ['ids' => '1', 'view' => 'evil']))->assertUnprocessable();
        $this->getJson(route('saved.cards', ['ids' => implode(',', range(1, 30)), 'view' => 'card']))->assertUnprocessable();
    }

    public function test_visit_request_creates_a_visit_lead_and_a_pending_visit(): void
    {
        Queue::fake();
        $property = $this->makeProperty(['title' => 'Visit home'], published: true);
        $when = now(config('urbanhaven.display_timezone'))->addDays(3)->setTime(11, 0)->format('Y-m-d H:i');

        $this->postJson(route('visits.store'), [
            'name' => 'Sadia',
            'phone' => '01755555555',
            'property_id' => $property->id,
            'preferred_at' => $when,
            'consent_given' => '1',
            'submission_token' => (string) Str::uuid(),
        ])->assertCreated()->assertJsonPath('event.event', 'visit_request_success');

        $lead = Lead::query()->sole();
        $this->assertSame('visit_request', $lead->type);
        $this->assertDatabaseHas('site_visit_requests', ['lead_id' => $lead->id, 'property_id' => $property->id, 'status' => SiteVisitRequest::REQUESTED]);
    }

    public function test_visit_request_rejects_past_and_far_future_times(): void
    {
        $property = $this->makeProperty([], published: true);
        $payload = fn (string $when): array => [
            'name' => 'Sadia',
            'phone' => '01755555555',
            'property_id' => $property->id,
            'preferred_at' => $when,
            'consent_given' => '1',
            'submission_token' => (string) Str::uuid(),
        ];

        $this->postJson(route('visits.store'), $payload(now()->subDay()->format('Y-m-d H:i')))->assertJsonValidationErrors('preferred_at');
        $this->postJson(route('visits.store'), $payload(now()->addDays(200)->format('Y-m-d H:i')))->assertJsonValidationErrors('preferred_at');
        $this->assertDatabaseCount('site_visit_requests', 0);
    }

    public function test_property_page_shows_visit_and_enquiry_forms_with_consent(): void
    {
        $property = $this->makePublishableProperty(['title' => 'Form home']);
        $property->publicationState->update(['status' => PublicationState::PUBLISHED]);

        $this->get(route('properties.show', $property->slug))
            ->assertOk()
            ->assertSee('name="consent_given"', false)
            ->assertSee('name="submission_token"', false)
            ->assertSee('name="website"', false)
            ->assertSee(route('visits.store'), false)
            ->assertSee('application/ld+json', false)
            ->assertSee('<link rel="canonical" href="'.route('properties.show', $property->slug).'">', false);
    }

    public function test_sold_listing_stays_reachable_but_offers_similar_homes_instead_of_a_visit(): void
    {
        $property = $this->makeProperty(['title' => 'Sold home', 'availability' => 'sold'], published: true);

        $this->get(route('properties.show', $property->slug))
            ->assertOk()
            ->assertSee('Ask about similar properties')
            ->assertDontSee(route('visits.store'), false);

        $this->get(route('properties.index'))->assertOk()->assertDontSee('Sold home');
    }

    public function test_location_landing_page_lists_only_that_area(): void
    {
        $banani = LocationArea::query()->create(['name' => 'Banani', 'city' => 'Dhaka', 'is_active' => true, 'intro' => 'Lake-side residential area north of Gulshan.']);
        $thin = LocationArea::query()->create(['name' => 'Uttara', 'city' => 'Dhaka', 'is_active' => true]);
        $this->makeProperty(['title' => 'Banani flat', 'location_area_id' => $banani->id], published: true);
        $this->makeProperty(['title' => 'Elsewhere flat', 'location_area_id' => $thin->id], published: true);

        $this->get(route('locations.show', $banani->slug))
            ->assertOk()
            ->assertSee('Banani flat')
            ->assertDontSee('Elsewhere flat');

        $this->get(route('locations.show', $thin->slug))->assertNotFound();
    }

    public function test_sitemap_lists_published_pages_only(): void
    {
        $live = $this->makeProperty(['title' => 'Live listing'], published: true);
        $draft = $this->makeProperty(['title' => 'Draft listing']);

        $this->get(route('sitemap'))->assertOk()->assertHeader('Content-Type', 'application/xml');
        $this->get(route('sitemap.section', 'properties'))
            ->assertOk()
            ->assertSee(route('properties.show', $live->slug), false)
            ->assertDontSee(route('properties.show', $draft->slug), false);
    }

    public function test_robots_blocks_everything_outside_production(): void
    {
        $this->get(route('robots'))->assertOk()->assertSee('Disallow: /');
    }

    public function test_robots_in_production_lists_the_sitemap_and_hides_private_paths(): void
    {
        $this->app['env'] = 'production';

        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /thank-you')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_redirects_apply_only_to_missing_paths(): void
    {
        Redirect::query()->create(['from_path' => '/old-listing', 'to_path' => '/properties', 'http_code' => 301, 'is_active' => true]);
        Redirect::query()->create(['from_path' => '/disabled', 'to_path' => '/properties', 'http_code' => 301, 'is_active' => false]);

        $this->get('/old-listing?utm_source=mail')->assertRedirect('/properties?utm_source=mail')->assertStatus(301);
        $this->get('/disabled')->assertNotFound();
    }

    public function test_map_endpoint_returns_pins_for_published_available_listings(): void
    {
        $this->makeProperty(['title' => 'Pinned', 'lat' => 23.79, 'lng' => 90.41], published: true);
        $this->makeProperty(['title' => 'Sold pin', 'lat' => 23.78, 'lng' => 90.40, 'availability' => 'sold'], published: true);
        $this->makeProperty(['title' => 'Draft pin', 'lat' => 23.77, 'lng' => 90.42]);

        $this->getJson(route('properties.map'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('points.0.title', 'Pinned');
    }

    public function test_invalid_search_filters_are_dropped_and_reported_not_fatal(): void
    {
        $this->get(route('properties.index', ['min_price' => 'cheap', 'sort' => 'drop table', 'q' => 'Gulshan']))
            ->assertRedirect(route('properties.index', ['q' => 'Gulshan']));

        $this->followingRedirects()
            ->get(route('properties.index', ['min_price' => 'cheap']))
            ->assertOk()
            ->assertSee('Some filters were not valid and have been ignored');

        $this->getJson(route('properties.map', ['min_price' => 'cheap']))->assertUnprocessable();
    }

    public function test_brochure_of_unpublished_listing_is_not_downloadable(): void
    {
        $draft = $this->makeProperty();
        $brochure = Media::query()->forceCreate([
            'mediable_type' => $draft->getMorphClass(),
            'mediable_id' => $draft->id,
            'collection' => 'brochure',
            'disk' => 'local',
            'path' => 'brochures/test.pdf',
            'original_filename' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 10,
            'is_public' => true,
        ]);

        $this->get(route('media.download', $brochure))->assertNotFound();
    }

    public function test_analytics_scripts_load_only_after_consent(): void
    {
        config(['urbanhaven.analytics.gtm_id' => 'GTM-TEST1']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee("analytics_storage: 'denied'", false)
            ->assertDontSee('googletagmanager.com/gtm.js', false)
            ->assertSee('GTM-TEST1', false);
    }

    public function test_disabled_rent_purpose_is_hidden_from_search(): void
    {
        Setting::query()->updateOrCreate(['key' => 'enable_rent'], ['value' => '0', 'cast' => 'bool', 'group' => 'listings']);
        Setting::forgetCache();
        $this->makeProperty(['title' => 'Rental flat', 'listing_type' => 'rent', 'price_basis' => 'monthly_rent'], published: true);

        $this->get(route('properties.index', ['listing_type' => 'rent']))->assertRedirect(route('properties.index'));
        $this->get(route('properties.index'))->assertOk()->assertDontSee('Rental flat');
        $this->get(route('home'))->assertOk()->assertDontSee('Rental flat');
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options')
            ->assertHeader('Referrer-Policy');
    }
}
