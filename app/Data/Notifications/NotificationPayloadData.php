<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use App\Models\Notification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;

final class NotificationPayloadData
{
    /**
     * @param array<string, mixed> $meta
     * @param array<int, string> $tags
     */
    private function __construct(
        private readonly string $id,
        private readonly string $notificationType,
        private readonly ?string $category,
        private readonly ?string $title,
        private readonly ?string $message,
        private readonly bool $urgent,
        private readonly ?string $color,
        private readonly array $tags,
        private readonly ?CarbonImmutable $readAt,
        private readonly CarbonImmutable $createdAt,
        private readonly array $meta,
    ) {
    }

    public static function fromModel(Notification $notification): self
    {
        $data = $notification->data ?? [];
        $tags = array_values(array_filter((array) ($data['tags'] ?? []), static fn ($value): bool => is_string($value) && $value !== ''));
        $meta = Arr::except($data, ['type', 'title', 'message', 'urgent', 'color', 'tags']);

        return new self(
            $notification->id,
            $notification->type,
            is_string($data['type'] ?? null) ? $data['type'] : null,
            is_string($data['title'] ?? null) ? $data['title'] : null,
            is_string($data['message'] ?? null) ? $data['message'] : null,
            (bool) ($data['urgent'] ?? false),
            is_string($data['color'] ?? null) ? $data['color'] : null,
            $tags,
            $notification->read_at?->toImmutable(),
            $notification->created_at->toImmutable(),
            $meta,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'notification_type' => $this->notificationType,
            'category' => $this->category,
            'title' => $this->title,
            'message' => $this->message,
            'urgent' => $this->urgent,
            'color' => $this->color,
            'tags' => $this->tags,
            'read_at' => $this->readAt?->toIso8601String(),
            'created_at' => $this->createdAt->toIso8601String(),
            'meta' => $this->meta,
        ];
    }
}
