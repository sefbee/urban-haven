<?php

namespace Tests\Feature\Admin;

use App\Models\MenuItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MenusTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_publisher_builds_a_dropdown_with_sub_links(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->post(route('admin.menus.store'), ['location' => 'header', 'label' => 'Buy', 'url' => '/properties?listing_type=sale'])->assertSessionHasNoErrors();
        $parent = MenuItem::query()->where('label', 'Buy')->firstOrFail();

        $this->actingAs($owner)->post(route('admin.menus.store'), ['location' => 'header', 'parent_id' => $parent->id, 'label' => 'Apartments', 'url' => '/properties?type=apartment', 'opens_new_tab' => '1'])->assertSessionHasNoErrors();

        $this->assertSame(['Buy'], MenuItem::forLocation('header')->pluck('label')->all(), 'The public header keeps showing top-level links only.');

        $tree = MenuItem::treeFor('header');
        $this->assertSame(['Apartments'], $tree->first()->children->pluck('label')->all());
        $this->assertTrue($tree->first()->children->first()->opens_new_tab);

        $this->actingAs($owner)->get(route('admin.menus.index'))->assertOk()->assertSeeInOrder(['Buy', 'Apartments']);
    }

    public function test_sub_links_must_sit_under_a_top_level_link_of_the_same_menu(): void
    {
        $owner = $this->owner();
        $footer = MenuItem::query()->create(['location' => 'footer', 'label' => 'Guides', 'url' => '/articles']);
        $header = MenuItem::query()->create(['location' => 'header', 'label' => 'Buy', 'url' => '/properties']);
        $child = MenuItem::query()->create(['location' => 'header', 'parent_id' => $header->id, 'label' => 'Flats', 'url' => '/properties']);

        $this->actingAs($owner)->post(route('admin.menus.store'), ['location' => 'header', 'parent_id' => $footer->id, 'label' => 'X', 'url' => '/x'])->assertSessionHasErrors('parent_id');
        $this->actingAs($owner)->post(route('admin.menus.store'), ['location' => 'header', 'parent_id' => $child->id, 'label' => 'X', 'url' => '/x'])->assertSessionHasErrors('parent_id');

        $other = MenuItem::query()->create(['location' => 'header', 'label' => 'Rent', 'url' => '/rent']);
        $this->actingAs($owner)->put(route('admin.menus.update', $header), ['location' => 'header', 'parent_id' => $other->id, 'label' => 'Buy', 'url' => '/properties'])->assertSessionHasErrors('parent_id');
    }

    public function test_links_move_and_toggle_visibility(): void
    {
        $owner = $this->owner();
        $first = MenuItem::query()->create(['location' => 'footer', 'label' => 'First', 'url' => '/a', 'sort_order' => 10]);
        $second = MenuItem::query()->create(['location' => 'footer', 'label' => 'Second', 'url' => '/b', 'sort_order' => 20]);

        $this->actingAs($owner)->post(route('admin.menus.move', $second), ['direction' => 'up'])->assertRedirect();
        $this->assertSame(['Second', 'First'], MenuItem::forLocation('footer')->pluck('label')->all());

        $this->actingAs($owner)->post(route('admin.menus.visibility', $first))->assertRedirect();
        $this->assertSame(['Second'], MenuItem::forLocation('footer')->pluck('label')->all());
    }

    public function test_removing_a_parent_removes_its_sub_links(): void
    {
        $parent = MenuItem::query()->create(['location' => 'header', 'label' => 'Buy', 'url' => '/properties']);
        MenuItem::query()->create(['location' => 'header', 'parent_id' => $parent->id, 'label' => 'Flats', 'url' => '/properties']);

        $this->actingAs($this->owner())->delete(route('admin.menus.destroy', $parent))->assertRedirect();

        $this->assertSame(0, MenuItem::query()->count());
    }

    public function test_editors_cannot_change_menus(): void
    {
        $editor = User::factory()->create();
        $this->assignRole($editor, Role::CONTENT_EDITOR);
        $item = MenuItem::query()->create(['location' => 'footer', 'label' => 'First', 'url' => '/a']);

        $this->actingAs($editor)->post(route('admin.menus.visibility', $item))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.menus.move', $item), ['direction' => 'up'])->assertForbidden();
    }

    private function owner(): User
    {
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);

        return $owner;
    }
}
