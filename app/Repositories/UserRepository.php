<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Support\Cache\CacheKeys;
use Illuminate\Support\Facades\Cache;

final class UserRepository
{
    public function count(?string $connection = null): int
    {
        $defaultConnection = config('database.default');

        if ($connection !== null && $connection !== $defaultConnection) {
            return User::on($connection)->newQuery()->count();
        }

        if (! Cache::supportsTags()) {
            return Cache::remember(
                CacheKeys::userTotalCount(),
                now()->addSeconds(CacheKeys::TTL_MINUTE),
                static fn (): int => User::query()->count(),
            );
        }

        return Cache::tags([CacheKeys::userAggregateTag(), CacheKeys::dashboardTag()])
            ->remember(
                CacheKeys::userTotalCount(),
                now()->addSeconds(CacheKeys::TTL_MINUTE),
                static fn (): int => User::query()->count(),
            );
    }
}
