<?php

declare(strict_types=1);

namespace App\Support\Monitoring;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Carbon;

final class CacheMetricsStore
{
    private const DEFAULT_PAYLOAD = [
        'hits'       => 0,
        'misses'     => 0,
        'stores'     => [],
        'updated_at' => null,
    ];

    public function __construct(
        private readonly CacheRepository $repository,
        private readonly string $cacheKey,
    ) {}

    public function recordHit(?string $storeName, string $key): void
    {
        $this->update(function (array $payload) use ($storeName): array {
            $payload['hits']++;
            $this->incrementStoreMetric($payload, $storeName, 'hits');

            return $payload;
        }, $storeName, $key);
    }

    public function recordMiss(?string $storeName, string $key): void
    {
        $this->update(function (array $payload) use ($storeName): array {
            $payload['misses']++;
            $this->incrementStoreMetric($payload, $storeName, 'misses');

            return $payload;
        }, $storeName, $key);
    }

    /**
     * @return array{
     *     hits: int,
     *     misses: int,
     *     hit_rate: float,
     *     stores: array<int, array{store: string, hits: int, misses: int, hit_rate: float}>,
     *     updated_at: string|null,
     * }
     */
    public function snapshot(): array
    {
        $payload = $this->repository->get($this->cacheKey, self::DEFAULT_PAYLOAD);

        $hits = (int) ($payload['hits'] ?? 0);
        $misses = (int) ($payload['misses'] ?? 0);
        $total = $hits + $misses;
        $hitRate = $total > 0 ? $hits / $total : 0.0;

        $stores = [];
        foreach ($payload['stores'] ?? [] as $name => $metrics) {
            $storeHits = (int) ($metrics['hits'] ?? 0);
            $storeMisses = (int) ($metrics['misses'] ?? 0);
            $storeTotal = $storeHits + $storeMisses;
            $stores[] = [
                'store'    => is_string($name) && $name !== '' ? $name : 'default',
                'hits'     => $storeHits,
                'misses'   => $storeMisses,
                'hit_rate' => $storeTotal > 0 ? $storeHits / $storeTotal : 0.0,
            ];
        }

        return [
            'hits'       => $hits,
            'misses'     => $misses,
            'hit_rate'   => $hitRate,
            'stores'     => $stores,
            'updated_at' => $payload['updated_at'] ?? null,
        ];
    }

    private function update(callable $callback, ?string $storeName, string $key): void
    {
        if ($this->shouldIgnoreKey($key)) {
            return;
        }

        $payload = $this->repository->get($this->cacheKey, self::DEFAULT_PAYLOAD);
        $payload = $callback($payload);
        $payload['updated_at'] = Carbon::now()->toIso8601String();

        $this->repository->forever($this->cacheKey, $payload);
    }

    private function incrementStoreMetric(array &$payload, ?string $storeName, string $metric): void
    {
        $name = $storeName ?: 'default';
        $payload['stores'][$name] ??= ['hits' => 0, 'misses' => 0];
        $payload['stores'][$name][$metric] = (int) ($payload['stores'][$name][$metric] ?? 0) + 1;
    }

    private function shouldIgnoreKey(string $key): bool
    {
        return str_starts_with($key, 'monitoring:');
    }
}
