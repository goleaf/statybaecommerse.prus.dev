<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

final class NotificationFilterData
{
    private function __construct(
        public readonly ?string $type,
        public readonly ?bool $read,
    ) {
        if ($this->type !== null && $this->type === '') {
            throw new InvalidArgumentException('Notification type cannot be an empty string.');
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $rawType = $input['type'] ?? null;
        $type = is_string($rawType) ? trim($rawType) : null;
        if ($type === '') {
            $type = null;
        }

        $read = null;
        if (array_key_exists('read', $input)) {
            $value = $input['read'];
            if (is_bool($value)) {
                $read = $value;
            } elseif ($value === 1 || $value === '1') {
                $read = true;
            } elseif ($value === 0 || $value === '0') {
                $read = false;
            } elseif ($value === null || $value === '') {
                $read = null;
            } else {
                throw new InvalidArgumentException('Read filter must be a boolean value.');
            }
        }

        return new self($type, $read);
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function read(): ?bool
    {
        return $this->read;
    }

    public function apply(Builder $builder): Builder
    {
        if ($this->type !== null) {
            $builder->byType($this->type);
        }

        if ($this->read !== null) {
            $this->read ? $builder->read() : $builder->unread();
        }

        return $builder;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'read' => $this->read,
        ];
    }
}
