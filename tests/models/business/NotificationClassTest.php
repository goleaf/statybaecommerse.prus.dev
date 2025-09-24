<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Notifications\AdminNotification;
use App\Notifications\OrderNotification;
use App\Notifications\ProductNotification;
use App\Notifications\SystemNotification;
use App\Notifications\TestNotification;
use App\Notifications\UserNotification;
use PHPUnit\Framework\TestCase;

final class NotificationClassTest extends TestCase
{
    public function test_test_notification_can_be_created(): void
    {
        $notification = new TestNotification(
            title: 'Test Title',
            message: 'Test Message',
            type: 'info'
        );

        $this->assertEquals('Test Title', $notification->title);
        $this->assertEquals('Test Message', $notification->message);
        $this->assertEquals('info', $notification->type);
    }

    public function test_admin_notification_can_be_created(): void
    {
        $notification = new AdminNotification(
            title: 'Admin Title',
            message: 'Admin Message',
            type: 'warning'
        );

        $this->assertEquals('Admin Title', $notification->title);
        $this->assertEquals('Admin Message', $notification->message);
        $this->assertEquals('warning', $notification->type);
    }

    public function test_order_notification_can_be_created(): void
    {
        $orderData = [
            'id' => 1,
            'order_number' => 'ORD-001',
            'total' => 100.00,
            'status' => 'pending',
        ];

        $notification = new OrderNotification('created', $orderData, 'Custom message');

        $this->assertEquals('created', $notification->action);
        $this->assertEquals($orderData, $notification->orderData);
        $this->assertEquals('Custom message', $notification->message);
    }

    public function test_system_notification_can_be_created(): void
    {
        $systemData = [
            'maintenance_type' => 'scheduled',
            'duration' => '2 hours',
        ];

        $notification = new SystemNotification('maintenance_started', $systemData, 'Custom message');

        $this->assertEquals('maintenance_started', $notification->action);
        $this->assertEquals($systemData, $notification->systemData);
        $this->assertEquals('Custom message', $notification->message);
    }

    public function test_user_notification_can_be_created(): void
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

    public function test_product_notification_can_be_created(): void
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

    public function test_notification_defaults_to_info_type(): void
    {
        $testNotification = new TestNotification('Test Title', 'Test Message');
        $this->assertEquals('info', $testNotification->type);

        $adminNotification = new AdminNotification('Admin Title', 'Admin Message');
        $this->assertEquals('info', $adminNotification->type);
    }
}
