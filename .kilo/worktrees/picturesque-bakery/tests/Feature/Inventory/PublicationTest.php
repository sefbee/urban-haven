<?php

namespace Tests\Feature\Inventory;

use App\Contracts\InventoryService;
use App\Models\PublicationState;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_property_moves_draft_to_published_then_is_public(): void
    {
        $property = $this->makeProperty(['title' => 'River view duplex']);
        $actor = User::factory()->create();
        $this->assignRole($actor, Role::OWNER_ADMIN);
        $inventory = app(InventoryService::class);

        $inventory->submitForReview($property, $actor);
        $inventory->approve($property, $actor);
        $inventory->publishProperty($property, $actor);

        $this->assertSame(PublicationState::PUBLISHED, $property->fresh()->editorialStatus());

        $this->get(route('properties.show', $property->slug))
            ->assertOk()
            ->assertSee('River view duplex');
    }

    public function test_unpublished_property_is_not_on_the_public_site(): void
    {
        $property = $this->makeProperty(['title' => 'Hidden home'], published: true);
        $actor = User::factory()->create();
        $this->assignRole($actor, Role::OWNER_ADMIN);

        app(InventoryService::class)->unpublishProperty($property, 'Sold privately', $actor);

        $this->get(route('properties.show', $property->slug))->assertNotFound();
        $this->get(route('properties.index'))->assertOk()->assertDontSee('Hidden home');
    }
}
