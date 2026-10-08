<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PublicationState;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_creates_an_article_as_a_draft(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.posts.store'), [
            'title' => 'Buying guide for first-timers',
            'body' => '<p>Here is what you need to know.</p>',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $post = Post::query()->sole();
        $this->assertSame('Buying guide for first-timers', $post->title);
        $this->assertSame(PublicationState::DRAFT, $post->editorialStatus());
    }

    public function test_owner_publishes_and_unpublishes_an_article(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $category = PostCategory::query()->create(['name' => 'Guides']);
        $post = Post::factory()->create(['post_category_id' => $category->id]);

        $this->actingAs($owner)->post(route('admin.posts.publish', $post))
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(PublicationState::PUBLISHED, $post->fresh()->editorialStatus());
        $this->get(route('articles.show', $post->fresh()->slug))->assertOk();

        $this->actingAs($owner)->post(route('admin.posts.unpublish', $post))
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertNotSame(PublicationState::PUBLISHED, $post->fresh()->editorialStatus());
        $this->get(route('articles.show', $post->fresh()->slug))->assertNotFound();
    }

    public function test_editor_changes_are_held_as_drafts_until_owner_publishes(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $editor = $this->staff(Role::CONTENT_EDITOR);
        $category = PostCategory::query()->create(['name' => 'Guides']);
        $post = Post::factory()->create(['post_category_id' => $category->id, 'body' => '<p>Original</p>']);

        $this->actingAs($owner)->post(route('admin.posts.publish', $post))->assertRedirect();

        $this->actingAs($editor)->put(route('admin.posts.update', $post), [
            'title' => $post->title,
            'body' => '<p>Edited body</p>',
        ])->assertRedirect();

        $this->get(route('articles.show', $post->fresh()->slug))
            ->assertOk()
            ->assertSee('Original')
            ->assertDontSee('Edited body');

        $this->actingAs($owner)->post(route('admin.posts.publish', $post->fresh()))->assertRedirect();

        $this->get(route('articles.show', $post->fresh()->slug))
            ->assertOk()
            ->assertSee('Edited body');
    }

    public function test_editor_cannot_publish_or_unpublish_articles(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);
        $category = PostCategory::query()->create(['name' => 'News']);
        $post = Post::factory()->create(['post_category_id' => $category->id]);

        $this->actingAs($editor)->post(route('admin.posts.publish', $post))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.posts.unpublish', $post))->assertForbidden();
    }

    public function test_owner_can_delete_a_draft_article(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $category = PostCategory::query()->create(['name' => 'Guides']);
        $post = Post::factory()->create(['post_category_id' => $category->id]);

        $this->actingAs($owner)->delete(route('admin.posts.destroy', $post))
            ->assertRedirect(route('admin.posts.index'));

        $this->assertModelMissing($post);
    }

    public function test_article_preview_renders_for_an_editor(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);
        $category = PostCategory::query()->create(['name' => 'Guides']);
        $post = Post::factory()->create(['post_category_id' => $category->id, 'body' => '<p>Preview content</p>']);

        $this->actingAs($editor)->get(route('admin.posts.preview', $post))
            ->assertOk()
            ->assertSee('Preview content')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_owner_can_add_a_post_category_via_json(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->postJson(route('admin.post-categories.store'), ['name' => 'Market Reports'])
            ->assertCreated()
            ->assertJsonPath('label', 'Market Reports');

        $this->assertDatabaseHas('post_categories', ['name' => 'Market Reports']);
    }

    public function test_duplicate_post_category_name_is_rejected(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        PostCategory::query()->create(['name' => 'Guides']);

        $this->actingAs($owner)->postJson(route('admin.post-categories.store'), ['name' => 'Guides'])
            ->assertUnprocessable();
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }
}
