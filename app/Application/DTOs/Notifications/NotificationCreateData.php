<?php

declare(strict_types=1);

namespace App\Application\DTOs\Notifications;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class NotificationCreateData
{
    private NotificationTags $tags;

    public function __construct(
        public readonly Model $notifiable,
        public readonly string $notificationClass,
        public readonly NotificationMessageData $message,
        public readonly bool $urgent = false,
        public readonly ?string $color = null,
        ?NotificationTags $tags = null,
    ) {
        if ($notificationClass === '') {
            throw new InvalidArgumentException('Notification class must be provided.');
        }

        $this->tags = $tags ?? NotificationTags::none();
    }

    public function tags(): NotificationTags
    {
        return $this->tags;
    }
}
