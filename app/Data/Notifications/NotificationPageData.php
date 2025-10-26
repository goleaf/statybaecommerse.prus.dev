<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class NotificationPageData
{
    /**
     * @param array<int, NotificationPayloadData> $items
     */
    private function __construct(
        private readonly array $items,
        private readonly array $meta,
        private readonly array $links,
    ) {}

    /**
     * @param array<int, NotificationPayloadData> $items
     */
    public static function fromPaginator(
        LengthAwarePaginator $paginator,
        NotificationPaginationData $pagination,
        NotificationFilterData $filters,
        ?NotificationSearchParametersData $search = null,
        array $items = [],
    ): self {
        $meta = [
            'query'      => $pagination->queryMeta($filters, $search),
            'pagination' => $pagination->paginationMeta($paginator),
        ];

        $links = $pagination->links($paginator);

        return new self($items, $meta, $links);
    }

    /**
     * @return array<int, NotificationPayloadData>
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * @return array<string, ?string>
     */
    public function links(): array
    {
        return $this->links;
    }
}
