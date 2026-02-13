<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Data\Orders\CreateOrderData;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Pricing\PriceCalculator;
use Illuminate\Support\Str;

/**
 * Single responsibility action for creating orders
 */
final readonly class CreateOrderAction
{
    public function __construct(
        private PriceCalculator $priceCalculator
    ) {}

    public function execute(CreateOrderData $orderData): Order
    {
        // Calculate totals
        $breakdown = $this->priceCalculator->breakdown(
            $orderData->subtotal,
            $orderData->discountTotal ?? 0,
            $orderData->shippingTotal ?? 0
        );

        // Create order
        $order = Order::create([
            'number'                => $this->generateOrderNumber(),
            'customer_id'           => $orderData->customerId,
            'currency_code'         => 'EUR',
            'shipping_address_id'   => $orderData->shippingAddressId,
            'billing_address_id'    => $orderData->billingAddressId,
            'shipping_option_id'    => $orderData->shippingOptionId,
            'payment_method_id'     => $orderData->paymentMethodId,
            'payment_method'        => $orderData->paymentMethod,
            'subtotal_amount'       => round($breakdown->subtotal, 2),
            'discount_total_amount' => round($breakdown->discount, 2),
            'tax_total_amount'      => round($breakdown->tax, 2),
            'shipping_total_amount' => round($breakdown->shipping, 2),
            'grand_total_amount'    => round($breakdown->total, 2),
            'status'                => 'pending',
            'notes'                 => $orderData->notes,
        ]);

        // Create order items
        foreach ($orderData->items as $itemData) {
            OrderItem::create([
                'order_id'          => $order->id,
                'product_id'        => $itemData->productId,
                'product_type'      => $itemData->productType ?? 'App\\Models\\Product',
                'quantity'          => $itemData->quantity,
                'unit_price_amount' => $itemData->unitPrice,
                'name'              => $itemData->name,
                'sku'               => $itemData->sku,
            ]);
        }

        return $order->fresh(['items', 'customer', 'shippingAddress', 'billingAddress']);
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('number', $number)->exists());

        return $number;
    }
}
