<?php

declare(strict_types=1);

namespace App\Application\DTOs\Notifications;

use InvalidArgumentException;

/**
 * Represents the payload stored under the notification's `data` attribute.
 */
final class NotificationMessageData
{
    /**
     * @param array<string, scalar|null> $payload
     */
    public function __construct(
        public readonly string $category,
        private readonly array $payload = [],
    ) {
        if ($category === '') {
            throw new InvalidArgumentException('Notification category must be a non-empty string.');
        }
    }

    public function withUrgency(bool $urgent): self
    {
        $payload = $this->payload;
        $payload['urgent'] = $urgent;

        return new self($this->category, $payload);
    }

    public function withColor(?string $color): self
    {
        $payload = $this->payload;
        $payload['color'] = $color;

        return new self($this->category, $payload);
    }

    public function withTags(NotificationTags $tags): self
    {
        $payload = $this->payload;
        $payload['tags'] = $tags->toArray();

        return new self($this->category, $payload);
    }

    /**
     * @return array<string, scalar|null>
     */
    public function toArray(): array
    {
        return array_merge(['type' => $this->category], $this->payload);
    }
}
