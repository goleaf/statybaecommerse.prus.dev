<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

final class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created(): void
    {
        $user = User::factory()->create();
        
        $notification = DatabaseNotification::create([
            'id' => 'test-notification-id',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Test notification'],
        ]);

        $this->assertInstanceOf(DatabaseNotification::class, $notification);
        $this->assertEquals('test-notification-id', $notification->id);
        $this->assertEquals('App\Notifications\TestNotification', $notification->type);
        $this->assertEquals(User::class, $notification->notifiable_type);
        $this->assertEquals($user->id, $notification->notifiable_id);
        $this->assertEquals(['message' => 'Test notification'], $notification->data);
    }

    public function test_notification_belongs_to_notifiable(): void
    {
        $user = User::factory()->create();
        
        $notification = DatabaseNotification::create([
            'id' => 'test-notification-id',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Test notification'],
        ]);

        $this->assertInstanceOf(User::class, $notification->notifiable);
        $this->assertEquals($user->id, $notification->notifiable->id);
    }

    public function test_notification_can_be_marked_as_read(): void
    {
        $user = User::factory()->create();
        
        $notification = DatabaseNotification::create([
            'id' => 'test-notification-id',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Test notification'],
        ]);

        $this->assertNull($notification->read_at);
        
        $notification->markAsRead();
        
        $this->assertNotNull($notification->read_at);
        $this->assertTrue($notification->read());
    }

    public function test_notification_can_be_marked_as_unread(): void
    {
        $user = User::factory()->create();
        
        $notification = DatabaseNotification::create([
            'id' => 'test-notification-id',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Test notification'],
            'read_at' => now(),
        ]);

        $this->assertNotNull($notification->read_at);
        $this->assertTrue($notification->read());
        
        $notification->markAsUnread();
        
        $this->assertNull($notification->read_at);
        $this->assertFalse($notification->read());
    }

    public function test_notification_data_is_casted_to_array(): void
    {
        $user = User::factory()->create();
        
        $notification = DatabaseNotification::create([
            'id' => 'test-notification-id',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Test notification', 'count' => 5],
        ]);

        $this->assertIsArray($notification->data);
        $this->assertEquals('Test notification', $notification->data['message']);
        $this->assertEquals(5, $notification->data['count']);
    }

    public function test_notification_has_read_scope(): void
    {
        $user = User::factory()->create();
        
        // Create read notification
        DatabaseNotification::create([
            'id' => 'read-notification-id',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Read notification'],
            'read_at' => now(),
        ]);

        // Create unread notification
        DatabaseNotification::create([
            'id' => 'unread-notification-id',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Unread notification'],
        ]);

        $readNotifications = DatabaseNotification::whereNotNull('read_at')->get();
        $unreadNotifications = DatabaseNotification::whereNull('read_at')->get();

        $this->assertCount(1, $readNotifications);
        $this->assertCount(1, $unreadNotifications);
        $this->assertEquals('read-notification-id', $readNotifications->first()->id);
        $this->assertEquals('unread-notification-id', $unreadNotifications->first()->id);
    }
}
