<?php

namespace Tests\Feature\Admin;

use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PropertyCreationInputTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_title_and_description_are_saved_to_their_own_fields_and_generate_the_slug(): void
    {
        $reference = $this->makeProperty();
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);
        $title = 'Three bed apartment with terrace in Gulshan';
        $description = '<p>Bright corner home with a private terrace and separate dining area.</p>';

        $this->actingAs($owner)->post(route('admin.properties.store'), [
            ...$this->validPropertyData($reference),
            'title' => $title,
            'description' => $description,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $created = Property::query()->where('title', $title)->sole();

        $this->assertSame($title, $created->title);
        $this->assertSame($description, $created->description);
        $this->assertSame('three-bed-apartment-with-terrace-in-gulshan', $created->slug);
    }

    public function test_title_longer_than_the_database_column_is_rejected_before_property_creation(): void
    {
        $reference = $this->makeProperty();
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);
        $submittedCount = Property::query()->count();
        $instructionText = 'TASK: Fix the property deletion confirmation modal. '.str_repeat('The save form has fields. ', 8);

        $this->actingAs($owner)->post(route('admin.properties.store'), [
            ...$this->validPropertyData($reference),
            'title' => $instructionText,
            'description' => $instructionText,
        ])->assertSessionHasErrors([
            'title' => 'The property title may not exceed 191 characters.',
        ]);

        $this->assertSame($submittedCount, Property::query()->count());
    }

    /**
     * @return array<string, mixed>
     */
    private function validPropertyData(Property $reference): array
    {
        return [
            'title' => 'Three bed apartment in Gulshan',
            'property_type_id' => $reference->property_type_id,
            'location_area_id' => $reference->location_area_id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_mode' => 'fixed',
            'price' => 8500000,
            'price_basis' => 'total_sale',
            'area_value' => 1200,
            'area_unit' => 'sqft',
        ];
    }
}
