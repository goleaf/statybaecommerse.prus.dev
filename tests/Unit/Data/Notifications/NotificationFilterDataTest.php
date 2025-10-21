<?php

declare(strict_types=1);

namespace Tests\Unit\Data\Notifications;

use App\Data\Notifications\NotificationFilterData;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NotificationFilterDataTest extends TestCase
{
    public function test_from_array_casts_values(): void
    {
        $filters = NotificationFilterData::fromArray([
            'type' => ' order ',
            'read' => '1',
        ]);

        $this->assertSame('order', $filters->type());
        $this->assertTrue($filters->read());
    }

    public function test_from_array_rejects_invalid_read_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        NotificationFilterData::fromArray([
            'read' => 'maybe',
        ]);
    }
}
