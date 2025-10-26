<?php

declare(strict_types=1);

namespace App\Data\Notifications;

final class NotificationStatsData
{
    private function __construct(
        private readonly int $total,
        private readonly int $read,
        private readonly int $unread,
        private readonly int $urgent,
    ) {}

    public static function fromCounts(int $total, int $read, int $unread, int $urgent): self
    {
        return new self($total, $read, $unread, $urgent);
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'total'  => $this->total,
            'read'   => $this->read,
            'unread' => $this->unread,
            'urgent' => $this->urgent,
        ];
    }
}
