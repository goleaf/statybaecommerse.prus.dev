<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Data\SearchQueryData;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource wrapper that keeps the search response contract consistent.
 */
final class SearchResultResource extends JsonResource
{
    /**
     * @param  array<string, mixed>  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $payload = is_array($this->resource) ? $this->resource : [];

        // Normalise optional keys so clients never need to guard against undefined indexes.
        return [
            'data' => $payload['data'] ?? [],
            'meta' => $this->normaliseMeta($payload['meta'] ?? []),
            'buckets' => $payload['buckets'] ?? [],
            'aggregations' => $payload['aggregations'] ?? null,
            'correction' => $payload['correction'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function normaliseMeta(array $meta): array
    {
        $defaults = [
            'query' => '',
            'page' => 1,
            'per_page' => SearchQueryData::DEFAULT_PER_PAGE,
            'max_per_page' => SearchQueryData::MAX_PER_PAGE,
            'total_results' => 0,
            'returned' => 0,
            'has_more' => false,
            'took_ms' => 0,
            'types' => ['product', 'category', 'brand'],
            'filters' => [],
            'sort' => SearchQueryData::ALLOWED_SORTS[0],
            'cached' => false,
            'blocked' => false,
        ];

        $merged = array_merge($defaults, $meta);

        if (! is_array($merged['types'])) {
            $merged['types'] = $defaults['types'];
        } else {
            $merged['types'] = array_values($merged['types']);
        }

        if (! is_array($merged['filters'])) {
            $merged['filters'] = [];
        }

        return $merged;
    }
}
