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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_property_save_publishes_it_without_a_review_transition(): void
    {
        $property = $this->makePublishableProperty(['title' => 'River view duplex']);
        $actor = $this->staff(Role::OWNER_ADMIN);
        $inventory = app(InventoryService::class);

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
            ->assertSee('Save property')
            ->assertSee('Saving this property publishes it immediately.')
            ->assertDontSee('Submit for review')
            ->assertDontSee('>Publish</button>', false)
            ->assertSee('Cover photograph');
        $this->actingAs($owner)->get(route('admin.properties.edit', $property))
            ->assertOk()
            ->assertSee('Editable home')
            ->assertSee('Update availability')
            ->assertSee('Publication')
            ->assertSee('Saving changes publishes the latest property details immediately.')
            ->assertDontSee('Submit for review')
            ->assertDontSee('>Publish</button>', false);
    }

    public function test_saving_a_legacy_draft_publishes_and_generates_missing_alt_text(): void
    {
        $property = $this->makePublishableProperty(['title' => 'Luxury apartment in Gulshan']);
        $cover = $property->media->first();
        $cover->forceFill(['alt_texts' => []])->save();
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->put(route('admin.properties.update', $property), $this->formPayload($property))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(PublicationState::PUBLISHED, $property->fresh()->editorialStatus());
        $this->assertStringContainsString('Luxury apartment in Gulshan', $cover->fresh()->alt());
        $this->get(route('properties.show', $property->slug))->assertOk()->assertSee('Luxury apartment in Gulshan');
    }

    public function test_saving_an_archived_property_does_not_unarchive_it(): void
    {
        $property = $this->makePublishableProperty();
        $owner = $this->staff(Role::OWNER_ADMIN);
        app(InventoryService::class)->publishProperty($property, $owner);
        app(InventoryService::class)->unpublishProperty($property, 'Archived intentionally', $owner);

        $this->actingAs($owner)->put(route('admin.properties.update', $property), $this->formPayload($property))
            ->assertRedirect();

        $this->assertSame(PublicationState::UNPUBLISHED, $property->fresh()->editorialStatus());
    }

    public function test_legacy_publication_command_is_dry_run_by_default_and_skips_archived_or_incomplete_properties(): void
    {
        $eligible = $this->makePublishableProperty(['title' => 'Eligible legacy property']);
        $eligible->media->first()->forceFill(['alt_texts' => []])->save();
        $incomplete = $this->makeProperty(['title' => 'Incomplete legacy property']);
        $archived = $this->makePublishableProperty(['title' => 'Archived legacy property']);
        $owner = $this->staff(Role::OWNER_ADMIN);
        app(InventoryService::class)->publishProperty($archived, $owner);
        app(InventoryService::class)->unpublishProperty($archived, 'Archived intentionally', $owner);

        $this->artisan('properties:publish-eligible')
            ->expectsOutputToContain('Eligible legacy property')
            ->expectsOutputToContain('skipped: 1')
            ->assertSuccessful();

        $this->assertSame(PublicationState::DRAFT, $eligible->fresh()->editorialStatus());

        $this->artisan('properties:publish-eligible --apply')->assertSuccessful();

        $this->assertSame(PublicationState::PUBLISHED, $eligible->fresh()->editorialStatus());
        $this->assertNotSame('', $eligible->media->first()->fresh()->alt());
        $this->assertSame(PublicationState::DRAFT, $incomplete->fresh()->editorialStatus());
        $this->assertSame(PublicationState::UNPUBLISHED, $archived->fresh()->editorialStatus());
    }

    public function test_archived_status_is_not_available_as_a_property_draft_workflow(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->get(route('admin.properties.create'))
            ->assertOk()
            ->assertDontSee('Save draft')
            ->assertDontSee('Submit for review');

        $this->assertFalse(Route::has('admin.properties.submit'));
        $this->assertFalse(Route::has('admin.properties.publish'));
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
        $this->assertTrue($media->is_public);

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
            'version' => $property->version,
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
