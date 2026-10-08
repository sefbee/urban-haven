<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UnitManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_add_a_unit_to_a_property(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $property = $this->makePublishableProperty();

        $this->actingAs($owner)->get(route('admin.units.create', $property))->assertOk();

        $this->actingAs($owner)->post(route('admin.units.store', $property), [
            'unit_number' => 'A-101',
            'price' => 5500000,
            'status' => 'available',
        ])->assertRedirect(route('admin.properties.edit', $property))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('units', ['property_id' => $property->id, 'unit_number' => 'A-101', 'status' => 'available']);
    }

    public function test_owner_can_update_a_unit(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $property = $this->makePublishableProperty();
        $unit = Unit::query()->create(['property_id' => $property->id, 'unit_number' => 'B-202', 'status' => 'available']);

        $this->actingAs($owner)->put(route('admin.units.update', $unit), [
            'unit_number' => 'B-202',
            'price' => 6000000,
            'status' => 'sold',
        ])->assertRedirect(route('admin.properties.edit', $property))
            ->assertSessionHasNoErrors();

        $this->assertSame('sold', $unit->fresh()->status);
        $this->assertSame(6000000.0, (float) $unit->fresh()->price);
    }

    public function test_owner_can_delete_a_unit(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $property = $this->makePublishableProperty();
        $unit = Unit::query()->create(['property_id' => $property->id, 'unit_number' => 'C-303', 'status' => 'reserved']);

        $this->actingAs($owner)->delete(route('admin.units.destroy', $unit))
            ->assertRedirect(route('admin.properties.edit', $property));

        $this->assertModelMissing($unit);
    }

    public function test_unit_status_must_be_a_valid_value(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $property = $this->makePublishableProperty();

        $this->actingAs($owner)->post(route('admin.units.store', $property), [
            'unit_number' => 'D-404',
            'status' => 'invalid_status',
        ])->assertSessionHasErrors('status');
    }

    public function test_editor_cannot_add_units_to_a_published_property(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);
        $property = $this->makeProperty([], published: true);

        $this->actingAs($editor)->post(route('admin.units.store', $property), [
            'unit_number' => 'X-001',
            'status' => 'available',
        ])->assertForbidden();
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }
}
