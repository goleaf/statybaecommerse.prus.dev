<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\TagAwareCache;

final class UserRepository
{
    public function count(?string $connection = null): int
    {
        $defaultConnection = config('database.default');

        if ($connection !== null && $connection !== $defaultConnection) {
            return User::on($connection)->newQuery()->count();
        }

        return TagAwareCache::remember(
            CacheKeys::userTotalCount(),
            now()->addSeconds(CacheKeys::TTL_MINUTE),
            static fn (): int => User::query()->count(),
            [CacheKeys::userAggregateTag(), CacheKeys::dashboardTag()]
        );
    }
}
