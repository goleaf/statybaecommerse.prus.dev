<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

use Illuminate\Pagination\LengthAwarePaginator;

final class ListResponse
{
    /**
     * @template T
     * @param callable(T):mixed|null $transformer
     */
    public static function fromPaginator(LengthAwarePaginator $paginator, ?callable $transformer = null): array
    {
        $items = $paginator->items();

        if ($transformer !== null) {
            $items = array_map($transformer, $items);
        }

        return [
            'data' => $items,
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
            'links' => [
                'next' => $paginator->nextPageUrl(),
                'prev' => $paginator->previousPageUrl(),
            ],
        ];
    }
}
