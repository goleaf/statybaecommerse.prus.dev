<?php declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Notifications\UserNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

final class UserNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created(): void
    {
        $userData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $notification = new UserNotification('registered', $userData, 'Custom message');

        $this->assertEquals('registered', $notification->action);
        $this->assertEquals($userData, $notification->userData);
        $this->assertEquals('Custom message', $notification->message);
    }

    public function test_notification_uses_database_channel(): void
    {
        $user = User::factory()->create();
        $userData = ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'];
        $notification = new UserNotification('registered', $userData);

        $channels = $notification->via($user);

        $this->assertEquals(['database'], $channels);
    }

    public function test_notification_database_data_structure(): void
    {
        $user = User::factory()->create();
        $userData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $notification = new UserNotification('registered', $userData);
        $data = $notification->toDatabase($user);

        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('action', $data);
        $this->assertArrayHasKey('user_id', $data);
        $this->assertArrayHasKey('user_name', $data);
        $this->assertArrayHasKey('user_email', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('sent_at', $data);
        
        $this->assertEquals('user', $data['type']);
        $this->assertEquals('registered', $data['action']);
        $this->assertEquals(1, $data['user_id']);
        $this->assertEquals('John Doe', $data['user_name']);
        $this->assertEquals('john@example.com', $data['user_email']);
        $this->assertEquals($userData, $data['data']);
    }

    public function test_notification_title_generation(): void
    {
        $user = User::factory()->create();
        $userData = ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'];

        $actions = ['registered', 'profile_updated', 'password_changed', 'email_verified', 'login', 'logout', 'account_suspended', 'account_activated'];
        
        foreach ($actions as $action) {
            $notification = new UserNotification($action, $userData);
            $data = $notification->toDatabase($user);
            
            $this->assertIsString($data['title']);
            $this->assertNotEmpty($data['title']);
        }
    }

    public function test_notification_message_generation(): void
    {
        $user = User::factory()->create();
        $userData = ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'];

        $actions = ['registered', 'profile_updated', 'password_changed', 'email_verified', 'login', 'logout', 'account_suspended', 'account_activated'];
        
        foreach ($actions as $action) {
            $notification = new UserNotification($action, $userData);
            $data = $notification->toDatabase($user);
            
            $this->assertIsString($data['message']);
            $this->assertNotEmpty($data['message']);
            $this->assertStringContainsString('John Doe', $data['message']);
        }
    }

    public function test_notification_can_be_sent_to_user(): void
    {
        $user = User::factory()->create();
        $userData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $notification = new UserNotification('registered', $userData);
        $user->notify($notification);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => UserNotification::class,
        ]);

        $dbNotification = DatabaseNotification::where('notifiable_id', $user->id)->first();
        $this->assertEquals('user', $dbNotification->data['type']);
        $this->assertEquals('registered', $dbNotification->data['action']);
        $this->assertEquals(1, $dbNotification->data['user_id']);
        $this->assertEquals('John Doe', $dbNotification->data['user_name']);
        $this->assertEquals('john@example.com', $dbNotification->data['user_email']);
    }

    public function test_notification_with_custom_message(): void
    {
        $user = User::factory()->create();
        $userData = ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'];
        $customMessage = 'Custom user message';

        $notification = new UserNotification('registered', $userData, $customMessage);
        $data = $notification->toDatabase($user);

        $this->assertEquals($customMessage, $data['message']);
    }

    public function test_notification_handles_missing_user_data(): void
    {
        $user = User::factory()->create();
        $userData = ['id' => 1]; // Missing name and email

        $notification = new UserNotification('registered', $userData);
        $data = $notification->toDatabase($user);

        $this->assertNull($data['user_name']);
        $this->assertNull($data['user_email']);
        $this->assertStringContainsString('Unknown User', $data['message']);
    }
}
