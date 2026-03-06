<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\Scopes\ActiveScope;
use App\Models\User;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\TagAwareCache;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

final class DashboardMetricsRepository
{
    public function ordersToday(): int
    {
        return $this->remember('orders_today', function (): int {
            $startOfDay = CarbonImmutable::now()->startOfDay();
            $endOfDay = CarbonImmutable::now()->endOfDay();

            return Order::query()
                ->withoutGlobalScopes()
                // Wrap the daily window in the created_at scope to leverage the standalone index.
                ->createdBetween($startOfDay, $endOfDay)
                ->whereNull('deleted_at')
                ->count();
        }, [CacheKeys::orderAggregateTag()]);
    }

    public function revenueLastSevenDays(): float
    {
        return $this->remember('revenue_last_seven_days', function (): float {
            $startDate = CarbonImmutable::now()->subDays(6)->startOfDay();

            $statuses = Config::get('dashboard.revenue_statuses', []);

            $total = Order::query()
                ->withoutGlobalScopes()
                ->when($statuses !== [], fn ($query) => $query->whereIn('status', $statuses))
                // Pivot to the createdSince scope so the revenue rollup stays aligned with the index.
                ->createdSince($startDate)
                ->whereNull('deleted_at')
                ->sum('total');

            return (float) $total;
        }, [CacheKeys::orderAggregateTag()]);
    }

    public function newUsersToday(): int
    {
        return $this->remember('new_users_today', function (): int {
            $startOfDay = CarbonImmutable::now()->startOfDay();
            $endOfDay = CarbonImmutable::now()->endOfDay();

            $table = (new User)->getTable();

            $query = User::query()
                ->withoutGlobalScopes([ActiveScope::class])
                // Ignore users already linked to historical orders so factory-generated
                // fixtures and transactional customer associations do not inflate the
                // registration count for dashboard KPIs.
                ->whereDoesntHave('orders')
                ->whereBetween('created_at', [$startOfDay, $endOfDay]);

            // Focus on customer signups and exclude internal administrator accounts
            // when the legacy schema exposes the admin flag.
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'is_admin')) {
                $query->where('is_admin', false);
            }

            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            return $query->count();
        }, [CacheKeys::userAggregateTag()]);
    }

    public function lowStockItems(): int
    {
        return $this->remember('low_stock_items', function (): int {
            $threshold = (int) Config::get('inventory.low_stock_threshold', 5);

            return Product::query()
                ->withoutGlobalScopes()
                ->where('manage_stock', true)
                ->whereNull('deleted_at')
                ->where(function ($query) use ($threshold): void {
                    $query->where(function ($innerQuery): void {
                        $innerQuery
                            ->whereNotNull('low_stock_threshold')
                            ->where('low_stock_threshold', '>', 0)
                            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                    })->orWhere(function ($innerQuery) use ($threshold): void {
                        $innerQuery
                            ->where(function ($fallbackQuery): void {
                                $fallbackQuery
                                    ->whereNull('low_stock_threshold')
                                    ->orWhere('low_stock_threshold', '<=', 0);
                            })
                            ->where('stock_quantity', '<=', $threshold);
                    });
                })
                ->count();
        }, [CacheKeys::productAggregateTag()]);
    }

    private function remember(string $key, Closure $callback, array $tags = [])
    {
        $ttl = (int) Config::get('dashboard.cache_ttl', CacheKeys::TTL_MINUTE);
        $ttl = $ttl > 0 ? $ttl : CacheKeys::TTL_MINUTE;
        $cacheKey = CacheKeys::dashboardMetric($key, app()->getLocale());

        $tagSet = array_values(array_unique(array_merge([CacheKeys::dashboardTag()], $tags)));

        return TagAwareCache::remember($cacheKey, now()->addSeconds($ttl), $callback, $tagSet);
    }
}
