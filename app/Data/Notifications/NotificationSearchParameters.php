<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use InvalidArgumentException;

final class NotificationSearchParameters
{
    public readonly string $query;

    public function __construct(
        string $query,
        public readonly NotificationFilterData $filters,
        public readonly NotificationPaginationOptions $pagination,
    ) {
        $query = trim($query);
        if ($query === '') {
            throw new InvalidArgumentException('Search query must not be empty.');
        }

        $this->query = $query;
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $query = (string) ($input['q'] ?? $input['query'] ?? '');

        return new self(
            $query,
            NotificationFilterData::fromArray($input),
            NotificationPaginationOptions::fromArray($input),
        );
    }

    public function toArray(): array
    {
        $payload = ['q' => $this->query];

        if ($this->filters->type !== null) {
            $payload['type'] = $this->filters->type;
        }

        if ($this->filters->read !== null) {
            $payload['read'] = $this->filters->read;
        }

        return array_merge($payload, $this->pagination->toArray());
    }
}
