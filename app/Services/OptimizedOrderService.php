<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Optimized order service to reduce N+1 queries and improve performance
 */
final class OptimizedOrderService
{
    /**
     * Get orders with optimized eager loading to prevent N+1 queries
     */
    public function getOrdersWithRelations(array $orderIds = []): Collection
    {
        $query = Order::query()
            ->select([
                'id', 'number', 'user_id', 'status', 'total',
                'currency', 'created_at', 'updated_at',
            ])
            ->with([
                'user:id,name,email',
                'items' => function ($query) {
                    $query->select([
                        'id', 'order_id', 'product_id', 'name',
                        'quantity', 'unit_price', 'total',
                    ]);
                },
                'items.product:id,name,slug,sku',
                'shipping:id,order_id,carrier_name,cost,tracking_number',
            ]);

        if (! empty($orderIds)) {
            $query->whereIn('id', $orderIds);
        }

        return $query->get();
    }

    /**
     * Batch create orders with optimized queries
     */
    public function batchCreateOrders(array $ordersData): Collection
    {
        return DB::transaction(function () use ($ordersData) {
            // Pre-fetch all required entities in single queries
            $userIds = collect($ordersData)->pluck('user_id')->unique();
            $users = \App\Models\User::whereIn('id', $userIds)->get()->keyBy('id');

            $productIds = collect($ordersData)
                ->flatMap(fn ($order) => collect($order['items'])->pluck('product_id'))
                ->unique();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $orders = collect();

            foreach ($ordersData as $orderData) {
                $order = Order::create([
                    'user_id'    => $orderData['user_id'],
                    'status'     => $orderData['status'],
                    'total'      => $orderData['total'],
                    'currency'   => $orderData['currency'] ?? 'EUR',
                    'tax_amount' => $orderData['tax_amount'] ?? 0.0, // Use new 0% tax rate
                ]);

                // Batch create order items
                $itemsData = collect($orderData['items'])->map(function ($item) use ($order, $products) {
                    $product = $products->get($item['product_id']);

                    return [
                        'order_id'   => $order->id,
                        'product_id' => $item['product_id'],
                        'name'       => $product?->name ?? $item['name'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total'      => $item['quantity'] * $item['unit_price'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                });

                \App\Models\OrderItem::insert($itemsData->toArray());
                $orders->push($order);
            }

            return $orders;
        });
    }

    /**
     * Get order analytics with cached results
     */
    public function getOrderAnalytics(string $period = 'month'): array
    {
        $cacheKey = "order_analytics_{$period}";

        return Cache::remember($cacheKey, 1800, function () use ($period) { // 30 min cache
            $startDate = match ($period) {
                'week'  => now()->subWeek(),
                'month' => now()->subMonth(),
                'year'  => now()->subYear(),
                default => now()->subMonth(),
            };

            return [
                'total_orders'        => Order::createdSince($startDate)->count(),
                'total_revenue'       => Order::createdSince($startDate)->sum('total'),
                'average_order_value' => Order::createdSince($startDate)->avg('total'),
                'orders_by_status'    => Order::createdSince($startDate)
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray(),
            ];
        });
    }
}
