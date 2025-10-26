<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OrderResource
 *
 * JSON resource that normalises order payloads for the lifecycle API endpoints.
 */
final class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $status = $this->resolveOrderStatus();
        $paymentStatus = $this->resolvePaymentStatus();

        return [
            'id'             => $this->resource->getKey(),
            'number'         => (string) $this->resource->number,
            'status'         => $status,
            'payment_status' => $paymentStatus,
            'currency'       => $this->resource->currency,
            'totals'         => [
                // Cast numeric totals to float so client applications do not have to coerce stringified decimals.
                'subtotal'    => (float) $this->resource->subtotal,
                'tax'         => (float) $this->resource->tax_amount,
                'shipping'    => (float) $this->resource->shipping_amount,
                'discount'    => (float) $this->resource->discount_amount,
                'grand_total' => (float) $this->resource->total,
            ],
            'lines'            => $this->formatLines(),
            'payments'         => $this->formatPayments(),
            'shipments'        => $this->formatShipments(),
            'billing_address'  => $this->formatAddress($this->resource->billing_address),
            'shipping_address' => $this->formatAddress($this->resource->shipping_address),
            'notes'            => $this->resource->notes,
            'placed_at'        => optional($this->resource->created_at)->toISOString(),
            'updated_at'       => optional($this->resource->updated_at)->toISOString(),
        ];
    }

    /**
     * Normalise the order lines collection into a predictable payload.
     *
     * @return array<int, array<string, mixed>>
     */
    private function formatLines(): array
    {
        if (! $this->resource->relationLoaded('items')) {
            return [];
        }

        return $this->resource->items
            ->map(static function (OrderItem $item): array {
                return [
                    'id'         => $item->getKey(),
                    'product_id' => $item->product_id,
                    'name'       => (string) $item->name,
                    'sku'        => $item->sku,
                    'quantity'   => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total'      => (float) $item->total,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Present payment transaction snapshots as a dedicated collection.
     *
     * @return array<int, array<string, mixed>>
     */
    private function formatPayments(): array
    {
        $transactions = is_array($this->resource->transactions) ? $this->resource->transactions : [];

        return collect($transactions)
            ->map(function (array $transaction): array {
                return [
                    'reference'    => (string) ($transaction['reference'] ?? ''),
                    'status'       => (string) ($transaction['status'] ?? ($this->resolvePaymentStatus()['value'] ?? 'pending')),
                    'amount'       => (float) ($transaction['amount'] ?? 0.0),
                    'currency'     => (string) ($transaction['currency'] ?? $this->resource->currency),
                    'type'         => (string) ($transaction['type'] ?? 'payment'),
                    'processed_at' => $transaction['processed_at'] ?? null,
                    'raw'          => $transaction,
                ];
            })
            ->all();
    }

    /**
     * Normalise shipment information even though the relationship is currently a single record.
     *
     * @return array<int, array<string, mixed>>
     */
    private function formatShipments(): array
    {
        if (! $this->resource->relationLoaded('shipping')) {
            return [];
        }

        $shipping = $this->resource->shipping;

        if (! $shipping instanceof OrderShipping) {
            return [];
        }

        return [[
            'id'              => $shipping->getKey(),
            'carrier'         => $shipping->carrier_name ?? $shipping->carrier,
            'method'          => $shipping->shipping_method,
            'tracking_number' => $shipping->tracking_number,
            'tracking_url'    => $shipping->tracking_url,
            'status'          => $shipping->status,
            'shipped_at'      => optional($shipping->shipped_at)->toISOString(),
            'delivered_at'    => optional($shipping->delivered_at)->toISOString(),
            'cost'            => $shipping->total_cost !== null ? (float) $shipping->total_cost : null,
        ]];
    }

    /**
     * Ensure addresses always resolve to an array so the API response is predictable.
     */
    private function formatAddress(mixed $address): array
    {
        if (! is_array($address)) {
            return [];
        }

        // Filter out null values while retaining falsy-but-meaningful entries like "0" postal codes.
        return collect($address)
            ->reject(static fn ($value): bool => $value === null)
            ->all();
    }

    /**
     * Resolve the order status metadata (value + label pair).
     *
     * @return array{value: string, label: string}
     */
    private function resolveOrderStatus(): array
    {
        $status = $this->resource->status instanceof OrderStatus
            ? $this->resource->status
            : OrderStatus::tryFrom((string) $this->resource->status);

        if (! $status instanceof OrderStatus) {
            $raw = (string) $this->resource->status;

            return ['value' => $raw, 'label' => ucfirst($raw)];
        }

        return ['value' => $status->value, 'label' => $status->label()];
    }

    /**
     * Resolve the payment status metadata just like the order status helper.
     *
     * @return array{value: string, label: string}
     */
    private function resolvePaymentStatus(): array
    {
        $status = $this->resource->payment_status instanceof PaymentStatus
            ? $this->resource->payment_status
            : PaymentStatus::tryFrom((string) $this->resource->payment_status);

        if (! $status instanceof PaymentStatus) {
            $raw = (string) $this->resource->payment_status;

            return ['value' => $raw, 'label' => ucfirst($raw ?: 'pending')];
        }

        // Use the enum value directly for the label to avoid translation coupling for now.
        return ['value' => $status->value, 'label' => ucfirst(str_replace('_', ' ', $status->value))];
    }
}
