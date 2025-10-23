<?php

declare(strict_types=1);

namespace App\Support\Repositories;

use App\Support\Cache\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class UserRepository
{
    public function count(?string $connection = null): int
    {
        $defaultConnection = config('database.default');

        if ($connection !== null && $connection !== $defaultConnection) {
            return $this->countUsingConnection($connection);
        }

        $callback = static fn (): int => (int) DB::table('users')->count();
        $expiresAt = now()->addSeconds(CacheKeys::TTL_MINUTE);

        if (! Cache::supportsTags()) {
            /** @var int $count */
            $count = Cache::remember(CacheKeys::userTotalCount(), $expiresAt, $callback);

            return $count;
        }

        /** @var int $count */
        $count = Cache::tags([CacheKeys::userAggregateTag(), CacheKeys::dashboardTag()])
            ->remember(CacheKeys::userTotalCount(), $expiresAt, $callback);

        return $count;
    }

    private function countUsingConnection(string $connection): int
    {
        return (int) DB::connection($connection)->table('users')->count();
    }
}
