<?php

namespace Tests\Feature\Inventory;

use App\Contracts\InventoryService;
use App\Models\Media;
use App\Models\Project;
use App\Models\Property;
use App\Models\PublicationState;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_property_moves_draft_to_published_then_is_public(): void
    {
        $property = $this->makePublishableProperty(['title' => 'River view duplex']);
        $actor = $this->staff(Role::OWNER_ADMIN);
        $inventory = app(InventoryService::class);

        $inventory->submitForReview($property, $actor);
        $inventory->approve($property, $actor);
        $inventory->publishProperty($property, $actor);

        $this->assertSame(PublicationState::PUBLISHED, $property->fresh()->editorialStatus());
        $this->get(route('properties.show', $property->slug))->assertOk()->assertSee('River view duplex');
    }

    public function test_publish_checklist_blocks_incomplete_listings(): void
    {
        $property = $this->makeProperty(['title' => 'Missing details']);

        try {
            app(InventoryService::class)->publishProperty($property, $this->staff(Role::OWNER_ADMIN));
            $this->fail('Publishing an incomplete listing should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('Reference number', implode(' ', $exception->errors()['publish'] ?? array_merge(...array_values($exception->errors()))));
        }

        $this->assertNotSame(PublicationState::PUBLISHED, $property->fresh()->editorialStatus());
        $this->get(route('properties.show', $property->slug))->assertNotFound();
    }

    public function test_editor_can_submit_but_cannot_publish_or_approve(): void
    {
        $property = $this->makePublishableProperty();
        $editor = $this->staff(Role::CONTENT_EDITOR);

        $this->actingAs($editor)->post(route('admin.properties.submit', $property))->assertRedirect();
        $this->assertSame(PublicationState::PENDING_REVIEW, $property->fresh()->editorialStatus());

        $this->actingAs($editor)->post(route('admin.properties.approve', $property))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.properties.publish', $property))->assertForbidden();
        $this->actingAs($editor)->postJson(route('admin.properties.publish', $property))->assertForbidden();

        $this->assertSame(PublicationState::PENDING_REVIEW, $property->fresh()->editorialStatus());
    }

    public function test_editor_cannot_edit_a_live_listing(): void
    {
        $property = $this->makePublishableProperty();
        $property->publicationState->update(['status' => PublicationState::PUBLISHED]);
        $editor = $this->staff(Role::CONTENT_EDITOR);

        $this->actingAs($editor)->put(route('admin.properties.update', $property), [
            'version' => $property->version,
            'title' => 'Changed by editor',
        ])->assertForbidden();

        $this->assertNotSame('Changed by editor', $property->fresh()->title);
    }

    public function test_unpublished_property_is_not_on_the_public_site(): void
    {
        $property = $this->makeProperty(['title' => 'Hidden home'], published: true);

        app(InventoryService::class)->unpublishProperty($property, 'Sold privately', $this->staff(Role::OWNER_ADMIN));

        $this->get(route('properties.show', $property->slug))->assertNotFound();
        $this->get(route('properties.index'))->assertOk()->assertDontSee('Hidden home');
    }

    public function test_stale_version_is_rejected_on_update(): void
    {
        $property = $this->makePublishableProperty(['title' => 'Version one']);
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->from(route('admin.properties.edit', $property))->put(route('admin.properties.update', $property), [
            ...$this->formPayload($property),
            'version' => $property->version + 5,
            'title' => 'Stale write',
        ])->assertSessionHasErrors('version');

        $this->assertSame('Version one', $property->fresh()->title);
    }

    public function test_reservation_expiry_returns_the_listing_to_available(): void
    {
        $property = $this->makePublishableProperty();
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->put(route('admin.properties.availability', $property), [
            'availability' => 'reserved',
            'version' => $property->version,
        ])->assertRedirect();

        $this->assertSame('reserved', $property->fresh()->availability);
        $this->assertNotNull($property->fresh()->reservation_expires_at);

        $this->travel((int) config('urbanhaven.inventory.reservation_days') + 1)->days();
        $this->artisan('uh:release-reservations')->assertSuccessful();

        $this->assertSame('available', $property->fresh()->availability);
        $this->assertDatabaseHas('property_status_history', ['property_id' => $property->id, 'field' => 'availability', 'to_value' => 'available']);
    }

    public function test_admin_property_create_and_edit_forms_render(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $property = $this->makePublishableProperty(['title' => 'Editable home']);

        $this->actingAs($owner)->get(route('admin.properties.create'))
            ->assertOk()
            ->assertSee('Save draft')
            ->assertSee('Cover photograph');
        $this->actingAs($owner)->get(route('admin.properties.edit', $property))
            ->assertOk()
            ->assertSee('Editable home')
            ->assertSee('Update availability')
            ->assertSee('All publishing requirements are met.');
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    public function test_owner_can_create_illustrate_and_publish_a_project_from_the_admin(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.projects.store'), [
            'name' => 'Lakeshore Residences',
            'description' => 'Twelve storey residential building beside the lake.',
            'development_stage' => 'ongoing',
            'city' => 'Dhaka',
        ])->assertRedirect();

        $project = Project::query()->where('name', 'Lakeshore Residences')->firstOrFail();

        $this->get(route('admin.projects.edit', $project))
            ->assertOk()
            ->assertSee('Before publishing, add:')
            ->assertSee('Cover image')
            ->assertSee('Photographs');

        $this->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('front.jpg', 1200, 800),
            'owner_type' => 'project',
            'owner_id' => $project->id,
            'collection' => 'gallery',
        ])->assertRedirect();

        $media = Media::query()->firstOrFail();
        $this->assertFalse($media->is_public);

        $this->patch(route('admin.media.update', $media), [
            'alt_text' => 'Front elevation of Lakeshore Residences',
            'is_public' => 1,
            'make_cover' => 1,
        ])->assertRedirect();

        $this->assertSame($media->id, $project->fresh()->featured_media_id);

        $this->post(route('admin.projects.publish', $project))->assertRedirect();

        $this->assertSame(PublicationState::PUBLISHED, $project->fresh()->editorialStatus());
    }

    public function test_project_video_must_be_a_youtube_or_vimeo_link(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.projects.store'), [
            'name' => 'River View',
            'development_stage' => 'upcoming',
            'city' => 'Dhaka',
            'video_url' => 'https://example.com/tour.mp4',
        ])->assertSessionHasErrors('video_url');

        $this->actingAs($owner)->post(route('admin.projects.store'), [
            'name' => 'River View',
            'development_stage' => 'upcoming',
            'city' => 'Dhaka',
            'video_url' => 'https://vimeo.com/76979871',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('https://vimeo.com/76979871', Project::query()->where('name', 'River View')->value('video_url'));
    }

    public function test_editor_sees_a_read_only_project_editor_without_publish_actions(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $this->actingAs($owner)->post(route('admin.projects.store'), [
            'name' => 'Garden Court',
            'development_stage' => 'upcoming',
            'city' => 'Dhaka',
        ]);
        $project = Project::query()->where('name', 'Garden Court')->firstOrFail();

        $this->actingAs($this->staff(Role::CONTENT_EDITOR))
            ->get(route('admin.projects.edit', $project))
            ->assertOk()
            ->assertSee('Submit for review')
            ->assertDontSee('>Publish</button>', false);

        $this->post(route('admin.projects.publish', $project))->assertForbidden();
    }

    private function formPayload(Property $property): array
    {
        return [
            'title' => $property->title,
            'property_type_id' => $property->property_type_id,
            'location_area_id' => $property->location_area_id,
            'listing_type' => $property->listing_type,
            'availability' => $property->availability,
            'price_mode' => 'fixed',
            'price' => 9_500_000,
            'price_basis' => 'total_sale',
            'area_value' => 1450,
            'area_unit' => 'sqft',
        ];
    }
}
