<?php

declare(strict_types=1);

namespace App\Application\DTOs\Notifications;

use InvalidArgumentException;

final class NotificationStatsData
{
    public function __construct(
        public readonly int $total,
        public readonly int $read,
        public readonly int $unread,
        public readonly int $urgent,
        public readonly int $today,
        public readonly int $thisWeek,
        public readonly int $thisMonth,
    ) {
        foreach (['total' => $total, 'read' => $read, 'unread' => $unread, 'urgent' => $urgent, 'today' => $today, 'thisWeek' => $thisWeek, 'thisMonth' => $thisMonth] as $label => $value) {
            if ($value < 0) {
                throw new InvalidArgumentException(sprintf('Notification stat "%s" cannot be negative.', $label));
            }
        }
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'read' => $this->read,
            'unread' => $this->unread,
            'urgent' => $this->urgent,
            'today' => $this->today,
            'this_week' => $this->thisWeek,
            'this_month' => $this->thisMonth,
        ];
    }
}
