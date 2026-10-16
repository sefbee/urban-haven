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
            ->get(route('admin.page-sections.index', ['page' => 'home']))
            ->assertOk()
            ->assertSeeInOrder(['Hero', 'Housing & apartment listings', 'Featured developments', 'Featured properties']);

        $this->assertSame(count(HomeSections::keys()), CmsBlock::query()->whereIn('key', HomeSections::blockKeys())->count());
        $this->assertFalse(CmsBlock::query()->where('key', 'about')->value('is_visible'));
        $this->assertCount(4, CmsBlock::query()->where('key', 'about')->value('content')['items']);
    }

    public function test_existing_hero_content_is_kept_when_sections_are_created(): void
    {
        CmsBlock::query()->create(['key' => 'hero', 'label' => 'Hero', 'content' => ['title' => 'Live title'], 'is_visible' => true, 'updated_at' => now()]);

        $this->actingAs($this->staff(Role::OWNER_ADMIN))
            ->get(route('admin.page-sections.edit', ['page' => 'home', 'section' => 'hero']))
            ->assertOk()
            ->assertDontSee('Small label above the headline')
            ->assertDontSee('Headline')
            ->assertDontSee('Supporting line')
            ->assertDontSee('Live title')
            ->assertSee('Button text')
            ->assertSee('Button link')
            ->assertSee('Seconds per background photo')
            ->assertSee('Background photos')
            ->assertDontSee('role="tab"', false);

        $this->assertSame('Live title', CmsBlock::query()->where('key', 'hero')->value('content')['title']);
    }

    public function test_saving_hero_controls_preserves_saved_copy_while_public_hero_omits_it(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $copy = [
            'eyebrow' => 'Urban Haven Properties Ltd.',
            'title' => 'Considered places to live in Dhaka.',
            'body' => 'Search houses, apartments, and land across Dhaka at prices you can verify with our desk.',
        ];
        $block = CmsBlock::query()->create([
            'key' => 'hero',
            'label' => 'Hero',
            'content' => [...$copy, 'cta_label' => 'Explore homes', 'cta_url' => '/properties', 'slide_seconds' => 9],
            'is_visible' => true,
            'updated_at' => now(),
        ]);

        $this->actingAs($owner)->put(route('admin.page-sections.update', ['page' => 'home', 'section' => 'hero']), ['content' => [
            'eyebrow' => 'Changed label',
            'title' => 'Changed headline',
            'body' => 'Changed supporting line',
            'cta_label' => 'Browse homes',
            'cta_url' => '/properties',
            'slide_seconds' => 12,
        ]])->assertRedirect();

        $this->assertSame($copy, array_intersect_key($block->fresh()->content, $copy));
        $this->assertSame('Browse homes', $block->fresh()->content['cta_label']);
        $this->assertSame('/properties', $block->fresh()->content['cta_url']);
        $this->assertSame(12, $block->fresh()->content['slide_seconds']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee($copy['eyebrow'])
            ->assertDontSee($copy['title'])
            ->assertDontSee('uh-cover-copy', false)
            ->assertDontSee('uh-cover-description', false)
            ->assertDontSee('id="hero-title"', false)
            ->assertSee('id="find"', false)
            ->assertSee('Browse homes');
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

        $this->actingAs($owner)->put(route('admin.page-sections.update', ['page' => 'home', 'section' => 'why']), ['content' => [
            'title' => 'Why choose us',
            'body' => 'We know Dhaka.',
            'link_label' => '',
            'link_url' => '/about',
            'items' => [
                ['icon' => 'shield', 'title' => 'Verified listings', 'text' => 'Every listing checked.'],
                ['icon' => 'check-circle', 'title' => 'Personal service', 'text' => 'Direct guidance.'],
            ],
        ]])->assertRedirect(route('admin.page-sections.edit', ['page' => 'home', 'section' => 'why']));

        $content = CmsBlock::query()->where('key', 'about')->value('content');
        $this->assertSame('Why choose us', $content['title']);
        $this->assertEquals([
            ['icon' => 'shield', 'title' => 'Verified listings', 'text' => 'Every listing checked.'],
            ['icon' => 'check-circle', 'title' => 'Personal service', 'text' => 'Direct guidance.'],
        ], $content['items']);
        $this->assertNull(HomeSections::resolve('why', $content)['link_label']);
    }

    public function test_why_editor_and_homepage_use_saved_content_and_reason_order(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        HomeSections::blocks();
        $block = CmsBlock::query()->where('key', 'about')->firstOrFail();
        $block->forceFill(['is_visible' => true])->save();

        $this->actingAs($owner)->put(route('admin.page-sections.update', ['page' => 'home', 'section' => 'why']), ['content' => [
            'eyebrow' => 'Our Advantage',
            'title' => 'A saved heading',
            'body' => 'A saved statement.',
            'link_label' => 'Meet the team',
            'link_url' => '/about',
            'items' => [
                ['icon' => 'users', 'title' => 'Second reason', 'text' => 'Second description.'],
                ['icon' => 'home', 'title' => 'First reason', 'text' => 'First description.'],
                ['icon' => 'document', 'title' => 'Added reason', 'text' => 'Added description.'],
            ],
        ]])->assertRedirect(route('admin.page-sections.edit', ['page' => 'home', 'section' => 'why']));

        $this->get(route('admin.page-sections.edit', ['page' => 'home', 'section' => 'why']))
            ->assertOk()
            ->assertSeeInOrder(['Second reason', 'First reason', 'Added reason']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('A saved heading')
            ->assertSee('A saved statement.')
            ->assertSee('Meet the team')
            ->assertSee('href="/about"', false)
            ->assertSeeInOrder(['Second reason', 'First reason', 'Added reason']);

        $this->assertSame('Added reason', $block->fresh()->content['items'][2]['title']);
    }

    public function test_why_editor_merges_partial_drafts_over_published_reasons(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        HomeSections::blocks();
        $block = CmsBlock::query()->where('key', 'about')->firstOrFail();
        $block->forceFill(['draft_content' => ['title' => 'Draft heading']])->save();

        $this->actingAs($owner)->get(route('admin.page-sections.edit', ['page' => 'home', 'section' => 'why']))
            ->assertOk()
            ->assertSeeInOrder(['Draft heading', 'Verified Listings', 'Expert Team', 'Clear Information', 'Personal Support']);

        $this->assertSame('Draft heading', $block->fresh()->draft_content['title']);
        $this->assertCount(4, $block->fresh()->content['items']);
    }

    public function test_why_reasons_can_be_removed_without_restoring_defaults(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        HomeSections::blocks();
        CmsBlock::query()->where('key', 'about')->firstOrFail()->forceFill(['is_visible' => true])->save();

        $this->actingAs($owner)->put(route('admin.page-sections.update', ['page' => 'home', 'section' => 'why']), ['content' => [
            'eyebrow' => 'Our Advantage',
            'title' => 'Why Urban Haven?',
            'body' => 'A statement.',
            'link_label' => 'More about us',
            'link_url' => '/about',
            'items' => '',
        ]])->assertRedirect();

        $content = CmsBlock::query()->where('key', 'about')->value('content');
        $this->assertSame([], $content['items']);
        $this->assertSame([], HomeSections::resolve('why', $content)['items']);

        $this->get(route('admin.page-sections.edit', ['page' => 'home', 'section' => 'why']))
            ->assertOk()
            ->assertSee('No reasons yet. Add one to display it on the page.');

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Verified Listings');
    }

    public function test_section_validation_rejects_bad_values(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->put(route('admin.page-sections.update', ['page' => 'home', 'section' => 'trending']), ['content' => ['title' => '', 'limit' => 99]])
            ->assertSessionHasErrors(['content.title', 'content.limit']);

        $this->actingAs($owner)->put(route('admin.page-sections.update', ['page' => 'home', 'section' => 'hero']), ['content' => ['title' => 'Hi', 'cta_url' => 'https://evil.example']])
            ->assertSessionHasErrors('content.cta_url');

        $this->actingAs($owner)->put(route('admin.page-sections.update', ['page' => 'home', 'section' => 'why']), ['content' => ['title' => 'Hi', 'items' => [['icon' => 'not-an-icon', 'title' => 'A']]]])
            ->assertSessionHasErrors(['content.items.0.icon', 'content.items.0.text']);

        $this->actingAs($owner)->put(route('admin.page-sections.update', ['page' => 'home', 'section' => 'why']), ['content' => ['items' => array_fill(0, 7, ['icon' => 'home', 'title' => 'Reason', 'text' => 'Description'])]])
            ->assertSessionHasErrors('content.items');
    }

    public function test_editor_changes_are_held_as_drafts_and_cannot_reorder_or_hide(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);
        HomeSections::blocks();

        $this->actingAs($editor)->put(route('admin.page-sections.update', ['page' => 'home', 'section' => 'trending']), ['content' => ['title' => 'Hot right now', 'limit' => 6]])->assertRedirect();

        $block = CmsBlock::query()->where('key', 'home_trending')->first();
        $this->assertSame('Trending Properties', $block->content['title']);
        $this->assertSame('Hot right now', $block->draft_content['title']);

        $this->actingAs($editor)->post(route('admin.page-sections.visibility', ['page' => 'home', 'section' => 'trending']))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.page-sections.move', ['page' => 'home', 'section' => 'trending']), ['direction' => 'up'])->assertForbidden();
        $this->actingAs($editor)->post(route('admin.page-sections.publish', ['page' => 'home', 'section' => 'trending']))->assertForbidden();

        $this->actingAs($this->staff(Role::OWNER_ADMIN))->post(route('admin.page-sections.publish', ['page' => 'home', 'section' => 'trending']))->assertRedirect();
        $this->assertSame('Hot right now', $block->fresh()->content['title']);
    }

    public function test_publisher_hides_and_reorders_sections(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.page-sections.visibility', ['page' => 'home', 'section' => 'faq']))->assertRedirect();
        $this->assertFalse(CmsBlock::query()->where('key', 'home_faq')->value('is_visible'));
        $this->assertArrayNotHasKey('faq', HomeSections::visible());

        $this->actingAs($owner)->post(route('admin.page-sections.move', ['page' => 'home', 'section' => 'featured']), ['direction' => 'up'])->assertRedirect();
        $this->assertSame(['hero', 'housing', 'featured', 'projects'], array_slice(HomeSections::blocks()->keys()->all(), 0, 4));

        $this->actingAs($owner)->post(route('admin.page-sections.move', ['page' => 'home', 'section' => 'hero']), ['direction' => 'up'])->assertRedirect();
        $this->assertSame('hero', HomeSections::blocks()->keys()->first());
    }

    public function test_unknown_sections_return_not_found(): void
    {
        $this->actingAs($this->staff(Role::OWNER_ADMIN))->get('/admin/page-sections/home/nope')->assertNotFound();
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
