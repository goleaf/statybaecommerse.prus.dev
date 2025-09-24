<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Notifications\TestNotification;
use PHPUnit\Framework\TestCase;

final class NotificationBasicTest extends TestCase
{
    public function test_notification_can_be_created(): void
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

    public function test_notification_defaults_to_info_type(): void
    {
        $notification = new TestNotification('Test Title', 'Test Message');

        $this->assertEquals('info', $notification->type);
    }
}
