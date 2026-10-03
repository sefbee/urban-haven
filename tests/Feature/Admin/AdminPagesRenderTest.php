<?php

namespace Tests\Feature\Admin;

use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Models\Faq;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminPagesRenderTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_every_admin_screen_renders_for_the_owner(): void
    {
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);
        $property = $this->makePublishableProperty();
        $page = CmsPage::query()->create(['title' => 'About Urban Haven', 'body' => '<p>About</p>']);
        $post = Post::factory()->create();
        CmsBlock::query()->create(['key' => 'hero', 'label' => 'Homepage hero', 'content' => ['title' => 'Find a property'], 'is_visible' => true, 'updated_at' => now()]);
        Faq::query()->create(['question' => 'Do you charge a fee?', 'answer' => 'No.', 'group' => 'Buying', 'is_visible' => true]);
        MenuItem::query()->create(['location' => 'footer', 'label' => 'Guides', 'url' => '/articles', 'is_visible' => true]);
        Redirect::query()->create(['from_path' => '/old', 'to_path' => '/new', 'http_code' => 301, 'is_active' => true]);

        $urls = [
            route('admin.dashboard'),
            route('admin.properties.index'),
            route('admin.properties.create'),
            route('admin.properties.edit', $property),
            route('admin.projects.index'),
            route('admin.projects.create'),
            route('admin.review.index'),
            route('admin.leads.index'),
            route('admin.follow-ups.index'),
            route('admin.visits.index'),
            route('admin.cms.index'),
            route('admin.cms.create'),
            route('admin.cms.edit', $page),
            route('admin.cms.preview', $page),
            route('admin.posts.index'),
            route('admin.posts.create'),
            route('admin.posts.edit', $post),
            route('admin.faqs.index'),
            route('admin.menus.index'),
            route('admin.redirects.index'),
            route('admin.audit.index'),
            route('admin.settings.index'),
            route('admin.listing-display'),
            route('admin.areas.index'),
            route('admin.property-types.index'),
            route('admin.amenities.index'),
            route('admin.staff.index'),
            route('admin.notifications.index'),
        ];

        foreach ($urls as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
        }
    }

    public function test_editor_navigation_hides_owner_only_areas(): void
    {
        $editor = User::factory()->create();
        $this->assignRole($editor, Role::CONTENT_EDITOR);

        $this->actingAs($editor)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Properties')
            ->assertSee('Areas')
            ->assertDontSee('Audit log')
            ->assertDontSee('Review queue')
            ->assertDontSee('Listing display')
            ->assertDontSee(route('admin.settings.index'), false);

        $this->actingAs($editor)->get(route('admin.areas.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.property-types.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.amenities.index'))->assertOk();

        foreach (['admin.settings.index', 'admin.listing-display', 'admin.audit.index', 'admin.redirects.index', 'admin.menus.index', 'admin.review.index', 'admin.staff.index'] as $name) {
            $this->actingAs($editor)->get(route($name))->assertForbidden();
        }
    }
}
