<?php

declare(strict_types=1);

namespace App\Application\DTOs\Notifications;

use InvalidArgumentException;

/**
 * @phpstan-type NotificationDataArray array<string, mixed>
 */
final class NotificationPayload
{
    /**
     * @var NotificationDataArray
     */
    private array $data;

    /**
     * @param NotificationDataArray $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param NotificationDataArray $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function category(): ?string
    {
        $type = $this->data['type'] ?? null;

        if ($type === null) {
            return null;
        }

        if (! is_string($type)) {
            throw new InvalidArgumentException('Notification payload type must be a string when present.');
        }

        return $type;
    }

    /**
     * @return NotificationDataArray
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
