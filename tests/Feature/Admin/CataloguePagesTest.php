<?php

namespace Tests\Feature\Admin;

use App\Models\Amenity;
use App\Models\LocationArea;
use App\Models\PropertyType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CataloguePagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_editor_can_manage_areas_from_the_dedicated_page(): void
    {
        $editor = User::factory()->create();
        $this->assignRole($editor, Role::CONTENT_EDITOR);

        $this->actingAs($editor)
            ->get(route('admin.areas.index'))
            ->assertOk()
            ->assertSee('Add an area')
            ->assertDontSee(route('admin.settings.index'), false);

        $this->actingAs($editor)
            ->post(route('admin.areas.store'), ['name' => 'Uttara', 'city' => 'Dhaka'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('location_areas', ['name' => 'Uttara', 'city' => 'Dhaka', 'is_active' => true]);
    }

    public function test_editor_can_add_a_property_type_and_an_amenity(): void
    {
        $editor = User::factory()->create();
        $this->assignRole($editor, Role::CONTENT_EDITOR);

        $this->actingAs($editor)
            ->post(route('admin.property-types.store'), [
                'key' => 'studio',
                'label' => 'Studio',
                'category' => 'residential',
                'field_profile' => 'apartment',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->actingAs($editor)
            ->post(route('admin.amenities.store'), ['key' => 'rooftop', 'label' => 'Rooftop'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('property_types', ['key' => 'studio', 'label' => 'Studio']);
        $this->assertDatabaseHas('amenities', ['key' => 'rooftop', 'label' => 'Rooftop']);
    }

    public function test_sub_types_are_listed_under_the_two_main_types(): void
    {
        $editor = User::factory()->create();
        $this->assignRole($editor, Role::CONTENT_EDITOR);
        PropertyType::query()->create(['key' => 'apartment', 'label' => 'Apartment', 'category' => 'residential', 'field_profile' => 'apartment', 'is_active' => true]);
        PropertyType::query()->create(['key' => 'office', 'label' => 'Office', 'category' => 'commercial', 'field_profile' => 'commercial', 'is_active' => true]);

        $this->actingAs($editor)
            ->get(route('admin.property-types.index'))
            ->assertOk()
            ->assertSeeInOrder(['Residential', 'Apartment', 'Commercial', 'Office']);

        $this->actingAs($editor)
            ->post(route('admin.property-types.store'), [
                'key' => 'farm',
                'label' => 'Farm',
                'category' => 'land',
                'field_profile' => 'plot',
            ])
            ->assertSessionHasErrors('category');

        $this->assertDatabaseMissing('property_types', ['key' => 'farm']);
    }

    public function test_staff_can_create_catalogue_items_as_json_for_quick_add(): void
    {
        $editor = User::factory()->create();
        $this->assignRole($editor, Role::CONTENT_EDITOR);

        $this->actingAs($editor)
            ->postJson(route('admin.areas.store'), ['name' => 'Uttara Sector 7', 'city' => 'Dhaka'])
            ->assertCreated()
            ->assertJsonPath('label', 'Uttara Sector 7 — Dhaka');

        $this->actingAs($editor)
            ->postJson(route('admin.property-types.store'), [
                'key' => 'penthouse',
                'label' => 'Penthouse',
                'category' => 'residential',
                'field_profile' => 'apartment',
            ])
            ->assertCreated()
            ->assertJsonPath('label', 'Penthouse')
            ->assertJsonPath('profile', 'apartment');

        $this->actingAs($editor)
            ->postJson(route('admin.amenities.store'), ['key' => 'concierge', 'label' => 'Concierge'])
            ->assertCreated()
            ->assertJsonPath('label', 'Concierge');
    }

    public function test_new_property_form_offers_quick_add_for_dynamic_selects(): void
    {
        $editor = User::factory()->create();
        $this->assignRole($editor, Role::CONTENT_EDITOR);

        $this->actingAs($editor)
            ->get(route('admin.properties.create'))
            ->assertOk()
            ->assertSee('Add a type', false)
            ->assertSee('Add an area', false)
            ->assertSee('Add a project', false)
            ->assertSee('Add an amenity', false);
    }

    public function test_sales_staff_cannot_open_catalogue_pages(): void
    {
        $sales = User::factory()->create();
        $this->assignRole($sales, Role::SALES_USER);

        foreach (['admin.areas.index', 'admin.property-types.index', 'admin.amenities.index', 'admin.listing-display'] as $name) {
            $this->actingAs($sales)->get(route($name))->assertForbidden();
        }

        $this->actingAs($sales)
            ->postJson(route('admin.areas.store'), ['name' => 'Banani', 'city' => 'Dhaka'])
            ->assertForbidden();
    }

    public function test_settings_page_no_longer_hosts_the_catalogue(): void
    {
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);
        LocationArea::query()->create(['name' => 'Banani', 'city' => 'Dhaka', 'is_active' => true]);
        PropertyType::query()->create(['key' => 'flat', 'label' => 'Flat', 'category' => 'residential', 'field_profile' => 'apartment', 'is_active' => true]);
        Amenity::query()->create(['key' => 'pool', 'label' => 'Pool', 'is_active' => true]);

        $this->actingAs($owner)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Identity')
            ->assertSee('Logos and icons')
            ->assertDontSee('Neighbourhoods used on listings');

        $this->actingAs($owner)
            ->get(route('admin.settings.enquiries'))
            ->assertOk()
            ->assertSee('Enquiries and follow-up')
            ->assertSee('Email delivery');

        $this->actingAs($owner)
            ->get(route('admin.listing-display'))
            ->assertOk()
            ->assertSee('Show properties for sale')
            ->assertSee('Public address and map pin');
    }
}
