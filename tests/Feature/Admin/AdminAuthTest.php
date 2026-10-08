<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_credentials_log_in_and_redirect_to_dashboard(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);
        $this->assignRole($user, Role::OWNER_ADMIN);

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'secret123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_returns_error_and_does_not_log_in(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct')]);
        $this->assignRole($user, Role::SALES_USER);

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email')
            ->assertRedirect();

        $this->assertGuest();
    }

    public function test_inactive_account_is_rejected_after_authentication(): void
    {
        $user = User::factory()->inactive()->create(['password' => bcrypt('secret')]);
        $this->assignRole($user, Role::SALES_USER);

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'secret'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->post(route('admin.login.store'), [])
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_failed_login_is_recorded_in_audit_log(): void
    {
        $user = User::factory()->create(['password' => bcrypt('right')]);
        $this->assignRole($user, Role::SALES_USER);

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'wrong']);

        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.login_failed']);
    }

    public function test_logout_clears_session_and_records_audit_entry(): void
    {
        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        $this->actingAs($user)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.logout', 'actor_id' => $user->id]);
    }

    public function test_rate_limiter_blocks_after_five_failed_attempts(): void
    {
        $user = User::factory()->create(['password' => bcrypt('right')]);
        $this->assignRole($user, Role::SALES_USER);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'bad']);
        }

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'right'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
