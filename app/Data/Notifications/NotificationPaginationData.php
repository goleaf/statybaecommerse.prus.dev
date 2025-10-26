<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

final class NotificationPaginationData
{
    private const DEFAULT_PER_PAGE = 25;

    private const MAX_PER_PAGE = 100;

    private const DEFAULT_SORT = 'created_at';

    private const DEFAULT_DIRECTION = 'desc';

    private const ALLOWED_SORTS = ['created_at', 'type'];

    private function __construct(
        private readonly int $page,
        private readonly int $perPage,
        private readonly string $sort,
        private readonly string $direction,
    ) {}

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $page = (int) ($input['page'] ?? 1);
        if ($page < 1) {
            throw new InvalidArgumentException('Page must be at least 1.');
        }

        $perPage = (int) ($input['per_page'] ?? self::DEFAULT_PER_PAGE);
        if ($perPage < 1) {
            throw new InvalidArgumentException('Per page must be at least 1.');
        }
        if ($perPage > self::MAX_PER_PAGE) {
            throw new InvalidArgumentException('Per page may not be greater than ' . self::MAX_PER_PAGE . '.');
        }

        $rawSort = is_string($input['sort'] ?? null) ? strtolower($input['sort']) : self::DEFAULT_SORT;
        if (! in_array($rawSort, self::ALLOWED_SORTS, true)) {
            throw new InvalidArgumentException('Sort field is not supported for notifications.');
        }

        $rawDirection = is_string($input['direction'] ?? null) ? strtolower($input['direction']) : self::DEFAULT_DIRECTION;
        if (! in_array($rawDirection, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Sort direction must be either asc or desc.');
        }

        return new self($page, $perPage, $rawSort, $rawDirection);
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function sort(): string
    {
        return $this->sort;
    }

    public function direction(): string
    {
        return $this->direction;
    }

    public function apply(Builder $builder): Builder
    {
        $column = match ($this->sort) {
            'type'  => 'notifications.type',
            default => 'notifications.created_at',
        };

        return $builder->orderBy($column, $this->direction);
    }

    /**
     * @return array<string, mixed>
     */
    public function queryMeta(NotificationFilterData $filters, ?NotificationSearchParametersData $search = null): array
    {
        $query = [
            'filters'   => $filters->toArray(),
            'sort'      => $this->sort,
            'direction' => $this->direction,
            'page'      => $this->page,
            'per_page'  => $this->perPage,
        ];

        if ($search !== null) {
            $query['search'] = $search->term();
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    public function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'total'        => $paginator->total(),
            'count'        => $paginator->count(),
            'per_page'     => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'total_pages'  => $paginator->lastPage(),
        ];
    }

    /**
     * @return array<string, ?string>
     */
    public function links(LengthAwarePaginator $paginator): array
    {
        return [
            'first' => $paginator->url(1),
            'last'  => $paginator->url($paginator->lastPage()),
            'prev'  => $paginator->previousPageUrl(),
            'next'  => $paginator->nextPageUrl(),
        ];
    }
}
