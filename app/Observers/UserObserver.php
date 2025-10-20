<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Support\Cache\CacheKeys;
use Illuminate\Support\Facades\Cache;

final class UserObserver
{
    use ResolvesSupportedLocales;

    public function created(User $user): void
    {
        $this->flushUserCaches();
    }

    public function updated(User $user): void
    {
        $this->flushUserCaches();
    }

    public function deleted(User $user): void
    {
        $this->flushUserCaches();
    }

    public function restored(User $user): void
    {
        $this->flushUserCaches();
    }

    public function forceDeleted(User $user): void
    {
        $this->flushUserCaches();
    }

    private function flushUserCaches(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags([CacheKeys::userAggregateTag()])->flush();

            return;
        }

        Cache::forget(CacheKeys::userTotalCount());

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::dashboardMetric('new_users_today', $locale));
        }
    }
}
