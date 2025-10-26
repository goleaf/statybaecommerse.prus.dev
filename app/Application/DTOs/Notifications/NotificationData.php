<?php

declare(strict_types=1);

namespace App\Application\DTOs\Notifications;

use App\Models\Notification;
use DateTimeImmutable;
use InvalidArgumentException;

final class NotificationData
{
    private NotificationTags $tags;

    public function __construct(
        public readonly string $id,
        public readonly string $notificationClass,
        public readonly string $notifiableType,
        public readonly string $notifiableId,
        public readonly NotificationPayload $payload,
        public readonly ?DateTimeImmutable $readAt,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly bool $isRead,
        public readonly bool $isUrgent,
        public readonly ?string $notificationType,
        public readonly ?string $formattedCreatedAt,
        public readonly ?string $formattedReadAt,
        public readonly ?string $title,
        public readonly ?string $message,
        public readonly ?string $color,
        NotificationTags $tags,
        public readonly ?string $attachment,
    ) {
        if ($id === '') {
            throw new InvalidArgumentException('Notification identifier cannot be empty.');
        }

        $this->tags = $tags;
    }

    public static function fromModel(Notification $notification): self
    {
        $createdAt = $notification->created_at?->toImmutable();
        $updatedAt = $notification->updated_at?->toImmutable();

        if ($createdAt === null || $updatedAt === null) {
            throw new InvalidArgumentException('Notification timestamps must be present.');
        }

        $payload = NotificationPayload::fromArray($notification->data ?? []);
        $tags = NotificationTags::from($notification->tags ?? []);

        return new self(
            id: (string) $notification->id,
            notificationClass: (string) $notification->type,
            notifiableType: (string) $notification->notifiable_type,
            notifiableId: (string) $notification->notifiable_id,
            payload: $payload,
            readAt: $notification->read_at?->toImmutable(),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            isRead: (bool) $notification->is_read,
            isUrgent: (bool) $notification->is_urgent,
            notificationType: $payload->category(),
            formattedCreatedAt: $notification->formatted_created_at,
            formattedReadAt: $notification->formatted_read_at,
            title: $notification->title,
            message: $notification->message,
            color: $notification->color,
            tags: $tags,
            attachment: $notification->attachment,
        );
    }

    public function tags(): NotificationTags
    {
        return $this->tags;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'type'                 => $this->notificationClass,
            'notifiable_type'      => $this->notifiableType,
            'notifiable_id'        => $this->notifiableId,
            'data'                 => $this->payload->toArray(),
            'read_at'              => $this->readAt?->format(DateTimeImmutable::ATOM),
            'created_at'           => $this->createdAt->format(DateTimeImmutable::ATOM),
            'updated_at'           => $this->updatedAt->format(DateTimeImmutable::ATOM),
            'is_read'              => $this->isRead,
            'is_urgent'            => $this->isUrgent,
            'notification_type'    => $this->notificationType,
            'formatted_created_at' => $this->formattedCreatedAt,
            'formatted_read_at'    => $this->formattedReadAt,
            'title'                => $this->title,
            'message'              => $this->message,
            'color'                => $this->color,
            'tags'                 => $this->tags->toArray(),
            'attachment'           => $this->attachment,
        ];
    }
}
