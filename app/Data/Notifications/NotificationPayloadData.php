<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use App\Models\Notification;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use JsonSerializable;

final class NotificationPayloadData implements Arrayable, JsonSerializable
{
    /**
     * @param list<string> $tags
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly string $notificationClass,
        public readonly string $category,
        public readonly ?string $title = null,
        public readonly ?string $message = null,
        public readonly bool $urgent = false,
        public readonly ?string $color = null,
        public readonly array $tags = [],
        public readonly array $context = [],
        public readonly ?string $id = null,
        public readonly ?CarbonInterface $readAt = null,
        public readonly ?CarbonInterface $createdAt = null,
    ) {}

    public static function make(
        string $notificationClass,
        string $category,
        ?string $title = null,
        ?string $message = null,
        bool $urgent = false,
        ?string $color = null,
        array $tags = [],
        array $context = [],
    ): self {
        return new self(
            $notificationClass,
            $category,
            $title,
            $message,
            $urgent,
            $color,
            self::normalizeTags($tags),
            self::normalizeContext($context),
        );
    }

    public static function fromModel(Notification $notification): self
    {
        $data = $notification->data ?? [];
        $category = (string) ($data['type'] ?? 'system');
        $title = $data['title'] ?? $data['subject'] ?? null;
        $message = $data['message'] ?? $data['body'] ?? null;
        $urgent = (bool) ($data['urgent'] ?? false);
        $color = $data['color'] ?? null;
        $tags = self::normalizeTags($data['tags'] ?? []);
        $context = self::normalizeContext(Arr::except($data, ['title', 'subject', 'message', 'body', 'type', 'urgent', 'color', 'tags']));

        return new self(
            $notification->type,
            $category,
            $title,
            $message,
            $urgent,
            $color,
            $tags,
            $context,
            $notification->id,
            $notification->read_at,
            $notification->created_at,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toStoredData(): array
    {
        return array_filter(
            array_merge(
                $this->context,
                [
                    'title' => $this->title,
                    'message' => $this->message,
                    'type' => $this->category,
                    'urgent' => $this->urgent,
                    'color' => $this->color,
                    'tags' => $this->tags,
                ],
            ),
            static fn ($value) => $value !== null && $value !== [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'notification_class' => $this->notificationClass,
            'type' => $this->category,
            'title' => $this->title,
            'message' => $this->message,
            'urgent' => $this->urgent,
            'color' => $this->color,
            'tags' => $this->tags,
            'is_read' => $this->readAt !== null,
            'read_at' => $this->readAt?->toIso8601String(),
            'created_at' => $this->createdAt?->toIso8601String(),
            'context' => $this->context,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param array<int, string|null> $tags
     * @return list<string>
     */
    private static function normalizeTags(array $tags): array
    {
        return array_values(array_filter(array_map(static fn ($tag): ?string => is_string($tag) ? trim($tag) : null, $tags), static fn (?string $tag): bool => $tag !== null && $tag !== ''));
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private static function normalizeContext(array $context): array
    {
        return array_filter($context, static fn ($value): bool => $value !== null);
    }
}
