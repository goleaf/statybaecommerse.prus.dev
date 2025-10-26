<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final class NotificationPaginationMetaData implements Arrayable, JsonSerializable
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $lastPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly ?int $from,
        public readonly ?int $to,
    ) {}

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

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
