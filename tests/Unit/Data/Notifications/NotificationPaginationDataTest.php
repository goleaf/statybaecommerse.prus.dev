<?php

declare(strict_types=1);

namespace Tests\Unit\Data\Notifications;

use App\Data\Notifications\NotificationPaginationData;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NotificationPaginationDataTest extends TestCase
{
    public function test_from_array_applies_defaults(): void
    {
        $pagination = NotificationPaginationData::fromArray([]);

        $this->assertSame(1, $pagination->page());
        $this->assertSame(25, $pagination->perPage());
        $this->assertSame('created_at', $pagination->sort());
        $this->assertSame('desc', $pagination->direction());
    }

    public function test_from_array_rejects_invalid_per_page(): void
    {
        $this->expectException(InvalidArgumentException::class);

        NotificationPaginationData::fromArray(['per_page' => 101]);
    }

    public function test_from_array_rejects_invalid_sort_and_direction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        NotificationPaginationData::fromArray(['sort' => 'invalid']);
    }

    public function test_from_array_rejects_invalid_direction_only(): void
    {
        $this->expectException(InvalidArgumentException::class);
        NotificationPaginationData::fromArray(['direction' => 'sideways']);
    }
}
