<?php

declare(strict_types=1);

namespace App\Support\Stats\Series;

use App\Models\Customer;
use App\Models\Order;
use App\Support\Cache\CacheKeys;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Provide cached order activity series for customers that power inline charts.
 */
final class CustomerSeries
{
    /**
     * Lifecycle statuses that confirm the order has been fulfilled or paid.
     *
     * @var array<int, string>
     */
    private const PAID_STATUSES = [
        'processing',
        'confirmed',
        'shipped',
        'delivered',
        'completed',
    ];

    /**
     * Payment status values that indicate payment confirmation when available.
     *
     * @var array<int, string>
     */
    private const PAID_PAYMENT_STATUSES = [
        'paid',
        'captured',
        'settled',
        'authorized',
    ];

    /**
     * Cache flag for the optional payment_status column to avoid repeated schema checks.
     */
    private static ?bool $ordersTableHasPaymentStatus = null;

    /**
     * Build a cached snapshot of recent order counts and revenue totals for the customer.
     *
     * @return array{labels: array<int, string>, orders: array<int, int>, revenue: array<int, float>}
     */
    public static function dailyOrders(Customer $customer, int $days = 14): array
    {
        $safeDays = max(1, $days);
        $cacheKey = CacheKeys::customerOrdersSeries($customer->getKey(), $safeDays);

        return Cache::remember($cacheKey, CacheKeys::TTL_FIVE_MINUTES, static function () use ($customer, $safeDays): array {
            $end = CarbonImmutable::now()->endOfDay();
            $start = $end->subDays($safeDays - 1)->startOfDay();

            /** @var Collection<string, object> $raw */
            $raw = Order::query()
                ->selectRaw('DATE(created_at) as day')
                ->selectRaw('COUNT(*) as orders_sum')
                ->selectRaw('SUM(total) as revenue_sum')
                ->where('customer_id', $customer->getKey())
                ->whereBetween('created_at', [$start, $end])
                ->where(static function (Builder $query): void {
                    self::applyPaidConstraints($query);
                })
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->keyBy(static fn (object $result): string => (string) $result->day);

            $labels = [];
            $orders = [];
            $revenue = [];

            for ($cursor = $start; $cursor <= $end; $cursor = $cursor->addDay()) {
                $dayKey = $cursor->format('Y-m-d');
                $labels[] = $cursor->isoFormat('MMM D');
                $orders[] = (int) ($raw[$dayKey]->orders_sum ?? 0);
                $revenue[] = round((float) ($raw[$dayKey]->revenue_sum ?? 0.0), 2);
            }

            return [
                'labels'  => $labels,
                'orders'  => $orders,
                'revenue' => $revenue,
            ];
        });
    }

    /**
     * Limit queries to orders with paid lifecycle or payment statuses.
     */
    private static function applyPaidConstraints(Builder $query): void
    {
        $query->where(static function (Builder $builder): void {
            $builder->whereIn('status', self::PAID_STATUSES);

            if (self::ordersTableHasPaymentStatus()) {
                $builder->orWhereIn('payment_status', self::PAID_PAYMENT_STATUSES);
            }
        });
    }

    /**
     * Detect whether the payment_status column exists on the orders table.
     */
    private static function ordersTableHasPaymentStatus(): bool
    {
        if (self::$ordersTableHasPaymentStatus !== null) {
            return self::$ordersTableHasPaymentStatus;
        }

        self::$ordersTableHasPaymentStatus = Schema::hasColumn((new Order)->getTable(), 'payment_status');

        return self::$ordersTableHasPaymentStatus;
    }
}
