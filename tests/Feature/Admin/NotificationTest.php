<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_staff_can_view_their_notifications(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        DatabaseNotification::query()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\OverdueFollowUpsDigest',
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
            'data' => ['count' => 3],
            'read_at' => null,
        ]);

        $this->actingAs($owner)->get(route('admin.notifications.index'))->assertOk();
    }

    public function test_staff_can_mark_a_notification_as_read(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $notification = DatabaseNotification::query()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\OverdueFollowUpsDigest',
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
            'data' => ['count' => 2],
            'read_at' => null,
        ]);

        $this->assertNull($notification->fresh()->read_at);

        $this->actingAs($owner)->patch(route('admin.notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_staff_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $other = $this->staff(Role::SALES_USER);

        $notification = DatabaseNotification::query()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\OverdueFollowUpsDigest',
            'notifiable_type' => User::class,
            'notifiable_id' => $other->id,
            'data' => ['count' => 1],
            'read_at' => null,
        ]);

        $this->actingAs($owner)->patch(route('admin.notifications.read', $notification->id))
            ->assertRedirect();

        // Notification should not be marked read because it belongs to $other
        $this->assertNull($notification->fresh()->read_at);
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }
}
