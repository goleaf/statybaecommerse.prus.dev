<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Data\Pricing\PriceBreakdown;
use App\Models\Order;
use App\Services\Pricing\PriceConfiguration;
use App\Support\Contracts\ContractPathResolver;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class OrderContract
{
    public const CONTRACT = 'order';

    public const VERSION = 'v1';

    public static function schemaPath(): string
    {
        return ContractPathResolver::schema('order.schema.json');
    }

    public static function examplePath(): string
    {
        return ContractPathResolver::example('order.json');
    }

    public static function forOrder(Order $order, array $meta = []): array
    {
        $orderPayload = self::mapOrder($order);

        return self::envelope([
            'order' => $orderPayload,
            'item' => $orderPayload,
        ], $meta);
    }

    public static function forCollection(iterable $orders, array $meta = []): array
    {
        $items = Collection::make($orders)
            ->map(fn (Order $order): array => self::mapOrder($order))
            ->values()
            ->all();

        return self::envelope([
            'orders' => $items,
            'items' => $items,
        ], $meta + ['total' => count($items)]);
    }

    private static function mapOrder(Order $order): array
    {
        $order->loadMissing(['items']);

        $configuration = app(PriceConfiguration::class);
        $breakdown = PriceBreakdown::fromOrder($order, $configuration);

        $status = $order->status;
        $paymentStatus = $order->payment_status;

        return [
            'id'     => $order->getKey(),
            'number' => (string) $order->number,
            'status' => [
                'state'         => $status instanceof \BackedEnum ? $status->value : (string) $status,
                'payment_state' => $paymentStatus instanceof \BackedEnum ? $paymentStatus->value : $paymentStatus,
            ],
            'totals' => $breakdown->toContractTotals(),
            'items'  => $order->items->map(static fn ($item): array => [
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
                'self' => route('api.orders.show', ['order' => $order->number]),
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
