<?php

declare(strict_types=1);

namespace Tests\Unit\Data\Notifications;

use App\Data\Notifications\NotificationStatsData;
use PHPUnit\Framework\TestCase;

final class NotificationStatsDataTest extends TestCase
{
    public function test_to_array_returns_expected_shape(): void
    {
        $stats = NotificationStatsData::fromCounts(total: 10, read: 4, unread: 5, urgent: 1);

        $this->assertSame([
            'total'  => 10,
            'read'   => 4,
            'unread' => 5,
            'urgent' => 1,
        ], $stats->toArray());
    }
}
