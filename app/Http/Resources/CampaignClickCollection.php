<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\ListQuery\ListQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CampaignClickCollection extends ResourceCollection
{
    private ?ListQuery $listQuery = null;

    /**
     * @var array<string, mixed>
     */
    private array $additionalMeta = [];

    /**
     * Attach the resolved list query so consumers know which filters were applied.
     */
    public function withListQuery(ListQuery $query, array $meta = []): self
    {
        // Persist both the query context and any extra metadata so we can merge
        // it into the serialized payload during the `toArray` call.
        $this->listQuery = $query;
        $this->additionalMeta = $meta;

        return $this;
    }

    public function toArray(Request $request): array
    {
        // Build the core pagination payload before augmenting it with the
        // optional list query context.
        $payload = [
            'data' => $this->collection,
            'meta' => [
                'total'          => $this->total(),
                'count'          => $this->count(),
                'per_page'       => $this->perPage(),
                'current_page'   => $this->currentPage(),
                'total_pages'    => $this->lastPage(),
                'has_more_pages' => $this->hasMorePages(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last'  => $this->url($this->lastPage()),
                'prev'  => $this->previousPageUrl(),
                'next'  => $this->nextPageUrl(),
            ],
        ];

        if ($this->listQuery !== null) {
            $payload['meta'] = array_merge($payload['meta'], [
                'query' => [
                    // Surface the exact pagination, sorting, and filter inputs used.
                    'page'     => $this->listQuery->page(),
                    'per_page' => $this->listQuery->perPage(),
                    'sort'     => $this->listQuery->sorts(),
                    'filters'  => $this->listQuery->filters(),
                ],
            ], $this->additionalMeta);
        } elseif ($this->additionalMeta !== []) {
            // When only loose metadata is provided, append it without the query envelope.
            $payload['meta'] = array_merge($payload['meta'], $this->additionalMeta);
        }

        return $payload;
    }
}
