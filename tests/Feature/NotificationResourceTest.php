<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Livewire;
use Tests\TestCase;

final class NotificationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_view_notifications_list(): void
    {
        // Create test notifications
        DatabaseNotification::create([
            'id' => 'test-notification-1',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'Test notification 1'],
        ]);

        DatabaseNotification::create([
            'id' => 'test-notification-2',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'Test notification 2'],
            'read_at' => now(),
        ]);

        $this->actingAs($this->adminUser)
            ->get('/admin/notifications')
            ->assertOk()
            ->assertSee('Test notification 1')
            ->assertSee('Test notification 2');
    }

    public function test_admin_can_view_single_notification(): void
    {
        $notification = DatabaseNotification::create([
            'id' => 'test-notification-view',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'Test notification for viewing'],
        ]);

        $this->actingAs($this->adminUser)
            ->get("/admin/notifications/{$notification->id}")
            ->assertOk()
            ->assertSee('Test notification for viewing');
    }

    public function test_admin_can_mark_notification_as_read(): void
    {
        $notification = DatabaseNotification::create([
            'id' => 'test-notification-read',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'Test notification to mark as read'],
        ]);

        $this->assertNull($notification->read_at);

        $this->actingAs($this->adminUser)
            ->post("/admin/notifications/{$notification->id}/mark-as-read")
            ->assertRedirect();

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_admin_can_mark_notification_as_unread(): void
    {
        $notification = DatabaseNotification::create([
            'id' => 'test-notification-unread',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'Test notification to mark as unread'],
            'read_at' => now(),
        ]);

        $this->assertNotNull($notification->read_at);

        $this->actingAs($this->adminUser)
            ->post("/admin/notifications/{$notification->id}/mark-as-unread")
            ->assertRedirect();

        $notification->refresh();
        $this->assertNull($notification->read_at);
    }

    public function test_admin_can_filter_notifications_by_type(): void
    {
        DatabaseNotification::create([
            'id' => 'test-notification-type-1',
            'type' => 'App\Notifications\OrderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'Order notification'],
        ]);

        DatabaseNotification::create([
            'id' => 'test-notification-type-2',
            'type' => 'App\Notifications\SystemNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'System notification'],
        ]);

        $this->actingAs($this->adminUser)
            ->get('/admin/notifications?type=App\\Notifications\\OrderNotification')
            ->assertOk()
            ->assertSee('Order notification')
            ->assertDontSee('System notification');
    }

    public function test_admin_can_filter_notifications_by_read_status(): void
    {
        DatabaseNotification::create([
            'id' => 'test-notification-read-filter',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'Read notification'],
            'read_at' => now(),
        ]);

        DatabaseNotification::create([
            'id' => 'test-notification-unread-filter',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'Unread notification'],
        ]);

        $this->actingAs($this->adminUser)
            ->get('/admin/notifications?read_at=1')
            ->assertOk()
            ->assertSee('Read notification')
            ->assertDontSee('Unread notification');
    }

    public function test_admin_can_bulk_mark_notifications_as_read(): void
    {
        $notification1 = DatabaseNotification::create([
            'id' => 'test-bulk-read-1',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'Bulk read notification 1'],
        ]);

        $notification2 = DatabaseNotification::create([
            'id' => 'test-bulk-read-2',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'Bulk read notification 2'],
        ]);

        $this->assertNull($notification1->read_at);
        $this->assertNull($notification2->read_at);

        $this->actingAs($this->adminUser)
            ->post('/admin/notifications/bulk-mark-as-read', [
                'records' => [$notification1->id, $notification2->id]
            ])
            ->assertRedirect();

        $notification1->refresh();
        $notification2->refresh();
        $this->assertNotNull($notification1->read_at);
        $this->assertNotNull($notification2->read_at);
    }

    public function test_admin_can_delete_notification(): void
    {
        $notification = DatabaseNotification::create([
            'id' => 'test-notification-delete',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->adminUser->id,
            'data' => ['message' => 'Test notification to delete'],
        ]);

        $this->actingAs($this->adminUser)
            ->delete("/admin/notifications/{$notification->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id
        ]);
    }

    public function test_non_admin_cannot_access_notifications(): void
    {
        $regularUser = User::factory()->create([
            'email' => 'user@test.com',
            'is_admin' => false,
        ]);

        $this->actingAs($regularUser)
            ->get('/admin/notifications')
            ->assertForbidden();
    }
}
