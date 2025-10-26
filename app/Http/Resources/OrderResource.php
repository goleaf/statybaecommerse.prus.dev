<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Order;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OrderResource
 *
 * API resource that exposes a checkout order summary for client receipt flows.
 *
 * @mixin Order
 */
final class OrderResource extends JsonResource
{
    /**
     * Transform the order into a structured JSON response for the storefront API.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $order = $this->resource;

        return [
            'id'             => $order->getKey(),
            'number'         => $order->number,
            'status'         => $order->status instanceof BackedEnum ? $order->status->value : (string) $order->status,
            'payment_status' => $order->payment_status instanceof BackedEnum ? $order->payment_status->value : (string) $order->payment_status,
            'payment_method' => $order->payment_method instanceof BackedEnum ? $order->payment_method->value : (string) $order->payment_method,
            'totals'         => [
                'subtotal' => (float) $order->subtotal,
                'tax'      => (float) $order->tax_amount,
                'shipping' => (float) $order->shipping_amount,
                'discount' => (float) $order->discount_amount,
                'total'    => (float) $order->total,
                'currency' => $order->currency,
            ],
            'items' => OrderItemResource::collection($order->items)->resolve(),
        ];
    }
}
