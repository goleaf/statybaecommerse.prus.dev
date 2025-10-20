<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use App\Support\Cache\CacheKeys;
use Illuminate\Support\Facades\Cache;

final class ProductRepository
{
    public function count(?string $connection = null): int
    {
        $defaultConnection = config('database.default');

        if ($connection !== null && $connection !== $defaultConnection) {
            return Product::on($connection)->newQuery()->count();
        }

        if (! Cache::supportsTags()) {
            return Cache::remember(
                CacheKeys::productTotalCount(),
                now()->addSeconds(CacheKeys::TTL_MINUTE),
                static fn (): int => Product::query()->count(),
            );
        }

        return Cache::tags([CacheKeys::productAggregateTag(), CacheKeys::dashboardTag()])
            ->remember(
                CacheKeys::productTotalCount(),
                now()->addSeconds(CacheKeys::TTL_MINUTE),
                static fn (): int => Product::query()->count(),
            );
    }
}
