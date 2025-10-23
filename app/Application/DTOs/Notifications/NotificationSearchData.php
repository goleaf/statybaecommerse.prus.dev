<?php

declare(strict_types=1);

namespace App\Application\DTOs\Notifications;

use InvalidArgumentException;

final class NotificationSearchData extends NotificationFilterData
{
    public function __construct(
        public readonly string $query,
        int $perPage = 25,
        ?string $type = null,
        ?bool $read = null,
    ) {
        $sanitised = trim($query);
        if ($sanitised === '') {
            throw new InvalidArgumentException('Search query must be provided.');
        }

        parent::__construct($perPage, $type, $read);
    }
}
