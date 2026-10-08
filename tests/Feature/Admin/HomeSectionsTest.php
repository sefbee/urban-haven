<?php

namespace Tests\Feature\Admin;

use App\Models\CmsBlock;
use App\Models\Role;
use App\Models\User;
use App\Support\HomeSections;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HomeSectionsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_index_lists_every_section_with_built_in_defaults(): void
    {
        $this->actingAs($this->staff(Role::OWNER_ADMIN))
            ->get(route('admin.home-sections.index'))
            ->assertOk()
            ->assertSeeInOrder(['Hero', 'Trending properties', 'Featured properties', 'Frequently asked questions']);

        $this->assertSame(count(HomeSections::keys()), CmsBlock::query()->whereIn('key', HomeSections::blockKeys())->count());
        $this->assertFalse(CmsBlock::query()->where('key', 'about')->value('is_visible'));
    }

    public function test_existing_hero_content_is_kept_when_sections_are_created(): void
    {
        CmsBlock::query()->create(['key' => 'hero', 'label' => 'Hero', 'content' => ['title' => 'Live title'], 'is_visible' => true, 'updated_at' => now()]);

        $this->actingAs($this->staff(Role::OWNER_ADMIN))
            ->get(route('admin.home-sections.edit', 'hero'))
            ->assertOk()
            ->assertSee('Live title');

        $this->assertSame('Live title', CmsBlock::query()->where('key', 'hero')->value('content')['title']);
    }

    public function test_tied_legacy_sort_orders_fall_back_to_the_homepage_order(): void
    {
        CmsBlock::query()->create(['key' => 'hero', 'label' => 'Hero', 'content' => ['title' => 'A'], 'is_visible' => true, 'sort_order' => 0, 'updated_at' => now()]);
        CmsBlock::query()->create(['key' => 'about', 'label' => 'About', 'content' => ['title' => 'B'], 'is_visible' => true, 'sort_order' => 0, 'updated_at' => now()]);

        $this->assertSame(HomeSections::keys(), HomeSections::blocks()->keys()->all());
    }

    public function test_publisher_updates_section_content_and_items(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->put(route('admin.home-sections.update', 'why'), ['content' => [
            'title' => 'Why choose us',
            'body' => 'We know Dhaka.',
            'link_label' => '',
            'link_url' => '/about',
            'items' => [
                ['icon' => 'shield', 'title' => 'Verified listings', 'text' => 'Every listing checked.'],
                ['icon' => 'check-circle', 'title' => '', 'text' => ''],
            ],
        ]])->assertRedirect(route('admin.home-sections.edit', 'why'));

        $content = CmsBlock::query()->where('key', 'about')->value('content');
        $this->assertSame('Why choose us', $content['title']);
        $this->assertEquals([['icon' => 'shield', 'title' => 'Verified listings', 'text' => 'Every listing checked.']], $content['items']);
        $this->assertSame('More about us', HomeSections::resolve('why', $content)['link_label'], 'Empty fields fall back to defaults.');
    }

    public function test_section_validation_rejects_bad_values(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->put(route('admin.home-sections.update', 'trending'), ['content' => ['title' => '', 'limit' => 99]])
            ->assertSessionHasErrors(['content.title', 'content.limit']);

        $this->actingAs($owner)->put(route('admin.home-sections.update', 'hero'), ['content' => ['title' => 'Hi', 'cta_url' => 'https://evil.example']])
            ->assertSessionHasErrors('content.cta_url');

        $this->actingAs($owner)->put(route('admin.home-sections.update', 'why'), ['content' => ['title' => 'Hi', 'items' => [['icon' => 'not-an-icon', 'title' => 'A']]]])
            ->assertSessionHasErrors('content.items.0.icon');
    }

    public function test_editor_changes_are_held_as_drafts_and_cannot_reorder_or_hide(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);
        HomeSections::blocks();

        $this->actingAs($editor)->put(route('admin.home-sections.update', 'trending'), ['content' => ['title' => 'Hot right now', 'limit' => 6]])->assertRedirect();

        $block = CmsBlock::query()->where('key', 'home_trending')->first();
        $this->assertSame('Trending Properties', $block->content['title']);
        $this->assertSame('Hot right now', $block->draft_content['title']);

        $this->actingAs($editor)->post(route('admin.home-sections.visibility', 'trending'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.home-sections.move', 'trending'), ['direction' => 'up'])->assertForbidden();
        $this->actingAs($editor)->post(route('admin.home-sections.publish', 'trending'))->assertForbidden();

        $this->actingAs($this->staff(Role::OWNER_ADMIN))->post(route('admin.home-sections.publish', 'trending'))->assertRedirect();
        $this->assertSame('Hot right now', $block->fresh()->content['title']);
    }

    public function test_publisher_hides_and_reorders_sections(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.home-sections.visibility', 'faq'))->assertRedirect();
        $this->assertFalse(CmsBlock::query()->where('key', 'home_faq')->value('is_visible'));
        $this->assertArrayNotHasKey('faq', HomeSections::visible());

        $this->actingAs($owner)->post(route('admin.home-sections.move', 'featured'), ['direction' => 'up'])->assertRedirect();
        $this->assertSame(['hero', 'featured', 'trending'], array_slice(HomeSections::blocks()->keys()->all(), 0, 3));

        $this->actingAs($owner)->post(route('admin.home-sections.move', 'hero'), ['direction' => 'up'])->assertRedirect();
        $this->assertSame('hero', HomeSections::blocks()->keys()->first());
    }

    public function test_unknown_sections_return_not_found(): void
    {
        $this->actingAs($this->staff(Role::OWNER_ADMIN))->get('/admin/home-sections/nope')->assertNotFound();
    }

    public function test_home_section_blocks_are_not_repeated_on_the_pages_screen(): void
    {
        HomeSections::blocks();
        CmsBlock::query()->create(['key' => 'contact_details', 'label' => 'Contact details', 'content' => ['title' => 'Visit us'], 'is_visible' => true, 'updated_at' => now()]);

        $this->actingAs($this->staff(Role::OWNER_ADMIN))
            ->get(route('admin.cms.index'))
            ->assertOk()
            ->assertSee('Contact details')
            ->assertDontSee('Homepage: Trending properties');
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }
}
