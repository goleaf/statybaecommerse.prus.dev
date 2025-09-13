<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Notification;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created(): void
    {
        $user = User::factory()->create();
        
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'type' => 'order_status',
            'title' => 'Order Updated',
            'message' => 'Your order has been shipped',
            'is_read' => false,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'order_status',
            'title' => 'Order Updated',
            'message' => 'Your order has been shipped',
            'is_read' => false,
        ]);
    }

    public function test_notification_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($user->id, $notification->user->id);
    }

    public function test_notification_can_belong_to_order(): void
    {
        $order = Order::factory()->create();
        $notification = Notification::factory()->create(['order_id' => $order->id]);

        $this->assertInstanceOf(Order::class, $notification->order);
        $this->assertEquals($order->id, $notification->order->id);
    }

    public function test_notification_casts_work_correctly(): void
    {
        $notification = Notification::factory()->create([
            'is_read' => true,
            'is_important' => false,
            'data' => ['key' => 'value'],
            'created_at' => now(),
        ]);

        $this->assertIsBool($notification->is_read);
        $this->assertIsBool($notification->is_important);
        $this->assertIsArray($notification->data);
        $this->assertEquals('value', $notification->data['key']);
        $this->assertInstanceOf(\Carbon\Carbon::class, $notification->created_at);
    }

    public function test_notification_fillable_attributes(): void
    {
        $notification = new Notification();
        $fillable = $notification->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('message', $fillable);
        $this->assertContains('is_read', $fillable);
    }

    public function test_notification_scope_unread(): void
    {
        $unreadNotification = Notification::factory()->create(['is_read' => false]);
        $readNotification = Notification::factory()->create(['is_read' => true]);

        $unreadNotifications = Notification::unread()->get();

        $this->assertTrue($unreadNotifications->contains($unreadNotification));
        $this->assertFalse($unreadNotifications->contains($readNotification));
    }

    public function test_notification_scope_read(): void
    {
        $unreadNotification = Notification::factory()->create(['is_read' => false]);
        $readNotification = Notification::factory()->create(['is_read' => true]);

        $readNotifications = Notification::read()->get();

        $this->assertFalse($readNotifications->contains($unreadNotification));
        $this->assertTrue($readNotifications->contains($readNotification));
    }

    public function test_notification_scope_important(): void
    {
        $importantNotification = Notification::factory()->create(['is_important' => true]);
        $normalNotification = Notification::factory()->create(['is_important' => false]);

        $importantNotifications = Notification::important()->get();

        $this->assertTrue($importantNotifications->contains($importantNotification));
        $this->assertFalse($importantNotifications->contains($normalNotification));
    }

    public function test_notification_scope_by_type(): void
    {
        $orderNotification = Notification::factory()->create(['type' => 'order_status']);
        $promotionNotification = Notification::factory()->create(['type' => 'promotion']);

        $orderNotifications = Notification::byType('order_status')->get();

        $this->assertTrue($orderNotifications->contains($orderNotification));
        $this->assertFalse($orderNotifications->contains($promotionNotification));
    }

    public function test_notification_scope_for_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $notification1 = Notification::factory()->create(['user_id' => $user1->id]);
        $notification2 = Notification::factory()->create(['user_id' => $user2->id]);

        $user1Notifications = Notification::forUser($user1->id)->get();

        $this->assertTrue($user1Notifications->contains($notification1));
        $this->assertFalse($user1Notifications->contains($notification2));
    }

    public function test_notification_scope_recent(): void
    {
        $recentNotification = Notification::factory()->create(['created_at' => now()]);
        $oldNotification = Notification::factory()->create(['created_at' => now()->subDays(10)]);

        $recentNotifications = Notification::recent()->get();

        $this->assertTrue($recentNotifications->contains($recentNotification));
        $this->assertFalse($recentNotifications->contains($oldNotification));
    }

    public function test_notification_can_have_action_url(): void
    {
        $notification = Notification::factory()->create([
            'action_url' => '/orders/123',
        ]);

        $this->assertEquals('/orders/123', $notification->action_url);
    }

    public function test_notification_can_have_action_text(): void
    {
        $notification = Notification::factory()->create([
            'action_text' => 'View Order',
        ]);

        $this->assertEquals('View Order', $notification->action_text);
    }

    public function test_notification_can_have_icon(): void
    {
        $notification = Notification::factory()->create([
            'icon' => 'heroicon-o-shopping-cart',
        ]);

        $this->assertEquals('heroicon-o-shopping-cart', $notification->icon);
    }

    public function test_notification_can_have_color(): void
    {
        $notification = Notification::factory()->create([
            'color' => 'green',
        ]);

        $this->assertEquals('green', $notification->color);
    }

    public function test_notification_can_have_expires_at(): void
    {
        $notification = Notification::factory()->create([
            'expires_at' => now()->addDays(7),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $notification->expires_at);
    }

    public function test_notification_can_have_metadata(): void
    {
        $notification = Notification::factory()->create([
            'metadata' => [
                'order_id' => 123,
                'tracking_number' => 'TRK123456',
                'estimated_delivery' => '2025-01-15',
            ],
        ]);

        $this->assertIsArray($notification->metadata);
        $this->assertEquals(123, $notification->metadata['order_id']);
        $this->assertEquals('TRK123456', $notification->metadata['tracking_number']);
        $this->assertEquals('2025-01-15', $notification->metadata['estimated_delivery']);
    }

    public function test_notification_can_be_marked_as_read(): void
    {
        $notification = Notification::factory()->create(['is_read' => false]);
        
        $notification->markAsRead();
        
        $this->assertTrue($notification->is_read);
    }

    public function test_notification_can_be_marked_as_unread(): void
    {
        $notification = Notification::factory()->create(['is_read' => true]);
        
        $notification->markAsUnread();
        
        $this->assertFalse($notification->is_read);
    }

    public function test_notification_can_check_if_expired(): void
    {
        $expiredNotification = Notification::factory()->create([
            'expires_at' => now()->subDay(),
        ]);

        $activeNotification = Notification::factory()->create([
            'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue($expiredNotification->isExpired());
        $this->assertFalse($activeNotification->isExpired());
    }

    public function test_notification_can_have_priority(): void
    {
        $notification = Notification::factory()->create([
            'priority' => 'high',
        ]);

        $this->assertEquals('high', $notification->priority);
    }

    public function test_notification_can_have_channel(): void
    {
        $notification = Notification::factory()->create([
            'channel' => 'email',
        ]);

        $this->assertEquals('email', $notification->channel);
    }

    public function test_notification_can_have_template(): void
    {
        $notification = Notification::factory()->create([
            'template' => 'order_shipped',
        ]);

        $this->assertEquals('order_shipped', $notification->template);
    }
}
