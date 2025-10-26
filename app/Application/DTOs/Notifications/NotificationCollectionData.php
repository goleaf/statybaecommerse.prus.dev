<?php

declare(strict_types=1);

namespace App\Application\DTOs\Notifications;

use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * @phpstan-type NotificationList list<NotificationData>
 */
final class NotificationCollectionData
{
    /**
     * @var NotificationList
     */
    private array $notifications;

    public function __construct(
        array $notifications,
        public readonly PaginationData $pagination,
    ) {
        $this->notifications = array_values($notifications);
    }

    /**
     * @return NotificationList
     */
    public function notifications(): array
    {
        return $this->notifications;
    }

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        $items = [];
        foreach ($paginator->items() as $item) {
            if ($item instanceof NotificationData) {
                $items[] = $item;

                continue;
            }

            if (! $item instanceof Notification) {
                throw new InvalidArgumentException('Paginator items must be notification models.');
            }

            $items[] = NotificationData::fromModel($item);
        }

        $pagination = new PaginationData(
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
            from: $paginator->firstItem(),
            to: $paginator->lastItem(),
        );

        return new self($items, $pagination);
    }

    /**
     * @return array{data: array<int, array<string, mixed>>, pagination: array<string, int|null>}
     */
    public function toArray(): array
    {
        return [
            'data'       => array_map(static fn (NotificationData $notification): array => $notification->toArray(), $this->notifications),
            'pagination' => $this->pagination->toArray(),
        ];
    }
}
