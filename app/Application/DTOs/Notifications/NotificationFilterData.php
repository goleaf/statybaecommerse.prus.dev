<?php

declare(strict_types=1);

namespace App\Application\DTOs\Notifications;

use InvalidArgumentException;

class NotificationFilterData
{
    public function __construct(
        public readonly int $perPage = 25,
        public readonly ?string $type = null,
        public readonly ?bool $read = null,
    ) {
        if ($perPage < 1 || $perPage > 100) {
            throw new InvalidArgumentException('Per-page value must be between 1 and 100.');
        }

        if ($type !== null && trim($type) === '') {
            throw new InvalidArgumentException('Type filter must be a non-empty string when provided.');
        }
    }
}
