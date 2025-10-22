<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class OrderContract
{
    public const CONTRACT = 'order';

    public const VERSION = 'v1';

    public static function schemaPath(): string
    {
        return resource_path('contracts/v1/order.schema.json');
    }

    public static function examplePath(): string
    {
        return resource_path('contracts/v1/examples/order.json');
    }

    public static function forOrder(Order $order, array $meta = []): array
    {
        return self::envelope([
            'item' => self::mapOrder($order),
        ], $meta);
    }

    public static function forCollection(iterable $orders, array $meta = []): array
    {
        $items = Collection::make($orders)->map(fn (Order $order): array => self::mapOrder($order))->values()->all();

        return self::envelope([
            'items' => $items,
        ], $meta + ['total' => count($items)]);
    }

    private static function mapOrder(Order $order): array
    {
        $order->loadMissing(['items']);

        return [
            'id'     => $order->getKey(),
            'number' => (string) $order->number,
            'status' => [
                'state'         => (string) $order->status,
                'payment_state' => $order->payment_status,
            ],
            'totals' => [
                // Stick to the contract schema by publishing the core monetary
                // aggregates without the richer presentation extras that the pricing
                // service exposes internally.
                'subtotal' => (float) $order->subtotal,
                'tax'      => (float) $order->tax_amount,
                'shipping' => (float) $order->shipping_amount,
                'discount' => (float) $order->discount_amount,
                'total'    => (float) $order->total,
                'currency' => (string) ($order->currency ?? config('app.currency', 'EUR')),
            ],
            'items' => $order->items->map(static fn ($item): array => [
                'id'         => $item->getKey(),
                'product_id' => $item->product_id,
                'name'       => (string) $item->name,
                'quantity'   => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total'      => (float) $item->total,
            ])->all(),
            'billing_address'  => is_array($order->billing_address) ? $order->billing_address : [],
            'shipping_address' => is_array($order->shipping_address) ? $order->shipping_address : [],
            'placed_at'        => $order->created_at?->toISOString(),
            'links'            => [
                'self' => route('frontend.orders.show', $order->number),
            ],
        ];
    }

    private static function envelope(array $data, array $meta = []): array
    {
        $meta = array_merge([
            'generated_at' => now()->toISOString(),
        ], Arr::whereNotNull($meta));

        return [
            'contract' => self::CONTRACT,
            'version'  => self::VERSION,
            'data'     => $data,
            'meta'     => $meta,
        ];
    }
}
