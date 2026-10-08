<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_view_unfiltered_audit_log(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        AuditLog::query()->create(['actor_id' => $owner->id, 'action' => 'faq.created', 'subject_type' => 'Faq', 'subject_id' => 1, 'ip' => '127.0.0.1']);

        $this->actingAs($owner)->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('faq.created');
    }

    public function test_audit_log_can_be_filtered_by_actor(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $other = $this->staff(Role::SALES_USER);

        AuditLog::query()->create(['actor_id' => $owner->id, 'action' => 'lead.assigned', 'subject_type' => 'Lead', 'subject_id' => 1, 'ip' => '127.0.0.1']);
        AuditLog::query()->create(['actor_id' => $other->id, 'action' => 'auth.logout', 'subject_type' => 'User', 'subject_id' => $other->id, 'ip' => '127.0.0.1']);

        $this->actingAs($owner)->get(route('admin.audit.index', ['actor_id' => $owner->id]))
            ->assertOk()
            ->assertSee('lead.assigned')
            ->assertDontSee('auth.logout');
    }

    public function test_audit_log_can_be_filtered_by_action_prefix(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        AuditLog::query()->create(['actor_id' => $owner->id, 'action' => 'lead.assigned', 'subject_type' => 'Lead', 'subject_id' => 1, 'ip' => '127.0.0.1']);
        AuditLog::query()->create(['actor_id' => $owner->id, 'action' => 'faq.created', 'subject_type' => 'Faq', 'subject_id' => 2, 'ip' => '127.0.0.1']);

        $this->actingAs($owner)->get(route('admin.audit.index', ['action' => 'lead']))
            ->assertOk()
            ->assertSee('lead.assigned')
            ->assertDontSee('faq.created');
    }

    public function test_audit_log_action_filter_rejects_invalid_characters(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->get(route('admin.audit.index', ['action' => 'drop table; --']))
            ->assertSessionHasErrors('action');
    }

    public function test_non_owner_cannot_view_audit_log(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);

        $this->actingAs($editor)->get(route('admin.audit.index'))->assertForbidden();
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }
}
