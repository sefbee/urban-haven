<?php

namespace Tests\Feature\Policies;

use App\Models\Lead;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OwnerAdminAuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_admin_is_authorized_for_staff_and_inventory_actions(): void
    {
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);
        $staff = User::factory()->create();

        $this->assertTrue($owner->can('create', User::class));
        $this->assertTrue($owner->can('update', $staff));
        $this->assertTrue($owner->can('deactivate', $staff));
        $this->assertTrue($owner->can('create', Property::class));
        $this->assertTrue($owner->can('publish', new Property));
        $this->assertTrue($owner->can('export', Lead::class));
    }

    public function test_sales_user_is_denied_staff_and_inventory_actions_without_permissions(): void
    {
        $sales = User::factory()->create();
        $this->assignRole($sales, Role::SALES_USER);
        $staff = User::factory()->create();

        $this->assertFalse($sales->can('create', User::class));
        $this->assertFalse($sales->can('deactivate', $staff));
        $this->assertFalse($sales->can('create', Property::class));
        $this->assertFalse($sales->can('publish', new Property));
        $this->assertFalse($sales->can('export', Lead::class));
    }
}
