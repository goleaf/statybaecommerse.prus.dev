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

    public function withListQuery(ListQuery $query, array $meta = []): self
    {
        $this->listQuery = $query;
        $this->additionalMeta = $meta;

        return $this;
    }

    public function toArray(Request $request): array
    {
        $payload = [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'has_more_pages' => $this->hasMorePages(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
        ];

        if ($this->listQuery !== null) {
            $payload['meta'] = array_merge($payload['meta'], [
                'query' => [
                    'page' => $this->listQuery->page(),
                    'per_page' => $this->listQuery->perPage(),
                    'sort' => $this->listQuery->sorts(),
                    'filters' => $this->listQuery->filters(),
                ],
            ], $this->additionalMeta);
        } elseif ($this->additionalMeta !== []) {
            $payload['meta'] = array_merge($payload['meta'], $this->additionalMeta);
        }

        return $payload;
    }
}
