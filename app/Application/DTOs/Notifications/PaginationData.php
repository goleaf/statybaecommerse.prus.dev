<?php

declare(strict_types=1);

namespace App\Application\DTOs\Notifications;

use InvalidArgumentException;

final class PaginationData
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $lastPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly ?int $from,
        public readonly ?int $to,
    ) {
        if ($currentPage < 1 || $lastPage < 0 || $perPage < 1 || $total < 0) {
            throw new InvalidArgumentException('Pagination values must be positive integers.');
        }
    }

    /**
     * @return array<string, int|null>
     */
    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage,
            'last_page'    => $this->lastPage,
            'per_page'     => $this->perPage,
            'total'        => $this->total,
            'from'         => $this->from,
            'to'           => $this->to,
        ];
    }
}
