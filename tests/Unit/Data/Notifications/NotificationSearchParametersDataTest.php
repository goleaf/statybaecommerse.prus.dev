<?php

declare(strict_types=1);

namespace Tests\Unit\Data\Notifications;

use App\Data\Notifications\NotificationFilterData;
use App\Data\Notifications\NotificationSearchParametersData;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NotificationSearchParametersDataTest extends TestCase
{
    public function test_from_array_builds_filter_and_term(): void
    {
        $search = NotificationSearchParametersData::fromArray([
            'q'    => 'urgent order',
            'type' => 'order',
            'read' => false,
        ]);

        $this->assertSame('urgent order', $search->term());
        $this->assertInstanceOf(NotificationFilterData::class, $search->filters());
        $this->assertSame('order', $search->filters()->type());
        $this->assertFalse($search->filters()->read());
    }

    public function test_from_array_requires_term(): void
    {
        $this->expectException(InvalidArgumentException::class);

        NotificationSearchParametersData::fromArray(['q' => ' ']);
    }
}
