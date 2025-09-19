<?php declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Notifications\ProductNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

final class ProductNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created(): void
    {
        $productData = [
            'id' => 1,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
        ];

        $notification = new ProductNotification('created', $productData, 'Custom message');

        $this->assertEquals('created', $notification->action);
        $this->assertEquals($productData, $notification->productData);
        $this->assertEquals('Custom message', $notification->message);
    }

    public function test_notification_uses_database_channel(): void
    {
        $user = User::factory()->create();
        $productData = ['id' => 1, 'name' => 'Test Product'];
        $notification = new ProductNotification('created', $productData);

        $channels = $notification->via($user);

        $this->assertEquals(['database'], $channels);
    }

    public function test_notification_database_data_structure(): void
    {
        $user = User::factory()->create();
        $productData = [
            'id' => 1,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
        ];

        $notification = new ProductNotification('created', $productData);
        $data = $notification->toDatabase($user);

        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('action', $data);
        $this->assertArrayHasKey('product_id', $data);
        $this->assertArrayHasKey('product_name', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('sent_at', $data);
        
        $this->assertEquals('product', $data['type']);
        $this->assertEquals('created', $data['action']);
        $this->assertEquals(1, $data['product_id']);
        $this->assertEquals('Test Product', $data['product_name']);
        $this->assertEquals($productData, $data['data']);
    }

    public function test_notification_title_generation(): void
    {
        $user = User::factory()->create();
        $productData = ['id' => 1, 'name' => 'Test Product'];

        $actions = ['created', 'updated', 'deleted', 'low_stock', 'out_of_stock', 'back_in_stock', 'price_changed', 'review_added'];
        
        foreach ($actions as $action) {
            $notification = new ProductNotification($action, $productData);
            $data = $notification->toDatabase($user);
            
            $this->assertIsString($data['title']);
            $this->assertNotEmpty($data['title']);
        }
    }

    public function test_notification_message_generation(): void
    {
        $user = User::factory()->create();
        $productData = ['id' => 1, 'name' => 'Test Product'];

        $actions = ['created', 'updated', 'deleted', 'low_stock', 'out_of_stock', 'back_in_stock', 'price_changed', 'review_added'];
        
        foreach ($actions as $action) {
            $notification = new ProductNotification($action, $productData);
            $data = $notification->toDatabase($user);
            
            $this->assertIsString($data['message']);
            $this->assertNotEmpty($data['message']);
            $this->assertStringContainsString('Test Product', $data['message']);
        }
    }

    public function test_notification_can_be_sent_to_user(): void
    {
        $user = User::factory()->create();
        $productData = [
            'id' => 1,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
        ];

        $notification = new ProductNotification('created', $productData);
        $user->notify($notification);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => ProductNotification::class,
        ]);

        $dbNotification = DatabaseNotification::where('notifiable_id', $user->id)->first();
        $this->assertEquals('product', $dbNotification->data['type']);
        $this->assertEquals('created', $dbNotification->data['action']);
        $this->assertEquals(1, $dbNotification->data['product_id']);
        $this->assertEquals('Test Product', $dbNotification->data['product_name']);
    }

    public function test_notification_with_custom_message(): void
    {
        $user = User::factory()->create();
        $productData = ['id' => 1, 'name' => 'Test Product'];
        $customMessage = 'Custom product message';

        $notification = new ProductNotification('created', $productData, $customMessage);
        $data = $notification->toDatabase($user);

        $this->assertEquals($customMessage, $data['message']);
    }

    public function test_notification_handles_missing_product_data(): void
    {
        $user = User::factory()->create();
        $productData = ['id' => 1]; // Missing name

        $notification = new ProductNotification('created', $productData);
        $data = $notification->toDatabase($user);

        $this->assertNull($data['product_name']);
        $this->assertStringContainsString('Unknown Product', $data['message']);
    }
}
