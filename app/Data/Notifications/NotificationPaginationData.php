<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
final class NotificationPaginationData implements Arrayable, JsonSerializable
{
    /**
     * @param list<NotificationPayloadData> $items
     */
    public function __construct(
        public readonly array $items,
        public readonly NotificationPaginationMetaData $meta,
    ) {}

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        $items = Collection::make($paginator->items())
            ->map(static fn ($notification): NotificationPayloadData => NotificationPayloadData::fromModel($notification))
            ->values()
            ->all();

        return new self(
            $items,
            new NotificationPaginationMetaData(
                $paginator->currentPage(),
                $paginator->lastPage(),
                $paginator->perPage(),
                $paginator->total(),
                $paginator->firstItem(),
                $paginator->lastItem(),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(static fn (NotificationPayloadData $payload): array => $payload->toArray(), $this->items),
            'pagination' => $this->meta->toArray(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
