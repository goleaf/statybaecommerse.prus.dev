<?php

declare(strict_types=1);

namespace App\Data\Notifications;

final class NotificationFilterData
{
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?bool $read = null,
    ) {}

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $type = null;
        if (array_key_exists('type', $input)) {
            $candidate = trim((string) $input['type']);
            $type = $candidate !== '' ? $candidate : null;
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
            }
        }

        return new self($type, $read);
    }

    public function withType(?string $type): self
    {
        return new self($type, $this->read);
    }

    public function withRead(?bool $read): self
    {
        return new self($this->type, $read);
    }
}
