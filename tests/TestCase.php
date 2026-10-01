<?php

namespace Tests;

use App\Models\LocationArea;
use App\Models\Media;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PublicationState;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
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
        if (! Role::query()->where('key', $key)->exists()) {
            $this->seed(RolesPermissionsSeeder::class);
        }

        $role = Role::query()->where('key', $key)->firstOrFail();

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
            $property->publicationState->update(['status' => PublicationState::PUBLISHED]);
        }

        return $property->fresh(['publicationState']);
    }

    /**
     * A draft that satisfies the publish checklist: reference, price, area and a described cover image.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function makePublishableProperty(array $overrides = []): Property
    {
        $property = $this->makeProperty(array_merge([
            'reference' => 'UH-'.random_int(1000, 9999),
            'price_mode' => Property::PRICE_FIXED,
            'price' => 9_500_000,
            'area_value' => 1450,
        ], $overrides));

        $cover = Media::query()->forceCreate([
            'mediable_type' => $property->getMorphClass(),
            'mediable_id' => $property->id,
            'collection' => 'gallery',
            'disk' => 'public',
            'path' => 'media/test/cover.jpg',
            'original_filename' => 'cover.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 1024,
            'is_public' => true,
            'alt_texts' => ['en' => 'Living room with south-facing windows'],
        ]);
        $property->forceFill(['featured_media_id' => $cover->id])->save();

        return $property->fresh(['publicationState', 'media']);
    }
}
