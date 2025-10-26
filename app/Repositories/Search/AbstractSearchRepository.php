<?php

declare(strict_types=1);

namespace App\Repositories\Search;

use App\Data\SearchQueryData;
use App\Services\SearchCacheService;
use Illuminate\Database\ConnectionInterface;

abstract class AbstractSearchRepository
{
    public function __construct(
        protected ConnectionInterface $connection,
        protected SearchCacheService $cache
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    final public function search(SearchQueryData $queryData, int $limit): array
    {
        return $this->remember($this->type(), $queryData, $limit, function () use ($queryData, $limit) {
            $rows = $this->connection->select($this->searchStatement($limit), $this->bindings($queryData, $limit));

            return array_map(fn ($row): array => $this->mapRow($row, $queryData), $rows);
        });
    }

    /**
     * @return array<int, mixed>
     */
    final public function explain(SearchQueryData $queryData, int $limit): array
    {
        return $this->connection->select(
            'EXPLAIN ' . $this->searchStatement($limit),
            $this->bindings($queryData, $limit)
        );
    }

    abstract protected function type(): string;

    protected function wildcard(string $value): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);

        return '%' . $escaped . '%';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function remember(string $type, SearchQueryData $queryData, int $limit, callable $resolver): array
    {
        $context = array_merge($queryData->context(), [
            'type'  => $type,
            'limit' => $limit,
        ]);

        $cacheKey = $this->cache->generateCacheKey($queryData->query(), $context);

        $cached = $this->cache->getCachedResults($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $results = $resolver();

        $this->cache->cacheSearchResults($cacheKey, $results, $queryData->query(), $context);

        return $results;
    }

    abstract protected function searchStatement(int $limit): string;

    /**
     * @return array<int, mixed>
     */
    abstract protected function bindings(SearchQueryData $queryData, int $limit): array;

    abstract protected function mapRow(object $row, SearchQueryData $queryData): array;
}
