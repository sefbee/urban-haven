<?php

namespace Tests\Feature\Media;

use App\Contracts\MediaService;
use App\Models\Media;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_upload_creates_media_and_derivatives(): void
    {
        Storage::fake('public');
        $property = $this->makeProperty();
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        $this->actingAs($user)
            ->postJson(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('home.jpg', 800, 600),
                'owner_type' => 'property',
                'owner_id' => $property->id,
                'collection' => 'gallery',
            ])->assertCreated();

        $media = Media::query()->first();
        $this->assertNotNull($media);
        Storage::disk('public')->assertExists($media->path);
        Storage::disk('public')->assertExists($media->derivativePath(480));
    }

    public function test_oversized_file_returns_422_without_media_record(): void
    {
        Storage::fake('public');
        $property = $this->makeProperty();
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        $this->actingAs($user)
            ->postJson(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('huge.jpg')->size(9000),
                'owner_type' => 'property',
                'owner_id' => $property->id,
            ])->assertStatus(422);

        $this->assertDatabaseCount('media', 0);
    }

    public function test_new_property_form_shows_photograph_field(): void
    {
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        $this->actingAs($user)
            ->get(route('admin.properties.create'))
            ->assertOk()
            ->assertSee('Cover photograph')
            ->assertSee('Upload the cover photograph')
            ->assertSee('name="photograph"', false);
    }

    public function test_creating_a_property_can_attach_a_photograph(): void
    {
        Storage::fake('public');
        $property = $this->makeProperty();
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        $this->actingAs($user)
            ->post(route('admin.properties.store'), [
                'title' => 'Garden apartment',
                'property_type_id' => $property->property_type_id,
                'location_area_id' => $property->location_area_id,
                'listing_type' => 'sale',
                'availability' => 'available',
                'price_mode' => 'fixed',
                'price' => 8500000,
                'price_basis' => 'total_sale',
                'area_value' => 1200,
                'area_unit' => 'sqft',
                'photograph' => UploadedFile::fake()->image('front.jpg', 800, 600),
                'photograph_alt' => 'Front elevation',
            ])
            ->assertRedirect();

        $created = Property::query()->where('title', 'Garden apartment')->first();
        $this->assertNotNull($created);
        $this->assertSame(1, $created->media()->count());
        $this->assertSame('front.jpg', $created->media()->first()->original_filename);
    }

    public function test_reorder_updates_sort_order(): void
    {
        Storage::fake('public');
        $property = $this->makeProperty();
        $service = app(MediaService::class);
        $first = $service->store($property, UploadedFile::fake()->image('a.jpg'), 'gallery');
        $second = $service->store($property, UploadedFile::fake()->image('b.jpg'), 'gallery');

        $service->reorder($property, 'gallery', [$second->id, $first->id]);

        $this->assertSame(0, $second->fresh()->sort_order);
        $this->assertSame(1, $first->fresh()->sort_order);
    }

    public function test_batch_update_persists_multiple_media_and_sets_cover(): void
    {
        Storage::fake('public');
        $property = $this->makeProperty();
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);
        $service = app(MediaService::class);
        $first = $service->store($property, UploadedFile::fake()->image('a.jpg'), 'gallery');
        $second = $service->store($property, UploadedFile::fake()->image('b.jpg'), 'gallery');

        $this->actingAs($user)
            ->patchJson(route('admin.media.batch-update'), [
                'items' => [
                    [
                        'id' => $first->id,
                        'alt_text' => 'Living room with natural light',
                        'is_public' => true,
                        'make_cover' => false,
                    ],
                    [
                        'id' => $second->id,
                        'alt_text' => 'Master bedroom overview',
                        'is_public' => true,
                        'make_cover' => true,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJson(['ok' => true, 'updated' => 2]);

        $this->assertSame('Living room with natural light', $first->fresh()->alt());
        $this->assertTrue($first->fresh()->is_public);
        $this->assertSame('Master bedroom overview', $second->fresh()->alt());
        $this->assertTrue($second->fresh()->is_public);
        $this->assertSame($second->id, $property->fresh()->featured_media_id);
    }

    public function test_reorder_endpoint_updates_media_sort_order(): void
    {
        Storage::fake('public');
        $property = $this->makeProperty();
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);
        $service = app(MediaService::class);
        $first = $service->store($property, UploadedFile::fake()->image('a.jpg'), 'gallery');
        $second = $service->store($property, UploadedFile::fake()->image('b.jpg'), 'gallery');

        $this->actingAs($user)
            ->putJson(route('admin.media.reorder'), [
                'owner_type' => 'property',
                'owner_id' => $property->id,
                'collection' => 'gallery',
                'ordered_ids' => [$second->id, $first->id],
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(0, $second->fresh()->sort_order);
        $this->assertSame(1, $first->fresh()->sort_order);
    }
}
