<?php

declare(strict_types=1);

namespace App\Support\Stats;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class OrderMetrics
{
    private const CACHE_TTL_SECONDS = 60;

    private const PAID_STATUSES = [
        'processing',
        'confirmed',
        'shipped',
        'delivered',
        'completed',
    ];

    private const PAID_PAYMENT_STATUSES = [
        'paid',
        'captured',
        'settled',
        'authorized',
    ];

    /**
     * @return array{
     *     orders: int,
     *     revenue: float,
     *     aov: float,
     *     conversionRate: float,
     *     refundRate: float,
     *     newCustomers: int,
     *     revenueSparkline: array<int, float>,
     *     ordersChange: ?float,
     *     revenueChange: ?float,
     *     topProducts: array<int, array{name: string|null, quantity: int, revenue: float}>
     * }
     */
    public static function forRange(CarbonInterface $from, CarbonInterface $to): array
    {
        [$from, $to] = self::normaliseRange($from, $to);

        $cacheKey = self::cacheKey('orders.range', $from, $to);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, static function () use ($from, $to): array {
            // Build the shared paid orders query using the created_at scope so MySQL can
            // consistently rely on the orders_created_at_index for the downstream aggregations.
            $ordersQuery = self::paidOrdersQuery($from, $to);

            $ordersCount = (clone $ordersQuery)->count();
            $revenue = (float) (clone $ordersQuery)->sum('total');
            $averageOrderValue = $ordersCount > 0 ? $revenue / $ordersCount : 0.0;

            $refundRate = $ordersCount > 0
                ? ((clone $ordersQuery)->whereIn('payment_status', ['refunded', 'partially_refunded'])->count() / $ordersCount) * 100
                : 0.0;

            $sessions = TrafficMetrics::sessionsCount($from, $to);
            $conversionRate = $sessions > 0 ? ($ordersCount / $sessions) * 100 : 0.0;

            $newCustomers = Customer::query()
                ->whereBetween('created_at', [$from, $to])
                ->count();

            [$previousFrom, $previousTo] = self::previousPeriod($from, $to);
            $previousOrders = self::paidOrdersQuery($previousFrom, $previousTo)->count();
            $previousRevenue = (float) self::paidOrdersQuery($previousFrom, $previousTo)->sum('total');

            return [
                'orders'           => $ordersCount,
                'revenue'          => round($revenue, 2),
                'aov'              => round($averageOrderValue, 2),
                'conversionRate'   => round($conversionRate, 2),
                'refundRate'       => round($refundRate, 2),
                'newCustomers'     => $newCustomers,
                'revenueSparkline' => self::revenueSparkline($from, $to),
                'ordersChange'     => self::percentageChange($previousOrders, $ordersCount),
                'revenueChange'    => self::percentageChange($previousRevenue, $revenue),
                'topProducts'      => self::topProducts($from, $to, 5),
            ];
        });
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, float>, change: ?float}
     */
    public static function salesSeriesMonthly(CarbonInterface $from, CarbonInterface $to): array
    {
        [$from, $to] = self::normaliseRange($from, $to);

        $cacheKey = self::cacheKey('orders.series.monthly', $from, $to);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, static function () use ($from, $to): array {
            $trend = Trend::query(self::paidOrdersQuery($from, $to))
                ->between($from, $to)
                ->perMonth()
                ->sum('total');

            $labels = [];
            $values = [];

            foreach ($trend as $value) {
                $labels[] = CarbonImmutable::parse($value->date)->isoFormat('MMM YYYY');
                $values[] = round((float) $value->aggregate, 2);
            }

            [$previousFrom, $previousTo] = self::previousPeriod($from, $to);
            $previousTotal = (float) self::paidOrdersQuery($previousFrom, $previousTo)->sum('total');
            $currentTotal = array_sum($values);

            return [
                'labels' => $labels,
                'values' => $values,
                'change' => self::percentageChange($previousTotal, $currentTotal),
            ];
        });
    }

    /**
     * @return array{labels: array<int, string>, orders: array<int, int>, revenue: array<int, float>}
     */
    public static function ordersTrend(CarbonInterface $from, CarbonInterface $to): array
    {
        [$from, $to] = self::normaliseRange($from, $to);

        $cacheKey = self::cacheKey('orders.trend', $from, $to);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, static function () use ($from, $to): array {
            $diffInDays = $from->diffInDays($to);
            $granularityMethod = $diffInDays > 90 ? 'perMonth' : 'perDay';

            /** @var Collection<int, TrendValue> $orderTrend */
            $orderTrend = Trend::query(self::paidOrdersQuery($from, $to))
                ->between($from, $to)
                ->{$granularityMethod}()
                ->count();

            /** @var Collection<int, TrendValue> $revenueTrend */
            $revenueTrend = Trend::query(self::paidOrdersQuery($from, $to))
                ->between($from, $to)
                ->{$granularityMethod}()
                ->sum('total');

            $labels = [];
            $orders = [];
            $revenue = [];

            foreach ($orderTrend as $index => $value) {
                $labels[] = $diffInDays > 90
                    ? CarbonImmutable::parse($value->date)->isoFormat('MMM YYYY')
                    : CarbonImmutable::parse($value->date)->isoFormat('MMM D');

                $orders[$index] = (int) $value->aggregate;
            }

            foreach ($revenueTrend as $index => $value) {
                $revenue[$index] = round((float) $value->aggregate, 2);
            }

            $orders = array_values($orders);
            $revenue = array_values($revenue);

            return [
                'labels'  => $labels,
                'orders'  => $orders,
                'revenue' => $revenue,
            ];
        });
    }

    /**
     * @return array<int, array{name: string|null, quantity: int, revenue: float}>
     */
    public static function topProducts(CarbonInterface $from, CarbonInterface $to, int $limit = 5): array
    {
        [$from, $to] = self::normaliseRange($from, $to);

        $cacheKey = self::cacheKey("orders.top-products.{$limit}", $from, $to);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, static function () use ($from, $to, $limit): array {
            return OrderItem::query()
                ->selectRaw('order_items.name, SUM(order_items.quantity) as quantity, SUM(order_items.total) as revenue')
                ->whereHas('order', function (Builder $query) use ($from, $to): void {
                    $query
                        // Reuse the indexed created_at scope to keep the nested order filter efficient.
                        ->createdBetween($from, $to)
                        ->where(static function (Builder $builder): void {
                            $builder
                                ->whereIn('status', self::PAID_STATUSES)
                                ->orWhereIn('payment_status', array_merge(self::PAID_PAYMENT_STATUSES, ['refunded', 'partially_refunded']));
                        });
                })
                ->groupBy('order_items.name')
                ->orderByDesc('quantity')
                ->limit($limit)
                ->get()
                ->map(static fn ($row): array => [
                    'name'     => Arr::get($row, 'name'),
                    'quantity' => (int) Arr::get($row, 'quantity', 0),
                    'revenue'  => round((float) Arr::get($row, 'revenue', 0.0), 2),
                ])
                ->all();
        });
    }

    /**
     * @return array<int, float>
     */
    private static function revenueSparkline(CarbonInterface $from, CarbonInterface $to): array
    {
        $diffInDays = $from->diffInDays($to);
        $granularityMethod = $diffInDays > 90 ? 'perMonth' : 'perDay';

        return Trend::query(self::paidOrdersQuery($from, $to))
            ->between($from, $to)
            ->{$granularityMethod}()
            ->sum('total')
            ->map(static fn (TrendValue $value): float => round((float) $value->aggregate, 2))
            ->all();
    }

    private static function paidOrdersQuery(CarbonInterface $from, CarbonInterface $to): Builder
    {
        return Order::query()
            // Scope the base query through created_at to align with the standalone index.
            ->createdBetween($from, $to)
            ->where(static function (Builder $query): void {
                $query
                    ->whereIn('status', self::PAID_STATUSES)
                    ->orWhereIn('payment_status', array_merge(self::PAID_PAYMENT_STATUSES, ['partially_refunded', 'refunded']));
            });
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private static function previousPeriod(CarbonInterface $from, CarbonInterface $to): array
    {
        $durationInSeconds = max($from->diffInSeconds($to), 0);
        $end = CarbonImmutable::instance($from)->subSecond();
        $start = $end->subSeconds($durationInSeconds);

        return [$start, $end];
    }

    private static function normaliseRange(CarbonInterface $from, CarbonInterface $to): array
    {
        $start = CarbonImmutable::instance($from)->startOfDay();
        $end = CarbonImmutable::instance($to)->endOfDay();

        if ($start->greaterThan($end)) {
            return [$end, $start];
        }

        return [$start, $end];
    }

    private static function percentageChange(float|int $previous, float|int $current): ?float
    {
        if ((float) $previous === 0.0 && (float) $current === 0.0) {
            return 0.0;
        }

        if ((float) $previous === 0.0) {
            return null;
        }

        $change = (($current - $previous) / $previous) * 100;

        return round((float) $change, 2);
    }

    private static function cacheKey(string $prefix, CarbonInterface $from, CarbonInterface $to): string
    {
        $start = CarbonImmutable::instance($from)->format('YmdHis');
        $end = CarbonImmutable::instance($to)->format('YmdHis');

        return Str::slug("{$prefix}.{$start}.{$end}");
    }
}
