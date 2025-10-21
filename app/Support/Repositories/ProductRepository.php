<?php

declare(strict_types=1);

namespace App\Support\Repositories;

use App\Support\Cache\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class ProductRepository
{
    public function count(?string $connection = null): int
    {
        $defaultConnection = config('database.default');

        if ($connection !== null && $connection !== $defaultConnection) {
            return $this->countUsingConnection($connection);
        }

        $callback = static fn (): int => (int) DB::table('products')->count();
        $expiresAt = now()->addSeconds(CacheKeys::TTL_MINUTE);

        if (! Cache::supportsTags()) {
            /** @var int $count */
            $count = Cache::remember(CacheKeys::productTotalCount(), $expiresAt, $callback);

            return $count;
        }

        /** @var int $count */
        $count = Cache::tags([CacheKeys::productAggregateTag(), CacheKeys::dashboardTag()])
            ->remember(CacheKeys::productTotalCount(), $expiresAt, $callback);

        return $count;
    }

    private function countUsingConnection(string $connection): int
    {
        return (int) DB::connection($connection)->table('products')->count();
    }
}
