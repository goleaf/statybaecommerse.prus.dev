<?php

declare(strict_types=1);

namespace App\Application\DTOs\Notifications;

use InvalidArgumentException;

final class NotificationBulkActionResult
{
    public function __construct(private readonly int $count)
    {
        if ($count < 0) {
            throw new InvalidArgumentException('Affected notification count cannot be negative.');
        }
    }

    public function count(): int
    {
        return $this->count;
    }
}
