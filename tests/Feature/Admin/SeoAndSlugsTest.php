<?php

namespace Tests\Feature\Admin;

use App\Models\CmsPage;
use App\Models\LocationArea;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PublicationState;
use App\Models\Redirect;
use App\Models\Role;
use App\Models\User;
use App\Support\PageSeo;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SeoAndSlugsTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const SEO = [
        'seo_focus_keyword' => 'apartment for sale in gulshan',
        'seo_meta_title' => 'Apartment for sale in Gulshan with lake view',
        'seo_meta_description' => 'A bright three-bedroom apartment for sale in Gulshan 2, close to the lake, with parking and a backup generator.',
        'seo_gsc_code' => 'abc123_XYZ-page',
        'seo_noindex' => '0',
    ];

    public function test_property_saves_seo_fields_and_a_custom_permalink(): void
    {
        [$type, $area] = $this->catalogue();

        $this->actingAs($this->owner())
            ->post(route('admin.properties.store'), [...$this->propertyPayload($type, $area), ...self::SEO, 'slug' => 'Gulshan Lake View Flat!'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $property = Property::query()->with('seoOverride')->sole();
        $this->assertSame('gulshan-lake-view-flat', $property->slug);
        $this->assertSame('apartment for sale in gulshan', $property->seoOverride->focus_keyword);
        $this->assertSame(self::SEO['seo_meta_title'], $property->seoOverride->meta_title);
        $this->assertSame('abc123_XYZ-page', $property->seoOverride->gsc_code);
        $this->assertFalse($property->seoOverride->noindex);
    }

    public function test_blank_permalink_is_generated_on_create_and_kept_on_update(): void
    {
        [$type, $area] = $this->catalogue();
        $owner = $this->owner();

        $this->actingAs($owner)->post(route('admin.properties.store'), [...$this->propertyPayload($type, $area), 'slug' => ''])->assertSessionHasNoErrors();
        $property = Property::query()->sole();
        $this->assertSame('three-bed-flat-in-gulshan', $property->slug);

        $this->actingAs($owner)->put(route('admin.properties.update', $property), [
            ...$this->propertyPayload($type, $area),
            'title' => 'Renamed listing',
            'slug' => '',
            'version' => $property->version,
        ])->assertSessionHasNoErrors();

        $this->assertSame('three-bed-flat-in-gulshan', $property->fresh()->slug);
    }

    public function test_changing_a_live_property_permalink_adds_a_redirect_and_rejects_duplicates(): void
    {
        [$type, $area] = $this->catalogue();
        $owner = $this->owner();
        $this->actingAs($owner)->post(route('admin.properties.store'), $this->propertyPayload($type, $area));
        $this->actingAs($owner)->post(route('admin.properties.store'), [...$this->propertyPayload($type, $area), 'title' => 'Other flat']);
        $property = Property::query()->where('title', 'Three bed flat in Gulshan')->sole();
        $property->publicationState->update(['status' => PublicationState::PUBLISHED]);

        $this->actingAs($owner)->put(route('admin.properties.update', $property), [...$this->propertyPayload($type, $area), 'slug' => 'other-flat', 'version' => $property->version])
            ->assertSessionHasErrors('slug');

        $this->actingAs($owner)->put(route('admin.properties.update', $property), [...$this->propertyPayload($type, $area), 'slug' => 'gulshan-flat', 'version' => $property->version])
            ->assertSessionHasNoErrors();

        $this->assertSame('gulshan-flat', $property->fresh()->slug);
        $this->assertTrue(Redirect::query()->where('from_path', '/properties/three-bed-flat-in-gulshan')->where('to_path', '/properties/gulshan-flat')->exists());
    }

    public function test_public_property_page_uses_the_seo_override_and_page_gsc_code(): void
    {
        [$type, $area] = $this->catalogue();
        $this->actingAs($this->owner())->post(route('admin.properties.store'), [...$this->propertyPayload($type, $area), ...self::SEO]);
        $property = Property::query()->sole();
        $property->publicationState->update(['status' => PublicationState::PUBLISHED]);
        auth()->logout();

        $this->get(route('properties.show', $property->slug))
            ->assertOk()
            ->assertSee('<title>'.e(self::SEO['seo_meta_title']), false)
            ->assertSee('<meta name="google-site-verification" content="abc123_XYZ-page">', false)
            ->assertSee(self::SEO['seo_meta_description'], false);
    }

    public function test_project_takes_its_city_from_the_chosen_area(): void
    {
        [, $area] = $this->catalogue();

        $this->actingAs($this->owner())->post(route('admin.projects.store'), [
            'name' => 'Lake Residence',
            'development_stage' => 'ongoing',
            'city' => 'Somewhere else',
            'location_area_id' => $area->id,
            ...self::SEO,
        ])->assertSessionHasNoErrors();

        $project = Project::query()->with('seoOverride')->sole();
        $this->assertSame('Chattogram', $project->city);
        $this->assertSame('lake-residence', $project->slug);
        $this->assertSame(self::SEO['seo_meta_title'], $project->seoOverride->meta_title);
    }

    public function test_article_and_page_save_seo_and_pages_reject_reserved_permalinks(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->post(route('admin.posts.store'), ['title' => 'Buying guide', 'body' => '<p>Text</p>', 'slug' => 'how-to-buy', ...self::SEO])
            ->assertSessionHasNoErrors();
        $post = Post::query()->with('seoOverride')->sole();
        $this->assertSame('how-to-buy', $post->slug);
        $this->assertSame('abc123_XYZ-page', $post->seoOverride->gsc_code);

        $this->actingAs($owner)->post(route('admin.cms.store'), ['title' => 'Map', 'slug' => 'map', 'template' => 'default'])
            ->assertSessionHasErrors('slug');

        $this->actingAs($owner)->post(route('admin.cms.store'), ['title' => 'About us', 'template' => 'default', 'body' => '<p>Hi</p>', ...self::SEO])
            ->assertSessionHasNoErrors();
        $page = CmsPage::query()->with('seoOverride')->sole();
        $this->assertSame('about-us', $page->slug);
        $this->assertSame(self::SEO['seo_meta_description'], $page->seoOverride->meta_description);
    }

    public function test_invalid_gsc_code_and_overlong_meta_title_are_rejected(): void
    {
        $this->actingAs($this->owner())->post(route('admin.posts.store'), [
            'title' => 'Guide',
            'body' => 'x',
            'seo_gsc_code' => '<meta name="x">',
            'seo_meta_title' => str_repeat('a', 71),
        ])->assertSessionHasErrors(['seo_gsc_code', 'seo_meta_title']);
    }

    public function test_area_saves_country_city_permalink_and_redirects_its_old_page(): void
    {
        $area = LocationArea::query()->create(['name' => 'Banani', 'city' => 'Dhaka', 'intro' => 'Leafy and central.', 'is_active' => true]);
        $this->assertSame('banani', $area->slug);

        $this->actingAs($this->owner())->put(route('admin.areas.update', $area), [
            'country' => 'Bangladesh',
            'city' => 'Dhaka',
            'name' => 'Banani DOHS',
            'slug' => 'banani-dohs',
            'intro' => 'Leafy and central.',
            ...self::SEO,
        ])->assertSessionHasNoErrors();

        $area->refresh();
        $this->assertSame('banani-dohs', $area->slug);
        $this->assertSame('Bangladesh', $area->country);
        $this->assertSame(self::SEO['seo_meta_title'], $area->seoOverride->meta_title);
        $this->assertTrue(Redirect::query()->where('from_path', '/locations/banani')->exists());

        $this->get(route('locations.show', 'banani-dohs'))->assertOk()->assertSee('<title>'.e(self::SEO['seo_meta_title']), false);
    }

    public function test_area_store_defaults_the_country_and_returns_place_details_for_quick_add(): void
    {
        $this->actingAs($this->owner())
            ->postJson(route('admin.areas.store'), ['city' => 'Sylhet', 'name' => 'Zindabazar'])
            ->assertCreated()
            ->assertJsonPath('country', 'Bangladesh')
            ->assertJsonPath('city', 'Sylhet')
            ->assertJsonPath('name', 'Zindabazar');
    }

    public function test_types_and_amenities_get_an_automatic_unique_slug(): void
    {
        $owner = $this->owner();
        PropertyType::query()->create(['key' => 'duplex', 'label' => 'Duplex', 'category' => 'residential', 'field_profile' => 'apartment', 'is_active' => true]);

        $this->actingAs($owner)->post(route('admin.property-types.store'), ['label' => 'Duplex', 'category' => 'residential', 'field_profile' => 'apartment'])
            ->assertSessionHasNoErrors();
        $this->actingAs($owner)->post(route('admin.amenities.store'), ['label' => 'Roof Garden'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('property_types', ['key' => 'duplex-2', 'label' => 'Duplex']);
        $this->assertDatabaseHas('amenities', ['key' => 'roof-garden']);
    }

    public function test_article_categories_can_be_renamed_with_seo_and_deleted(): void
    {
        $owner = $this->owner();
        $category = PostCategory::query()->create(['name' => 'Guides']);
        $post = Post::factory()->create(['post_category_id' => $category->id]);

        $this->actingAs($owner)->get(route('admin.post-categories.index'))->assertOk()->assertSee('Guides');

        $this->actingAs($owner)->put(route('admin.post-categories.update', $category), ['name' => 'Buying guides', 'slug' => 'buying-guides', ...self::SEO])
            ->assertSessionHasNoErrors();
        $category->refresh();
        $this->assertSame('buying-guides', $category->slug);
        $this->assertSame(self::SEO['seo_meta_title'], $category->seoOverride->meta_title);

        $this->actingAs($owner)->delete(route('admin.post-categories.destroy', $category))->assertSessionHasNoErrors();
        $this->assertModelMissing($category);
        $this->assertNull($post->fresh()->post_category_id);
    }

    public function test_fixed_page_seo_applies_to_the_unfiltered_page_only(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get(route('admin.page-seo.index'))->assertOk()->assertSee('Property search');

        $this->actingAs($owner)->put(route('admin.page-seo.update'), [
            'route' => 'faq',
            'pages' => ['faq' => [...self::SEO, 'seo_meta_title' => 'Property FAQs answered by our team']],
        ])->assertSessionHasNoErrors();

        $this->assertSame('Property FAQs answered by our team', PageSeo::all()['faq']['meta_title']);
        auth()->logout();

        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('<title>Property FAQs answered by our team', false)
            ->assertSee('content="abc123_XYZ-page"', false);

        $this->actingAs($owner)->put(route('admin.page-seo.update'), ['pages' => ['unknown' => self::SEO]])->assertSessionHasErrors('pages');
    }

    public function test_site_url_directory_lists_public_addresses(): void
    {
        $owner = $this->owner();
        PostCategory::query()->create(['name' => 'News']);
        LocationArea::query()->create(['name' => 'Uttara', 'city' => 'Dhaka', 'is_active' => true]);

        $this->actingAs($owner)->get(route('admin.site-urls'))
            ->assertOk()
            ->assertSee('/locations/uttara')
            ->assertSee('/articles?category=news')
            ->assertSee('Copy URL');
    }

    public function test_property_form_offers_country_city_and_area_in_that_order(): void
    {
        $this->catalogue();

        $this->actingAs($this->owner())->get(route('admin.properties.create'))
            ->assertOk()
            ->assertSeeInOrder(['What are you listing?', 'Where is it?', 'Country', 'City', 'Area', 'Describe it', 'Search engine (SEO)'])
            ->assertSee('Focus keyword')
            ->assertSee('Permalink / URL')
            ->assertSee('Google Search Console (GSC) code');
    }

    /**
     * @return array{0: PropertyType, 1: LocationArea}
     */
    private function catalogue(): array
    {
        return [
            PropertyType::query()->create(['key' => 'apartment', 'label' => 'Apartment', 'category' => 'residential', 'field_profile' => 'apartment', 'is_active' => true]),
            LocationArea::query()->create(['name' => 'Agrabad', 'city' => 'Chattogram', 'is_active' => true]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function propertyPayload(PropertyType $type, LocationArea $area): array
    {
        return [
            'title' => 'Three bed flat in Gulshan',
            'property_type_id' => $type->id,
            'location_area_id' => $area->id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_mode' => 'fixed',
            'price' => 8500000,
            'price_basis' => 'total_sale',
            'area_value' => 1200,
            'area_unit' => 'sqft',
        ];
    }

    private function owner(): User
    {
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);

        return $owner;
    }
}
