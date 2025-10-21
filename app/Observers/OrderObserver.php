<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Support\Cache\CacheKeys;
use Illuminate\Support\Facades\Cache;

final class OrderObserver
{
    use ResolvesSupportedLocales;

    public function created(Order $order): void
    {
        $this->flushOrderCaches();
    }

    public function updated(Order $order): void
    {
        $this->flushOrderCaches();
    }

    public function deleted(Order $order): void
    {
        $this->flushOrderCaches();
    }

    public function restored(Order $order): void
    {
        $this->flushOrderCaches();
    }

    public function forceDeleted(Order $order): void
    {
        $this->flushOrderCaches();
    }

    private function flushOrderCaches(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags([CacheKeys::orderAggregateTag()])->flush();

            return;
        }

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::dashboardMetric('orders_today', $locale));
            Cache::forget(CacheKeys::dashboardMetric('revenue_last_seven_days', $locale));
        }
    }
}
