<?php declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Notifications\OrderNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

final class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created(): void
    {
        $orderData = [
            'id' => 1,
            'order_number' => 'ORD-001',
            'total' => 100.00,
            'status' => 'pending',
            'customer_name' => 'John Doe',
        ];

        $notification = new OrderNotification('created', $orderData, 'Custom message');

        $this->assertEquals('created', $notification->action);
        $this->assertEquals($orderData, $notification->orderData);
        $this->assertEquals('Custom message', $notification->message);
    }

    public function test_notification_uses_database_channel(): void
    {
        $user = User::factory()->create();
        $orderData = ['id' => 1, 'order_number' => 'ORD-001'];
        $notification = new OrderNotification('created', $orderData);

        $channels = $notification->via($user);

        $this->assertEquals(['database'], $channels);
    }

    public function test_notification_database_data_structure(): void
    {
        $user = User::factory()->create();
        $orderData = [
            'id' => 1,
            'order_number' => 'ORD-001',
            'total' => 100.00,
            'status' => 'pending',
            'customer_name' => 'John Doe',
        ];

        $notification = new OrderNotification('created', $orderData);
        $data = $notification->toDatabase($user);

        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('action', $data);
        $this->assertArrayHasKey('order_id', $data);
        $this->assertArrayHasKey('order_number', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('sent_at', $data);
        
        $this->assertEquals('order', $data['type']);
        $this->assertEquals('created', $data['action']);
        $this->assertEquals(1, $data['order_id']);
        $this->assertEquals('ORD-001', $data['order_number']);
        $this->assertEquals($orderData, $data['data']);
    }

    public function test_notification_title_generation(): void
    {
        $user = User::factory()->create();
        $orderData = ['id' => 1, 'order_number' => 'ORD-001'];

        $actions = ['created', 'updated', 'cancelled', 'completed', 'shipped', 'delivered', 'payment_received', 'payment_failed', 'refund_processed'];
        
        foreach ($actions as $action) {
            $notification = new OrderNotification($action, $orderData);
            $data = $notification->toDatabase($user);
            
            $this->assertIsString($data['title']);
            $this->assertNotEmpty($data['title']);
        }
    }

    public function test_notification_message_generation(): void
    {
        $user = User::factory()->create();
        $orderData = ['id' => 1, 'order_number' => 'ORD-001'];

        $actions = ['created', 'updated', 'cancelled', 'completed', 'shipped', 'delivered', 'payment_received', 'payment_failed', 'refund_processed'];
        
        foreach ($actions as $action) {
            $notification = new OrderNotification($action, $orderData);
            $data = $notification->toDatabase($user);
            
            $this->assertIsString($data['message']);
            $this->assertNotEmpty($data['message']);
            $this->assertStringContainsString('ORD-001', $data['message']);
        }
    }

    public function test_notification_can_be_sent_to_user(): void
    {
        $user = User::factory()->create();
        $orderData = [
            'id' => 1,
            'order_number' => 'ORD-001',
            'total' => 100.00,
            'status' => 'pending',
        ];

        $notification = new OrderNotification('created', $orderData);
        $user->notify($notification);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => OrderNotification::class,
        ]);

        $dbNotification = DatabaseNotification::where('notifiable_id', $user->id)->first();
        $this->assertEquals('order', $dbNotification->data['type']);
        $this->assertEquals('created', $dbNotification->data['action']);
        $this->assertEquals(1, $dbNotification->data['order_id']);
        $this->assertEquals('ORD-001', $dbNotification->data['order_number']);
    }

    public function test_notification_with_custom_message(): void
    {
        $user = User::factory()->create();
        $orderData = ['id' => 1, 'order_number' => 'ORD-001'];
        $customMessage = 'Custom order message';

        $notification = new OrderNotification('created', $orderData, $customMessage);
        $data = $notification->toDatabase($user);

        $this->assertEquals($customMessage, $data['message']);
    }
}
