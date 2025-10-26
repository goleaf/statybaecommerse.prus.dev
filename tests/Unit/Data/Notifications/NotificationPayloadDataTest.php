<?php

declare(strict_types=1);

namespace Tests\Unit\Data\Notifications;

use App\Data\Notifications\NotificationPayloadData;
use App\Models\Notification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Tests\TestCase;

final class NotificationPayloadDataTest extends TestCase
{
    public function test_payload_normalizes_meta_and_tags(): void
    {
        $now = CarbonImmutable::parse('2024-01-01T12:00:00Z');
        $notification = new Notification;
        $notification->forceFill([
            'id'   => (string) Str::uuid(),
            'type' => 'App\\Notifications\\OrderCreatedNotification',
            'data' => [
                'type'    => 'order',
                'title'   => 'Order created',
                'message' => 'Order #123 created',
                'urgent'  => true,
                'color'   => 'blue',
                'tags'    => ['primary', '', ''],
                'extra'   => 'value',
            ],
            'read_at'    => $now,
            'created_at' => $now,
        ]);

        $payload = NotificationPayloadData::fromModel($notification);
        $data = $payload->toArray();

        $this->assertSame($notification->id, $data['id']);
        $this->assertSame('order', $data['category']);
        $this->assertTrue($data['urgent']);
        $this->assertSame(['primary'], $data['tags']);
        $this->assertSame(['extra' => 'value'], $data['meta']);
        $this->assertNotNull($data['read_at']);
    }
}
