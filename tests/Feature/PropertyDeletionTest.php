<?php

namespace Tests\Feature;

use App\Contracts\MediaService;
use App\Models\Lead;
use App\Models\Project;
use App\Models\PublicationState;
use App\Models\Role;
use App\Models\SeoOverride;
use App\Models\SiteVisitRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyDeletionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_deletes_a_property_and_its_private_data_while_retaining_business_records(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $owner = $this->staff(Role::OWNER_ADMIN);
        $property = $this->makePublishableProperty(['title' => 'River View Apartment']);
        $property->publicationState->update(['status' => PublicationState::PUBLISHED]);
        $photo = app(MediaService::class)->store($property, UploadedFile::fake()->image('river-view.jpg'), 'gallery');
        $property->forceFill(['featured_media_id' => $photo->id])->save();
        SeoOverride::query()->create([
            'seoable_type' => $property->getMorphClass(),
            'seoable_id' => $property->id,
            'meta_title' => 'River View Apartment',
        ]);
        $unit = Unit::query()->create(['property_id' => $property->id, 'unit_number' => 'A-101', 'status' => 'available']);
        $lead = Lead::query()->create([
            'type' => 'property_inquiry',
            'name' => 'Interested buyer',
            'phone' => '+8801711111111',
            'phone_hash' => Lead::hashPhone('+8801711111111'),
            'property_id' => $property->id,
            'status' => 'new',
            'priority' => 'medium',
        ]);
        $visit = SiteVisitRequest::query()->create([
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'preferred_at' => now()->addDays(2),
            'status' => SiteVisitRequest::REQUESTED,
        ]);
        $metric = ['property_id' => $property->id, 'date' => now()->toDateString(), 'views' => 3, 'cta_clicks' => 1];
        DB::table('property_metrics_daily')->insert($metric);

        $this->actingAs($owner)->delete(route('admin.properties.destroy', $property))
            ->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('status', 'Property deleted.');

        $this->assertModelMissing($property);
        $this->assertModelMissing($unit);
        $this->assertModelMissing($photo);
        $this->assertDatabaseMissing('publication_states', ['publishable_type' => $property->getMorphClass(), 'publishable_id' => $property->id]);
        $this->assertDatabaseMissing('seo_overrides', ['seoable_type' => $property->getMorphClass(), 'seoable_id' => $property->id]);
        $this->assertDatabaseMissing('property_metrics_daily', $metric);
        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'property_id' => null]);
        $this->assertDatabaseHas('site_visit_requests', ['id' => $visit->id, 'property_id' => null]);
        Storage::disk('public')->assertMissing($photo->path);
        Storage::disk('local')->assertMissing($photo->original_path);
    }

    public function test_shared_media_files_are_not_removed_when_a_property_is_deleted(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $owner = $this->staff(Role::OWNER_ADMIN);
        $property = $this->makePublishableProperty();
        $photo = app(MediaService::class)->store($property, UploadedFile::fake()->image('shared.jpg'), 'gallery');
        $project = Project::query()->create([
            'name' => 'Shared media project',
            'development_stage' => 'ongoing',
            'city' => 'Dhaka',
        ]);
        $sharedPhoto = $photo->replicate();
        $sharedPhoto->mediable_type = $project->getMorphClass();
        $sharedPhoto->mediable_id = $project->id;
        $sharedPhoto->save();

        $this->actingAs($owner)->delete(route('admin.properties.destroy', $property))->assertRedirect();

        $this->assertDatabaseHas('media', ['id' => $sharedPhoto->id, 'mediable_id' => $project->id]);
        Storage::disk('public')->assertExists($photo->path);
        Storage::disk('local')->assertExists($photo->original_path);
    }

    public function test_media_referenced_as_another_listing_cover_is_preserved(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $owner = $this->staff(Role::OWNER_ADMIN);
        $property = $this->makePublishableProperty();
        $photo = app(MediaService::class)->store($property, UploadedFile::fake()->image('referenced-cover.jpg'), 'gallery');
        $project = Project::query()->create([
            'name' => 'Cover reference project',
            'development_stage' => 'ongoing',
            'city' => 'Dhaka',
        ]);
        $project->forceFill(['featured_media_id' => $photo->id])->save();

        $this->actingAs($owner)->delete(route('admin.properties.destroy', $property))->assertRedirect();

        $this->assertDatabaseHas('media', ['id' => $photo->id]);
        $this->assertSame($photo->id, $project->fresh()->featured_media_id);
        Storage::disk('public')->assertExists($photo->path);
        Storage::disk('local')->assertExists($photo->original_path);
    }

    public function test_property_delete_action_uses_a_custom_dialog_on_list_and_edit_pages(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $property = $this->makePublishableProperty(['title' => 'City Centre Residence']);

        $listingResponse = $this->actingAs($owner)->get(route('admin.properties.index'))
            ->assertOk()
            ->assertSee('Delete property?', false)
            ->assertSee('City Centre Residence')
            ->assertSee('Cancel')
            ->assertSee('aria-haspopup="dialog"', false)
            ->assertSee('class="fixed inset-0 m-0 h-dvh w-full max-h-none max-w-none overflow-y-auto border-0 bg-transparent p-0', false)
            ->assertSee('@click.self="cancel()"', false)
            ->assertSee('my-auto max-h-[calc(100dvh-2rem)] w-[min(28rem,100%)] overflow-y-auto', false)
            ->assertSee('method="POST"', false)
            ->assertSee('name="_method" value="DELETE"', false);

        $this->assertSame(0, preg_match('/<dialog\b[^>]*\bopen(?:\s|=|>)/i', $listingResponse->getContent()));

        $this->actingAs($owner)->get(route('admin.properties.edit', $property))
            ->assertOk()
            ->assertSee('Permanently remove this listing')
            ->assertSee('Delete property?', false);
    }

    public function test_staff_without_delete_permission_cannot_delete_a_property(): void
    {
        $property = $this->makePublishableProperty();
        $editor = $this->staff(Role::CONTENT_EDITOR);

        $this->actingAs($editor)->delete(route('admin.properties.destroy', $property))->assertForbidden();

        $this->assertModelExists($property);
    }

    public function test_deleting_an_archived_property_does_not_change_its_status_or_another_property(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $archived = $this->makePublishableProperty(['title' => 'Archived listing']);
        $archived->publicationState->update(['status' => PublicationState::UNPUBLISHED]);
        $other = $this->makePublishableProperty(['title' => 'Keep this listing']);

        $this->actingAs($owner)->delete(route('admin.properties.destroy', $archived))->assertRedirect();

        $this->assertModelMissing($archived);
        $this->assertSame(PublicationState::DRAFT, $other->fresh()->editorialStatus());
    }

    public function test_deleting_the_last_record_on_a_page_returns_to_a_valid_listing_page(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        foreach (range(1, 21) as $number) {
            $this->makeProperty(['title' => 'Paginated property '.$number]);
        }

        $response = $this->actingAs($owner)->get(route('admin.properties.index', ['page' => 2]))->assertOk();
        $lastPageProperties = $response->viewData('properties');
        $this->assertCount(1, $lastPageProperties);

        $this->delete(route('admin.properties.destroy', $lastPageProperties->first()))
            ->assertRedirect(route('admin.properties.index'));

        $this->get(route('admin.properties.index'))->assertOk();
        $this->assertDatabaseCount('properties', 20);
    }

    public function test_nonexistent_property_returns_a_not_found_response(): void
    {
        $this->actingAs($this->staff(Role::OWNER_ADMIN))
            ->delete(route('admin.properties.destroy', ['property' => 'missing-listing']))
            ->assertNotFound();
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }
}
