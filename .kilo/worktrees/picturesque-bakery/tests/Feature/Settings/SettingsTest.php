<?php

namespace Tests\Feature\Settings;

use App\Models\LocationArea;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_setting_set_then_get_returns_cast_type(): void
    {
        Setting::set('lead_window', 30, 'leads', 'integer');

        $this->assertSame(30, Setting::get('lead_window'));
    }

    public function test_inactive_area_is_excluded_from_property_create_options(): void
    {
        LocationArea::query()->create(['name' => 'Visible Area', 'city' => 'Dhaka', 'is_active' => true]);
        LocationArea::query()->create(['name' => 'Hidden Area', 'city' => 'Dhaka', 'is_active' => false]);

        $user = User::factory()->create();
        $this->assignRole($user, Role::OWNER_ADMIN);

        $this->actingAs($user)
            ->get(route('admin.properties.create'))
            ->assertOk()
            ->assertSee('Visible Area')
            ->assertDontSee('Hidden Area');
    }

    public function test_sales_user_cannot_update_settings(): void
    {
        $user = User::factory()->create();
        $this->assignRole($user, Role::SALES_USER);

        $this->actingAs($user)
            ->put(route('admin.settings.update'), ['settings' => ['phone' => '017']])
            ->assertForbidden();
    }
}
