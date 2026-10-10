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

    public function test_staff_can_fetch_unread_notifications_json(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        DatabaseNotification::query()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\NewLeadNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
            'data' => [
                'message' => 'New lead from John Doe',
                'name' => 'John Doe',
                'icon' => 'inbox',
            ],
            'read_at' => null,
        ]);

        $response = $this->actingAs($owner)
            ->getJson(route('admin.notifications.unread'))
            ->assertOk();

        $response->assertJson([
            'unread_count' => 1,
        ]);
        $this->assertCount(1, $response->json('notifications'));
        $this->assertSame('New lead from John Doe', $response->json('notifications.0.message'));
    }

    public function test_staff_can_mark_all_notifications_as_read(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        DatabaseNotification::query()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\NewLeadNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
            'data' => ['message' => 'One'],
            'read_at' => null,
        ]);

        DatabaseNotification::query()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\NewLeadNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
            'data' => ['message' => 'Two'],
            'read_at' => null,
        ]);

        $this->assertSame(2, $owner->unreadNotifications()->count());

        $this->actingAs($owner)
            ->post(route('admin.notifications.mark-all-read'))
            ->assertRedirect();

        $this->assertSame(0, $owner->unreadNotifications()->count());
    }

    public function test_staff_can_click_go_which_marks_as_read_and_redirects(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $notification = DatabaseNotification::query()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\NewLeadNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
            'data' => [
                'message' => 'Enquiry for apartment',
                'url' => route('admin.leads.index'),
            ],
            'read_at' => null,
        ]);

        $this->actingAs($owner)
            ->get(route('admin.notifications.go', $notification->id))
            ->assertRedirect(route('admin.leads.index'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }
}
