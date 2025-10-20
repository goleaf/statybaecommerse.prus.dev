<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\TagAwareCache;

final class ProductRepository
{
    public function count(?string $connection = null): int
    {
        $defaultConnection = config('database.default');

        if ($connection !== null && $connection !== $defaultConnection) {
            return Product::on($connection)->newQuery()->count();
        }

        return TagAwareCache::remember(
            CacheKeys::productTotalCount(),
            now()->addSeconds(CacheKeys::TTL_MINUTE),
            static fn (): int => Product::query()->count(),
            [CacheKeys::productAggregateTag(), CacheKeys::dashboardTag()]
        );
    }
}
