<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use App\Support\Cache\CacheKeys;
use Illuminate\Support\Facades\Cache;

final class ProductRepository
{
    private const SEARCH_CACHE_TTL_MINUTES = 5;
    private const SHOW_CACHE_TTL_MINUTES = 5;

    public function __construct(private readonly CacheRepository $cache)
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
