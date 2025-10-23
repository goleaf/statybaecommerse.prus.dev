<?php

declare(strict_types=1);

namespace App\Support\Stats\Inline;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

final class CustomerSeries
{
    private const CACHE_TTL = 600; // 10 minutes

    /**
     * Build a 12 month revenue series for a customer.
     *
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    public static function ordersLast12m(int $customerId): array
    {
        $cacheKey = "inline:customer:{$customerId}:ltv:12m";

        return Cache::remember($cacheKey, self::CACHE_TTL, static function () use ($customerId): array {
            $end = Carbon::now()->startOfMonth();
            $start = $end->copy()->subMonths(11);

            $foreignKey = Schema::hasColumn('orders', 'customer_id') ? 'customer_id' : 'user_id';

            $connection = Order::query()->getModel()->getConnection();
            $driver = $connection->getDriverName();
            $monthExpression = $driver === 'sqlite'
                ? "strftime('%Y-%m-01', created_at)"
                : "DATE_FORMAT(created_at, '%Y-%m-01')";

            $monthlyTotals = Order::query()
                ->selectRaw("{$monthExpression} as month, SUM(total) as revenue")
                ->where($foreignKey, $customerId)
                ->whereBetween('created_at', [$start, $end->copy()->endOfMonth()])
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('revenue', 'month');

            $labels = [];
            $values = [];

            for ($offset = 0; $offset < 12; $offset++) {
                $currentMonth = $start->copy()->addMonths($offset);
                $key = $currentMonth->format('Y-m-01');

                $labels[] = $currentMonth->format('M Y');
                $values[] = (float) ($monthlyTotals[$key] ?? 0.0);
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        });
    }
}
