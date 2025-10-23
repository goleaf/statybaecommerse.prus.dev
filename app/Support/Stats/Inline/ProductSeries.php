<?php

declare(strict_types=1);

namespace App\Support\Stats\Inline;

use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class ProductSeries
{
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Build a 30 day quantity series for the given product.
     *
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public static function last30Days(int $productId): array
    {
        $cacheKey = "inline:product:{$productId}:sales:30d";

        return Cache::remember($cacheKey, self::CACHE_TTL, static function () use ($productId): array {
            $end = Carbon::now()->startOfDay();
            $start = $end->copy()->subDays(29);

            $dailyTotals = OrderItem::query()
                ->selectRaw('DATE(created_at) as day, SUM(quantity) as qty')
                ->where('product_id', $productId)
                ->whereBetween('created_at', [$start, $end->copy()->endOfDay()])
                ->groupBy('day')
                ->orderBy('day')
                ->pluck('qty', 'day');

            $labels = [];
            $values = [];

            for ($offset = 0; $offset < 30; $offset++) {
                $currentDate = $start->copy()->addDays($offset);
                $key = $currentDate->toDateString();

                $labels[] = $currentDate->format('M j');
                $values[] = (int) ($dailyTotals[$key] ?? 0);
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        });
    }
}
