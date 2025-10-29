<?php

declare(strict_types=1);

namespace App\Support\Stats\Series;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

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

        /** @var int|string|null $productId */
        $productId = $product->getKey();

        if ($productId === null) {
            throw new RuntimeException('Daily sales requires a persisted product identifier.');
        }

        if (! is_int($productId)) {
            $productId = (int) $productId; // Normalise string identifiers produced by some database drivers.
        }

        /** @var int $productId */
        $cacheKey = CacheKeys::productSalesSeries($productId, $safeDays);

        /** @var array{labels: array<int, string>, quantities: array<int, int>, revenue: array<int, float>} $series */
        $series = Cache::remember($cacheKey, CacheKeys::TTL_FIVE_MINUTES, static function () use ($product, $safeDays): array {
            $end = CarbonImmutable::now()->endOfDay();
            $start = $end->subDays($safeDays - 1)->startOfDay();

            $revenueExpression = self::orderItemsTableHasTotalColumn()
                ? 'COALESCE(SUM(order_items.total), 0)'
                : 'COALESCE(SUM(order_items.quantity * order_items.unit_price), 0)';

            /**
             * Build the aggregate query with schema-aware revenue calculations so deployments that
             * lack the `total` column (older exports) still report accurate earnings while newer
             * installs continue using the pre-calculated totals stored on each item.
             *
             * @var array<string, array{quantity_sum: int, revenue_sum: float}> $raw
             */
            $raw = OrderItem::query()
                ->selectRaw('DATE(order_items.created_at) as day')
                ->selectRaw('SUM(order_items.quantity) as quantity_sum')
                ->selectRaw("{$revenueExpression} as revenue_sum")
                ->where('product_id', $product->getKey())
                ->whereBetween('order_items.created_at', [$start, $end])
                ->whereHas('order', static function (Builder $query): void {
                    self::applyPaidConstraints($query);
                })
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->mapWithKeys(static function (OrderItem $result): array {
                    $attributes = $result->getAttributes();
                    $day = $attributes['day'] ?? null;

                    if (! is_string($day) || $day === '') {
                        return [];
                    }

                    $quantityRaw = $attributes['quantity_sum'] ?? 0;
                    if (is_int($quantityRaw)) {
                        $quantitySum = $quantityRaw;
                    } elseif (is_float($quantityRaw)) {
                        $quantitySum = (int) round($quantityRaw);
                    } elseif (is_string($quantityRaw) && is_numeric($quantityRaw)) {
                        $quantitySum = (int) $quantityRaw;
                    } else {
                        $quantitySum = 0;
                    }

                    $revenueRaw = $attributes['revenue_sum'] ?? 0.0;
                    if (is_float($revenueRaw)) {
                        $revenueSum = $revenueRaw;
                    } elseif (is_int($revenueRaw)) {
                        $revenueSum = (float) $revenueRaw;
                    } elseif (is_string($revenueRaw) && is_numeric($revenueRaw)) {
                        $revenueSum = (float) $revenueRaw;
                    } else {
                        $revenueSum = 0.0;
                    }

                    return [
                        $day => [
                            'quantity_sum' => $quantitySum,
                            'revenue_sum'  => $revenueSum,
                        ],
                    ];
                })
                ->all();

            $labels = [];
            $quantities = [];
            $revenue = [];

            $defaultTotals = ['quantity_sum' => 0, 'revenue_sum' => 0.0];

            for ($cursor = $start; $cursor <= $end; $cursor = $cursor->addDay()) {
                $dayKey = $cursor->format('Y-m-d');
                $labels[] = $cursor->isoFormat('MMM D');

                /** @var array{quantity_sum: int, revenue_sum: float} $dayTotals */
                $dayTotals = $raw[$dayKey] ?? $defaultTotals;

                $quantities[] = $dayTotals['quantity_sum'];
                $revenue[] = round($dayTotals['revenue_sum'], 2);
            }

            return [
                'labels'     => $labels,
                'quantities' => $quantities,
                'revenue'    => $revenue,
            ];
        });

        return $series;
    }

    /**
     * Apply payment constraints so that only completed or paid orders are considered.
     *
     * @template TModel of Model
     *
     * @param Builder<TModel> $query
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

    /**
     * Check whether order items persist a `total` column before referencing it in SQL.
     */
    private static function orderItemsTableHasTotalColumn(): bool
    {
        $table = (new OrderItem)->getTable();

        if (! Schema::hasTable($table)) {
            return false;
        }

        return Schema::hasColumn($table, 'total');
    }
}
