<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_unauthenticated_admin_request_redirects_to_staff_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_can_view_staff_login(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Staff sign in');
    }

    public function test_inactive_staff_is_logged_out_and_redirected_to_login(): void
    {
        $user = User::factory()->inactive()->create();
        $this->assignRole($user, Role::SALES_USER);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['email']);
    }

    public function test_owner_admin_can_view_dashboard(): void
    {
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin dashboard');
    }

    public function test_active_staff_can_view_dashboard(): void
    {
        $user = User::factory()->create();
        $this->assignRole($user, Role::SALES_USER);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin dashboard');
    }
}
