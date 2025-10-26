<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

final class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
        ]);
    }

    public function test_notification_index_returns_view(): void
    {
        $this->actingAs($this->user)
            ->get('/notifications')
            ->assertOk()
            ->assertViewIs('notifications.index');
    }

    public function test_mark_notification_as_read_success(): void
    {
        $notification = DatabaseNotification::create([
            'id'              => 'test-notification-read',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Test', 'message' => 'Test message'],
        ]);

        $this->assertNull($notification->read_at);

        $response = $this->actingAs($this->user)
            ->postJson("/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('Notification marked as read'),
            ]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_notification_as_read_with_morph_alias_success(): void
    {
        $originalMorphMap = Relation::morphMap();

        // Register a temporary morph map alias to emulate polymorphic notification storage customisation.
        Relation::morphMap([
            'frontend-user' => User::class,
        ], false);

        try {
            $notification = DatabaseNotification::create([
                'id'              => 'test-notification-alias',
                'type'            => TestNotification::class,
                'notifiable_type' => 'frontend-user',
                'notifiable_id'   => $this->user->id,
                'data'            => ['title' => 'Test', 'message' => 'Test message'],
            ]);

            $response = $this->actingAs($this->user)
                ->postJson("/notifications/{$notification->id}/read");

            $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => __('Notification marked as read'),
                ]);

            $notification->refresh();
            $this->assertNotNull($notification->read_at);
        } finally {
            // Reset the morph map to avoid leaking state into subsequent tests.
            if ($originalMorphMap === []) {
                Relation::morphMap([], false);
            } else {
                Relation::morphMap($originalMorphMap, false);
            }
        }
    }

    public function test_mark_notification_as_read_not_found(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/notifications/non-existent-id/read');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Notification not found',
            ]);
    }

    public function test_mark_notification_as_read_unauthorized(): void
    {
        $otherUser = User::factory()->create();
        $notification = DatabaseNotification::create([
            'id'              => 'test-notification-unauthorized',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $otherUser->id,
            'data'            => ['title' => 'Test', 'message' => 'Test message'],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/notifications/{$notification->id}/read");

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Notification not found',
            ]);
    }

    public function test_mark_notification_as_unread_success(): void
    {
        $notification = DatabaseNotification::create([
            'id'              => 'test-notification-unread',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Test', 'message' => 'Test message'],
            'read_at'         => now(),
        ]);

        $this->assertNotNull($notification->read_at);

        $response = $this->actingAs($this->user)
            ->postJson("/notifications/{$notification->id}/unread");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('Notification marked as unread'),
            ]);

        $notification->refresh();
        $this->assertNull($notification->read_at);
    }

    public function test_mark_notification_as_unread_not_found(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/notifications/non-existent-id/unread');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Notification not found',
            ]);
    }

    public function test_mark_notification_as_unread_unauthorized(): void
    {
        $otherUser = User::factory()->create();

        $notification = DatabaseNotification::create([
            'id'              => 'test-notification-unauthorized-unread',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $otherUser->id,
            'data'            => ['title' => 'Test', 'message' => 'Test message'],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/notifications/{$notification->id}/unread");

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Notification not found',
            ]);
    }

    public function test_mark_all_notifications_as_read(): void
    {
        // Create multiple unread notifications
        DatabaseNotification::create([
            'id'              => 'test-notification-1',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Test 1', 'message' => 'Test message 1'],
        ]);

        DatabaseNotification::create([
            'id'              => 'test-notification-2',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Test 2', 'message' => 'Test message 2'],
        ]);

        $this->user->refresh();

        $this->assertEquals(2, $this->user->unreadNotifications()->count());

        $response = $this->actingAs($this->user)
            ->postJson('/notifications/read-all');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('All notifications marked as read'),
            ]);

        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }

    public function test_delete_notification_success(): void
    {
        $notification = DatabaseNotification::create([
            'id'              => 'test-notification-delete',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Test', 'message' => 'Test message'],
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/notifications/{$notification->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('Notification deleted'),
            ]);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_delete_notification_not_found(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson('/notifications/non-existent-id');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Notification not found',
            ]);
    }

    public function test_delete_notification_not_found_via_standard_request(): void
    {
        $response = $this->actingAs($this->user)
            ->delete('/notifications/non-existent-id');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Notification not found',
            ]);
    }

    public function test_delete_notification_unauthorized(): void
    {
        $otherUser = User::factory()->create();
        $notification = DatabaseNotification::create([
            'id'              => 'test-notification-unauthorized-delete',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $otherUser->id,
            'data'            => ['title' => 'Test', 'message' => 'Test message'],
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/notifications/{$notification->id}");

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Notification not found',
            ]);
    }

    public function test_clear_all_notifications(): void
    {
        // Create multiple notifications
        DatabaseNotification::create([
            'id'              => 'test-notification-clear-1',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Test 1', 'message' => 'Test message 1'],
        ]);

        DatabaseNotification::create([
            'id'              => 'test-notification-clear-2',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Test 2', 'message' => 'Test message 2'],
        ]);

        $this->user->refresh();

        $this->assertEquals(2, $this->user->notifications()->count());

        $response = $this->actingAs($this->user)
            ->deleteJson('/notifications');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('All notifications cleared'),
            ]);

        $this->assertEquals(0, $this->user->notifications()->count());
    }

    public function test_get_unread_count(): void
    {
        // Create unread notifications
        DatabaseNotification::create([
            'id'              => 'test-notification-count-1',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Test 1', 'message' => 'Test message 1'],
        ]);

        DatabaseNotification::create([
            'id'              => 'test-notification-count-2',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Test 2', 'message' => 'Test message 2'],
        ]);

        // Create read notification
        DatabaseNotification::create([
            'id'              => 'test-notification-count-3',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Test 3', 'message' => 'Test message 3'],
            'read_at'         => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/notifications/unread-count');

        $response->assertOk()
            ->assertJson([
                'count' => 2,
            ]);
    }

    public function test_get_recent_notifications(): void
    {
        // Create notifications with different timestamps
        DatabaseNotification::create([
            'id'              => 'test-notification-recent-1',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Recent 1', 'message' => 'Recent message 1'],
            'created_at'      => now()->subMinutes(1),
        ]);

        DatabaseNotification::create([
            'id'              => 'test-notification-recent-2',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Recent 2', 'message' => 'Recent message 2'],
            'created_at'      => now()->subMinutes(2),
        ]);

        DatabaseNotification::create([
            'id'              => 'test-notification-recent-3',
            'type'            => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'Recent 3', 'message' => 'Recent message 3'],
            'created_at'      => now()->subMinutes(3),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/notifications/recent');

        $response->assertOk()
            ->assertJsonStructure([
                'notifications' => [
                    '*' => [
                        'id',
                        'type',
                        'title',
                        'message',
                        'read_at',
                        'created_at',
                    ],
                ],
            ]);

        /** @var array<int, array<string, mixed>> $notifications */
        $notifications = $response->json('notifications');
        $this->assertCount(3, $notifications);

        // Should be ordered by latest first
        $this->assertEquals('test-notification-recent-1', $notifications[0]['id']);
        $this->assertEquals('Recent 1', $notifications[0]['title']);
        $this->assertEquals('Recent message 1', $notifications[0]['message']);
    }

    public function test_get_recent_notifications_limits_to_5(): void
    {
        // Create 7 notifications
        for ($i = 1; $i <= 7; $i++) {
            DatabaseNotification::create([
                'id'              => "test-notification-limit-{$i}",
                'type'            => TestNotification::class,
                'notifiable_type' => User::class,
                'notifiable_id'   => $this->user->id,
                'data'            => ['title' => "Test {$i}", 'message' => "Test message {$i}"],
                'created_at'      => now()->subMinutes($i),
            ]);
        }

        $response = $this->actingAs($this->user)
            ->getJson('/notifications/recent');

        $response->assertOk();
        /** @var array<int, array<string, mixed>> $notifications */
        $notifications = $response->json('notifications');
        $this->assertCount(5, $notifications);
    }

    public function test_guest_cannot_access_notifications(): void
    {
        $this->get('/notifications')
            ->assertRedirect('/login');

        $this->postJson('/notifications/test-id/read')
            ->assertStatus(401);

        $this->postJson('/notifications/read-all')
            ->assertStatus(401);

        $this->deleteJson('/notifications/test-id')
            ->assertStatus(401);

        $this->deleteJson('/notifications')
            ->assertStatus(401);

        $this->getJson('/notifications/unread-count')
            ->assertStatus(401);

        $this->getJson('/notifications/recent')
            ->assertStatus(401);
    }

    public function test_mark_notification_as_read_missing_returns_json_payload(): void
    {
        $this->actingAs($this->user)
            ->post('/notifications/non-existent-id/read')
            ->assertStatus(404)
            ->assertJson([
                'error' => 'Notification not found',
            ]);
    }
}
