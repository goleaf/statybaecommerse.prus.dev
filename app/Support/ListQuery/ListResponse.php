<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListResponse
{
    /**
     * @template TValue
     *
     * @param callable(TValue): array|null $transform
     * @return array{data: array<int, mixed>, meta: array<string, mixed>, links: array<string, string|null>}
     */
    public static function fromPaginator(LengthAwarePaginator $paginator, ListQuery $query, ?callable $transform = null): array
    {
        $collection = $paginator->getCollection();

        if ($transform !== null) {
            $collection = $collection->map(static fn ($item) => $transform($item));
        }

        $data = $collection->values()->all();

        return [
            'data' => $data,
            'meta' => [
                'pagination' => [
                    'total' => $paginator->total(),
                    'count' => $paginator->count(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'sort' => [
                    'by' => $query->sortBy(),
                    'direction' => $query->sortDirection(),
                ],
                'filters' => $query->activeFilters(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];
    }
}
