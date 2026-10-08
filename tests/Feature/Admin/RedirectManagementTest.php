<?php

namespace Tests\Feature\Admin;

use App\Models\Redirect;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RedirectManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_create_a_301_redirect(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.redirects.store'), [
            'from_path' => '/old-page',
            'to_path' => '/new-page',
            'http_code' => 301,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('redirects', ['from_path' => '/old-page', 'to_path' => '/new-page', 'http_code' => 301]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'redirect.saved']);
    }

    public function test_redirect_from_path_must_start_with_a_slash(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.redirects.store'), [
            'from_path' => 'no-leading-slash',
            'to_path' => '/target',
            'http_code' => 301,
        ])->assertSessionHasErrors('from_path');
    }

    public function test_redirect_to_path_can_be_an_absolute_https_url(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.redirects.store'), [
            'from_path' => '/external',
            'to_path' => 'https://external.example.com/page',
            'http_code' => 302,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('redirects', ['from_path' => '/external', 'http_code' => 302]);
    }

    public function test_owner_can_toggle_a_redirect(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $redirect = Redirect::query()->create(['from_path' => '/toggle-me', 'to_path' => '/dest', 'http_code' => 301, 'is_active' => true]);

        $this->actingAs($owner)->post(route('admin.redirects.toggle', $redirect))
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertFalse($redirect->fresh()->is_active);

        $this->actingAs($owner)->post(route('admin.redirects.toggle', $redirect))->assertRedirect();
        $this->assertTrue($redirect->fresh()->is_active);
    }

    public function test_owner_can_delete_a_redirect(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $redirect = Redirect::query()->create(['from_path' => '/del', 'to_path' => '/here', 'http_code' => 301, 'is_active' => true]);

        $this->actingAs($owner)->delete(route('admin.redirects.destroy', $redirect))->assertRedirect();

        $this->assertModelMissing($redirect);
        $this->assertDatabaseHas('audit_logs', ['action' => 'redirect.deleted']);
    }

    public function test_redirect_index_can_be_searched_by_from_path(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        Redirect::query()->create(['from_path' => '/find-me', 'to_path' => '/a', 'http_code' => 301, 'is_active' => true]);
        Redirect::query()->create(['from_path' => '/other', 'to_path' => '/b', 'http_code' => 301, 'is_active' => true]);

        $this->actingAs($owner)->get(route('admin.redirects.index', ['q' => 'find']))
            ->assertOk()
            ->assertSee('/find-me')
            ->assertDontSee('/other');
    }

    public function test_non_owner_cannot_manage_redirects(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);
        $redirect = Redirect::query()->create(['from_path' => '/r', 'to_path' => '/t', 'http_code' => 301, 'is_active' => true]);

        $this->actingAs($editor)->get(route('admin.redirects.index'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.redirects.store'), [])->assertForbidden();
        $this->actingAs($editor)->post(route('admin.redirects.toggle', $redirect))->assertForbidden();
        $this->actingAs($editor)->delete(route('admin.redirects.destroy', $redirect))->assertForbidden();
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }
}
