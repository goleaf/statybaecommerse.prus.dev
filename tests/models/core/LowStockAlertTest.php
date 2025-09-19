<?php declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Notifications\LowStockAlert;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

final class LowStockAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);

        $notification = new LowStockAlert($product);

        $this->assertEquals($product, $notification->product);
    }

    public function test_notification_uses_mail_and_database_channels(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $notification = new LowStockAlert($product);

        $channels = $notification->via($user);

        $this->assertEquals(['mail', 'database'], $channels);
    }

    public function test_notification_array_data_structure(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);

        $notification = new LowStockAlert($product);
        $data = $notification->toArray($user);

        $this->assertArrayHasKey('product_id', $data);
        $this->assertArrayHasKey('product_name', $data);
        $this->assertArrayHasKey('product_sku', $data);
        $this->assertArrayHasKey('current_stock', $data);
        $this->assertArrayHasKey('threshold', $data);
        $this->assertArrayHasKey('message', $data);
        
        $this->assertEquals($product->id, $data['product_id']);
        $this->assertEquals('Test Product', $data['product_name']);
        $this->assertEquals('TEST-001', $data['product_sku']);
        $this->assertEquals(5, $data['current_stock']);
        $this->assertEquals(10, $data['threshold']);
    }

    public function test_notification_can_be_sent_to_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);

        $notification = new LowStockAlert($product);
        $user->notify($notification);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => LowStockAlert::class,
        ]);

        $dbNotification = DatabaseNotification::where('notifiable_id', $user->id)->first();
        $this->assertEquals($product->id, $dbNotification->data['product_id']);
        $this->assertEquals('Test Product', $dbNotification->data['product_name']);
        $this->assertEquals(5, $dbNotification->data['current_stock']);
        $this->assertEquals(10, $dbNotification->data['threshold']);
    }

    public function test_notification_implements_should_queue(): void
    {
        $product = Product::factory()->create();
        $notification = new LowStockAlert($product);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $notification);
    }
}
