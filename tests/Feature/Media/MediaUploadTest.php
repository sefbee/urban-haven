<?php

namespace Tests\Feature\Media;

use App\Contracts\MediaService;
use App\Models\Media;
use App\Models\Property;
use App\Models\PublicationState;
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
            ->assertSee('Property gallery')
            ->assertSee('Upload the cover photograph')
            ->assertSee('name="photograph"', false);
    }

    public function test_creating_a_property_can_attach_a_photograph(): void
    {
        Storage::fake('public');
        Storage::fake('local');
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
        $this->assertSame(PublicationState::PUBLISHED, $created->editorialStatus());
        $this->assertSame('Front elevation', $created->media()->first()->alt());
        $this->assertSame($created->media()->first()->id, $created->featured_media_id);
    }

    public function test_creating_a_property_persists_a_distinct_cover_and_multiple_gallery_photographs(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $reference = $this->makeProperty();
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        $this->actingAs($user)->post(route('admin.properties.store'), [
            'title' => 'Rooftop residence',
            'property_type_id' => $reference->property_type_id,
            'location_area_id' => $reference->location_area_id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_mode' => 'fixed',
            'price' => 12500000,
            'price_basis' => 'total_sale',
            'area_value' => 1400,
            'area_unit' => 'sqft',
            'photograph' => UploadedFile::fake()->image('cover.jpg', 800, 600),
            'photographs' => [
                UploadedFile::fake()->image('living-room.jpg', 800, 600),
                UploadedFile::fake()->image('balcony.jpg', 800, 600),
            ],
        ])->assertRedirect();

        $created = Property::query()->where('title', 'Rooftop residence')->firstOrFail();
        $images = $created->media()->where('collection', 'gallery')->orderBy('sort_order')->get();

        $this->assertCount(3, $images);
        $this->assertSame('cover.jpg', $created->featuredImage()?->original_filename);
        $this->assertSame(['cover.jpg', 'living-room.jpg', 'balcony.jpg'], $images->pluck('original_filename')->all());
        $this->assertNotSame('', $images[1]->alt());
        $this->assertNotSame('', $images[2]->alt());
    }

    public function test_property_without_manual_alt_text_is_published_with_generated_alt_text(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $reference = $this->makeProperty();
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        $this->actingAs($user)->post(route('admin.properties.store'), [
            'title' => 'Luxury apartment in Gulshan',
            'property_type_id' => $reference->property_type_id,
            'location_area_id' => $reference->location_area_id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_mode' => 'fixed',
            'price' => 8500000,
            'price_basis' => 'total_sale',
            'area_value' => 1200,
            'area_unit' => 'sqft',
            'photograph' => UploadedFile::fake()->image('front.jpg', 800, 600),
        ])->assertRedirect();

        $created = Property::query()->where('title', 'Luxury apartment in Gulshan')->firstOrFail();
        $photo = $created->media()->firstOrFail();

        $this->assertSame(PublicationState::PUBLISHED, $created->editorialStatus());
        $this->assertStringContainsString('Luxury apartment in Gulshan', $photo->alt());
        $this->assertStringContainsString('Gulshan', $photo->alt());
        $this->get(route('properties.show', $created->slug))->assertOk()->assertSee('alt="'.e($photo->alt()).'"', false);
    }

    public function test_property_without_a_cover_image_is_still_published_when_saved(): void
    {
        $reference = $this->makeProperty();
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        $this->actingAs($user)->post(route('admin.properties.store'), [
            'title' => 'Plot available on request',
            'property_type_id' => $reference->property_type_id,
            'location_area_id' => $reference->location_area_id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_mode' => 'on_request',
            'price_basis' => 'total_sale',
            'area_value' => 1200,
            'area_unit' => 'sqft',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $created = Property::query()->where('title', 'Plot available on request')->firstOrFail();

        $this->assertSame(PublicationState::PUBLISHED, $created->editorialStatus());
        $this->get(route('properties.show', $created->slug))->assertOk()->assertSee('Plot available on request');
    }

    public function test_property_gallery_and_floor_plan_uploads_receive_distinct_generated_alt_text(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $property = $this->makeProperty(['title' => 'Riverside plot']);
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        foreach (['gallery', 'floor_plan'] as $collection) {
            $this->actingAs($user)->postJson(route('admin.media.store'), [
                'files' => [
                    UploadedFile::fake()->image($collection.'-one.jpg', 800, 600),
                    UploadedFile::fake()->image($collection.'-two.jpg', 800, 600),
                ],
                'owner_type' => 'property',
                'owner_id' => $property->id,
                'collection' => $collection,
            ])->assertCreated();
        }

        $gallery = $property->media()->where('collection', 'gallery')->orderBy('sort_order')->get();
        $plans = $property->media()->where('collection', 'floor_plan')->orderBy('sort_order')->get();

        $this->assertStringContainsString('Riverside plot', $gallery[0]->alt());
        $this->assertStringContainsString('Property photo 1', $gallery[0]->alt());
        $this->assertStringContainsString('Property photo 2', $gallery[1]->alt());
        $this->assertStringContainsString('Floor plan 1', $plans[0]->alt());
        $this->assertStringContainsString('Floor plan 2', $plans[1]->alt());
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

    public function test_multiple_rounds_of_photo_uploads_accumulate_without_removing_previous_photos(): void
    {
        Storage::fake('public');
        $property = $this->makeProperty();
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        // First round: upload 2 photos
        $response1 = $this->actingAs($user)
            ->postJson(route('admin.media.store'), [
                'files' => [
                    UploadedFile::fake()->image('photo_1.jpg', 800, 600),
                    UploadedFile::fake()->image('photo_2.jpg', 800, 600),
                ],
                'owner_type' => 'property',
                'owner_id' => $property->id,
                'collection' => 'gallery',
            ])
            ->assertCreated()
            ->assertJsonStructure([
                'id',
                'records' => [
                    '*' => ['id', 'url', 'thumb_url', 'is_public', 'original_filename', 'size_bytes'],
                ],
            ]);

        $this->assertCount(2, $response1->json('records'));
        $this->assertSame(2, $property->media()->where('collection', 'gallery')->count());

        // Second round: upload 3 more photos
        $response2 = $this->actingAs($user)
            ->postJson(route('admin.media.store'), [
                'files' => [
                    UploadedFile::fake()->image('photo_3.jpg', 800, 600),
                    UploadedFile::fake()->image('photo_4.jpg', 800, 600),
                    UploadedFile::fake()->image('photo_5.jpg', 800, 600),
                ],
                'owner_type' => 'property',
                'owner_id' => $property->id,
                'collection' => 'gallery',
            ])
            ->assertCreated();

        $this->assertCount(3, $response2->json('records'));

        // All 5 photos exist and belong to the property gallery
        $allMedia = $property->media()->where('collection', 'gallery')->orderBy('sort_order')->get();
        $this->assertCount(5, $allMedia);
        $this->assertSame(
            ['photo_1.jpg', 'photo_2.jpg', 'photo_3.jpg', 'photo_4.jpg', 'photo_5.jpg'],
            $allMedia->pluck('original_filename')->all()
        );
    }
}
