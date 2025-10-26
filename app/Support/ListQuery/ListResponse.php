<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListResponse
{
    /**
     * @param  array<string, mixed>|list<mixed> $data
     * @return array<string, mixed>
     */
    public static function fromPaginator(LengthAwarePaginator $paginator, ListQuery $query, array $data, array $extraMeta = []): array
    {
        return [
            'data'  => $data,
            'meta'  => self::meta($query, $paginator, $extraMeta),
            'links' => self::links($paginator),
        ];
    }

    public static function meta(ListQuery $query, ?LengthAwarePaginator $paginator = null, array $extraMeta = []): array
    {
        $meta = array_merge([
            'query' => [
                'page'     => $query->page(),
                'per_page' => $query->perPage(),
                'sort'     => $query->sorts(),
                'filters'  => $query->filters(),
            ],
        ], $extraMeta);

        if ($paginator !== null) {
            $meta['pagination'] = [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ];
        }

        return $meta;
    }

    public static function links(LengthAwarePaginator $paginator): array
    {
        return [
            'first' => $paginator->url(1),
            'last'  => $paginator->url($paginator->lastPage()),
            'prev'  => $paginator->previousPageUrl(),
            'next'  => $paginator->nextPageUrl(),
        ];
    }
}
