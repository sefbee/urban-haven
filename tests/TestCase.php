<?php

namespace Tests;

use App\Models\LocationArea;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PublicationState;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function assignRole(User $user, string $key): void
    {
        $role = Role::query()->firstOrCreate(
            ['key' => $key],
            ['label' => str_replace('_', ' ', $key)],
        );

        $user->roles()->syncWithoutDetaching([$role->id]);
        $user->unsetRelation('roles');
    }

    protected function makeProperty(array $overrides = [], bool $published = false): Property
    {
        $area = LocationArea::query()->first() ?? LocationArea::query()->create([
            'name' => 'Gulshan',
            'city' => 'Dhaka',
            'is_active' => true,
        ]);
        $type = PropertyType::query()->first() ?? PropertyType::query()->create([
            'key' => 'apartment',
            'label' => 'Apartment',
            'is_active' => true,
        ]);

        $property = Property::query()->create(array_merge([
            'title' => 'Test listing',
            'property_type_id' => $type->id,
            'location_area_id' => $area->id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_basis' => 'total_sale',
            'area_unit' => 'sqft',
        ], $overrides));

        if ($published) {
            $property->publicationState()->update(['status' => PublicationState::PUBLISHED]);
        }

        return $property->fresh(['publicationState']);
    }
}
