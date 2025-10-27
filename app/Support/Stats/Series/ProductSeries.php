<?php

declare(strict_types=1);

namespace App\Support\Stats\Series;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Provide cached sales series data for products that can be rendered as sparklines.
 */
final class ProductSeries
{
    /**
     * Lifecycle statuses that imply the order has been fully paid.
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
     * Payment status values that indicate captured funds when the column exists.
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
     * Return a cached set of labels, quantities, and revenue totals for the recent period.
     *
     * @return array{labels: array<int, string>, quantities: array<int, int>, revenue: array<int, float>}
     */
    public static function dailySales(Product $product, int $days = 14): array
    {
        $safeDays = max(1, $days);
        $cacheKey = CacheKeys::productSalesSeries($product->getKey(), $safeDays);

        return Cache::remember($cacheKey, CacheKeys::TTL_FIVE_MINUTES, static function () use ($product, $safeDays): array {
            $end = CarbonImmutable::now()->endOfDay();
            $start = $end->subDays($safeDays - 1)->startOfDay();

            /** @var Collection<string, object> $raw */
            $raw = OrderItem::query()
                ->selectRaw('DATE(order_items.created_at) as day')
                ->selectRaw('SUM(order_items.quantity) as quantity_sum')
                ->selectRaw('SUM(order_items.total) as revenue_sum')
                ->where('product_id', $product->getKey())
                ->whereBetween('order_items.created_at', [$start, $end])
                ->whereHas('order', static function (Builder $query): void {
                    self::applyPaidConstraints($query);
                })
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->keyBy(static fn (object $result): string => (string) $result->day);

            $labels = [];
            $quantities = [];
            $revenue = [];

            for ($cursor = $start; $cursor <= $end; $cursor = $cursor->addDay()) {
                $dayKey = $cursor->format('Y-m-d');
                $labels[] = $cursor->isoFormat('MMM D');
                $quantities[] = (int) ($raw[$dayKey]->quantity_sum ?? 0);
                $revenue[] = round((float) ($raw[$dayKey]->revenue_sum ?? 0.0), 2);
            }

            return [
                'labels'     => $labels,
                'quantities' => $quantities,
                'revenue'    => $revenue,
            ];
        });
    }

    /**
     * Apply payment constraints so that only completed or paid orders are considered.
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
     * Determine if the orders table tracks payment_status to broaden the query.
     */
    private static function ordersTableHasPaymentStatus(): bool
    {
        $table = (new Order)->getTable();

        if (! Schema::hasTable($table)) {
            return false;
        }

        return Schema::hasColumn($table, 'payment_status');
    }
}
