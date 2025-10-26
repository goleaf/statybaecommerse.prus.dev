<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Data\Notifications\NotificationFilterData;
use App\Data\Notifications\NotificationPaginationOptions;
use App\Data\Notifications\NotificationPayloadData;
use App\Data\Notifications\NotificationSearchParameters;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

final class NotificationDataTest extends TestCase
{
    public function test_filter_data_from_array_casts_values(): void
    {
        $filters = NotificationFilterData::fromArray(['type' => ' order ', 'read' => '1']);

        self::assertSame('order', $filters->type);
        self::assertTrue($filters->read);
    }

    public function test_pagination_options_enforces_bounds(): void
    {
        self::expectException(InvalidArgumentException::class);
        new NotificationPaginationOptions(0);
    }

    public function test_pagination_options_enforces_upper_bounds(): void
    {
        self::expectException(InvalidArgumentException::class);
        new NotificationPaginationOptions(101);
    }

    public function test_payload_data_from_model_includes_context(): void
    {
        $notification = new Notification([
            'id'              => (string) Str::uuid(),
            'type'            => 'App\\Notifications\\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => 1,
            'data'            => [
                'title'    => 'Test',
                'message'  => 'Message',
                'type'     => 'info',
                'urgent'   => true,
                'color'    => 'blue',
                'tags'     => ['alpha', 'beta'],
                'order_id' => 42,
            ],
        ]);
        $notification->created_at = now();
        $notification->read_at = now();

        $payload = NotificationPayloadData::fromModel($notification);
        $serialized = $payload->toArray();

        self::assertSame('App\\Notifications\\TestNotification', $serialized['notification_class']);
        self::assertSame('info', $serialized['notification_type']);
        self::assertSame('info', $serialized['category_key']);
        self::assertSame(['alpha', 'beta'], $serialized['tags']);
        self::assertTrue($serialized['is_read']);
        self::assertSame(['order_id' => 42], $serialized['context']);
        self::assertSame('info', $serialized['type']);
    }

    public function test_search_parameters_trim_query(): void
    {
        $parameters = NotificationSearchParameters::fromArray(['q' => '  keyword  ']);

        self::assertSame('keyword', $parameters->query);
        self::assertNull($parameters->filters->read);
    }
}
