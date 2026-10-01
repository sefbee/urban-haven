<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_credentials_start_a_staff_session(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $this->assignRole($user, Role::SALES_USER);

        $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_record_an_audit_entry(): void
    {
        $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => 'nobody@example.com',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.login_failed']);
    }

    public function test_sixth_attempt_on_one_account_is_locked_even_with_the_right_password(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $this->assignRole($user, Role::SALES_USER);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_more_than_ten_attempts_from_one_address_return_429(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('admin.login.store'), ['email' => "user{$i}@example.com", 'password' => 'x']);
        }

        $this->post(route('admin.login.store'), ['email' => 'another@example.com', 'password' => 'x'])
            ->assertStatus(429);
    }

    public function test_sales_user_is_forbidden_from_staff_index(): void
    {
        $user = User::factory()->create();
        $this->assignRole($user, Role::SALES_USER);

        $this->actingAs($user)
            ->get(route('admin.staff.index'))
            ->assertForbidden();
    }
}
