<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use InvalidArgumentException;

final class NotificationPaginationOptions
{
    public function __construct(public readonly int $perPage = 25)
    {
        if ($this->perPage < 1 || $this->perPage > 100) {
            throw new InvalidArgumentException('Pagination per page value must be between 1 and 100.');
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $perPage = array_key_exists('per_page', $input)
            ? (int) $input['per_page']
            : 25;

        return new self($perPage);
    }

    public function toArray(): array
    {
        return ['per_page' => $this->perPage];
    }
}
