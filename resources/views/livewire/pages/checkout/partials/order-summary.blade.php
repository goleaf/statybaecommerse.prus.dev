@php
    $orderSummaryItems = $cartItems->map(static function (\App\Models\CartItem $item) use ($summary): array {
        $snapshot = (array) $item->product_snapshot;
        $product = $item->product;
        $name = $snapshot['name'] ?? $product?->name ?? __('ui.unknown_product');
        $lineTotal = $item->calculateSubtotal();
        $currency = (string) ($summary['currency'] ?? current_currency());

        return [
            'name'                 => $name,
            'quantity'             => (int) $item->quantity,
            'line_total'           => (float) $lineTotal,
            'formatted_line_total' => app_money_format($lineTotal, $currency),
        ];
    });
@endphp

<x-order.right-panel
    :items="$orderSummaryItems"
    :summary="$summary"
    :item-count="$cartItems->sum('quantity')"
    :show-coupon="false"
    summary-panel-class="sticky top-24 lg:top-8"
/>
