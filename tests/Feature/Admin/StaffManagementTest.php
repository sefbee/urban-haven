<?php

namespace Tests\Feature\Admin;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_list_and_create_a_staff_account(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->get(route('admin.staff.index'))->assertOk()->assertSee($owner->name);

        $this->actingAs($owner)->get(route('admin.staff.create'))->assertOk()->assertSee('Add a staff account');

        $this->actingAs($owner)->post(route('admin.staff.store'), [
            'name' => 'Nadia Islam',
            'email' => 'nadia@example.com',
            'phone' => '01711000001',
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
            'role' => Role::SALES_USER,
        ])->assertRedirect(route('admin.staff.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'nadia@example.com', 'name' => 'Nadia Islam', 'is_active' => true]);
    }

    public function test_owner_can_edit_and_update_a_staff_account(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $member = $this->staff(Role::SALES_USER);

        $this->actingAs($owner)->get(route('admin.staff.edit', $member))
            ->assertOk()
            ->assertSee($member->name);

        $this->actingAs($owner)->put(route('admin.staff.update', $member), [
            'name' => 'Updated Name',
            'email' => $member->email,
            'phone' => $member->phone ?? '01711000002',
            'role' => Role::SALES_USER,
        ])->assertRedirect(route('admin.staff.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame('Updated Name', $member->fresh()->name);
    }

    public function test_owner_can_deactivate_a_staff_member(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $member = $this->staff(Role::SALES_USER);

        $this->actingAs($owner)
            ->post(route('admin.staff.deactivate', $member))
            ->assertRedirect(route('admin.staff.index'));

        $this->assertFalse($member->fresh()->is_active);
    }

    public function test_deactivating_staff_reassigns_their_open_leads(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $member = $this->staff(Role::SALES_USER);
        $other = $this->staff(Role::SALES_USER);

        $lead = $this->lead(['assigned_to' => $member->id]);

        $this->actingAs($owner)->post(route('admin.staff.deactivate', $member), [
            'reassign_to' => $other->id,
        ])->assertRedirect();

        $this->assertSame($other->id, $lead->fresh()->assigned_to);
    }

    public function test_non_owner_cannot_access_staff_management(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);
        $member = $this->staff(Role::SALES_USER);

        $this->actingAs($editor)->get(route('admin.staff.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.staff.create'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.staff.store'), [])->assertForbidden();
        $this->actingAs($editor)->get(route('admin.staff.edit', $member))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.staff.update', $member), [])->assertForbidden();
        $this->actingAs($editor)->post(route('admin.staff.deactivate', $member))->assertForbidden();
    }

    public function test_owner_can_reset_a_staff_members_mfa(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $member = $this->staff(Role::SALES_USER);
        $member->forceFill(['mfa_secret' => 'JBSWY3DPEHPK3PXP', 'mfa_enabled_at' => now()])->save();

        $this->assertTrue($member->hasMfaEnabled());

        $this->actingAs($owner)
            ->post(route('admin.staff.mfa.reset', $member))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertFalse($member->fresh()->hasMfaEnabled());
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $existing = $this->staff(Role::SALES_USER);

        $this->actingAs($owner)->post(route('admin.staff.store'), [
            'name' => 'Duplicate',
            'email' => $existing->email,
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
            'role' => Role::SALES_USER,
        ])->assertSessionHasErrors('email');
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function lead(array $attributes = []): Lead
    {
        $phone = PhoneNumber::normalize($attributes['phone'] ?? '01711111111');

        return Lead::query()->create(array_merge([
            'type' => 'general_contact',
            'name' => 'Test Lead',
            'phone_hash' => Lead::hashPhone($phone),
            'status' => 'new',
            'priority' => 'medium',
        ], $attributes, ['phone' => $phone]));
    }
}
