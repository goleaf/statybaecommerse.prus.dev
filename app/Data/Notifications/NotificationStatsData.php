<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final class NotificationStatsData implements Arrayable, JsonSerializable
{
    public function __construct(
        public readonly int $total,
        public readonly int $read,
        public readonly int $unread,
        public readonly int $urgent,
    ) {}

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'read' => $this->read,
            'unread' => $this->unread,
            'urgent' => $this->urgent,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
