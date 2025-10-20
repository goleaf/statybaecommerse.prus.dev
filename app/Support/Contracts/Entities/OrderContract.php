<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\Order;
use App\Models\OrderItem;
use function collect;

final class OrderContract
{
    public static function fromModel(Order $order): array
    {
        $order->loadMissing(['items.product', 'user.roles', 'shipping']);

        return [
            'id' => (int) $order->getKey(),
            'number' => (string) $order->number,
            'status' => (string) $order->status,
            'items' => $order->items->map(static fn (OrderItem $item): array => self::mapItem($item))->values()->toArray(),
            'totals' => [
                'subtotal' => (float) ($order->subtotal ?? 0),
                'tax' => (float) ($order->tax_amount ?? 0),
                'shipping' => (float) ($order->shipping_amount ?? 0),
                'discount' => (float) ($order->discount_amount ?? 0),
                'total' => (float) ($order->total ?? 0),
                'currency' => strtoupper((string) ($order->currency ?? config('shared.default_currency', config('app.currency', 'EUR')))),
            ],
            'customer' => self::mapCustomer($order),
            'payments' => self::mapPayments($order),
            'shipments' => self::mapShipments($order),
        ];
    }

    private static function mapItem(OrderItem $item): array
    {
        $product = $item->product;
        $productId = $product?->getKey() ?? $item->product_id;

        return [
            'id' => (int) $item->getKey(),
            'product' => [
                'id' => $productId ? (int) $productId : 0,
                'sku' => (string) ($product?->sku ?? $item->sku ?? ''),
                'title' => (string) ($product?->name ?? $item->name ?? ''),
            ],
            'quantity' => (int) ($item->quantity ?? 1),
            'unit_price' => (float) ($item->unit_price ?? $item->price ?? 0),
            'total' => (float) ($item->total ?? 0),
            'status' => (string) ($item->status ?? 'fulfilled'),
        ];
    }

    private static function mapCustomer(Order $order): array
    {
        $user = $order->user;
        if (! $user) {
            return [
                'id' => 0,
                'email' => '',
                'name' => '',
                'meta' => [],
            ];
        }

        $contract = UserContract::fromModel($user);
        $meta = $contract['meta'];
        unset($contract['meta']);

        return $contract + ['meta' => $meta];
    }

    private static function mapPayments(Order $order): array
    {
        return collect([[
            'status' => (string) ($order->payment_status ?? 'pending'),
            'amount' => (float) ($order->total ?? 0),
            'method' => $order->payment_method,
            'reference' => $order->payment_reference,
            'processed_at' => optional($order->updated_at)->toISOString(),
        ]])->filter(static fn (array $payment): bool => $payment['status'] !== '' || $payment['method'] !== null)->values()->toArray();
    }

    private static function mapShipments(Order $order): array
    {
        if (! $order->relationLoaded('shipping') && $order->shipping === null) {
            return [];
        }

        $shipping = $order->shipping;
        if (! $shipping) {
            return [];
        }

        return [[
            'status' => (string) $shipping->status,
            'carrier' => $shipping->carrier_name,
            'service' => $shipping->service,
            'tracking_number' => $shipping->tracking_number,
            'tracking_url' => $shipping->tracking_url,
            'shipped_at' => optional($shipping->shipped_at)->toISOString(),
            'delivered_at' => optional($shipping->delivered_at)->toISOString(),
        ]];
    }
}
