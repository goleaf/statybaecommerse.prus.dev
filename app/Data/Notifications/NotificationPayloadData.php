<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use App\Models\Notification;
use App\Support\Notifications\NotificationCategoryResolver;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;

final class NotificationPayloadData
{
    /**
     * @param array<int, string>   $tags
     * @param array<string, mixed> $meta
     */
    private function __construct(
        private readonly string $id,
        private readonly string $notificationClass,
        private readonly ?string $legacyType,
        private readonly ?string $categoryKey,
        private readonly ?string $categoryLabel,
        private readonly ?string $categoryDescription,
        private readonly ?string $title,
        private readonly ?string $message,
        private readonly bool $urgent,
        private readonly ?string $color,
        private readonly array $tags,
        private readonly ?CarbonImmutable $readAt,
        private readonly ?CarbonImmutable $createdAt,
        private readonly array $meta,
    ) {}

    public static function fromModel(Notification $notification): self
    {
        $data = is_array($notification->data) ? $notification->data : [];
        $tags = array_values(array_filter(
            (array) ($data['tags'] ?? []),
            static fn ($value): bool => is_string($value) && $value !== ''
        ));

        // Normalise all known metadata fields to keep the context payload lean.
        $meta = Arr::except($data, ['type', 'title', 'message', 'urgent', 'color', 'tags', 'category', 'notification_type']);

        $id = $notification->getAttribute('id');
        if (! is_string($id) || $id === '') {
            $key = $notification->getKey();
            $id = is_string($key) && $key !== '' ? $key : '';
        }

        $legacyType = self::normalizeString($data['type'] ?? null);
        $categoryHint = self::normalizeString($data['category'] ?? $data['notification_type'] ?? null);
        $category = NotificationCategoryResolver::resolve(
            $categoryHint ?? $legacyType,
            is_string($notification->type) ? $notification->type : null,
        );

        return new self(
            $id,
            (string) $notification->type,
            $legacyType,
            $category['key'] ?? $categoryHint ?? $legacyType,
            $category['label'] ?? null,
            $category['description'] ?? null,
            self::normalizeString($data['title'] ?? null),
            self::normalizeString($data['message'] ?? null),
            (bool) ($data['urgent'] ?? false),
            self::normalizeString($data['color'] ?? null),
            $tags,
            self::normalizeTimestamp($notification->read_at),
            self::normalizeTimestamp($notification->created_at),
            $meta,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'notification_class'   => $this->notificationClass,
            'notification_type'    => $this->categoryKey ?? $this->legacyType,
            'category'             => $this->legacyType,
            'category_key'         => $this->categoryKey ?? $this->legacyType,
            'category_label'       => $this->categoryLabel,
            'category_description' => $this->categoryDescription,
            'type'                 => $this->legacyType,
            'title'                => $this->title,
            'message'              => $this->message,
            'urgent'               => $this->urgent,
            'color'                => $this->color,
            'tags'                 => $this->tags,
            'is_read'              => $this->readAt !== null,
            'read_at'              => $this->readAt?->toIso8601String(),
            'created_at'           => $this->createdAt?->toIso8601String(),
            'meta'                 => $this->meta,
            'context'              => $this->meta,
        ];
    }

    private static function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private static function normalizeTimestamp(?CarbonInterface $timestamp): ?CarbonImmutable
    {
        if ($timestamp === null) {
            return null;
        }

        if ($timestamp instanceof CarbonImmutable) {
            return $timestamp;
        }

        return CarbonImmutable::instance($timestamp);
    }
}
