<?php

declare(strict_types=1);

namespace Tests\Unit\Data\Notifications;

use App\Data\Notifications\NotificationPayloadData;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotificationPayloadDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_payload_normalizes_meta_and_tags(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->forUser($user)->create([
            'data' => [
                'type' => 'order',
                'title' => 'Order created',
                'message' => 'Order #123 created',
                'urgent' => true,
                'color' => 'blue',
                'tags' => ['primary', '', ''],
                'extra' => 'value',
            ],
            'read_at' => now(),
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
