<section
    class="sticky top-24 space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:top-8"
    aria-labelledby="order-summary-heading"
>
    <div class="flex items-center justify-between">
        <div>
            <h2 id="order-summary-heading" class="text-lg font-semibold text-gray-900">
                {{ __('Order summary') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ trans_choice(':count item|:count items', $cartItems->sum('quantity'), ['count' => $cartItems->sum('quantity')]) }}
            </p>
        </div>
        <span class="text-sm font-semibold text-primary-700">
            {{ $summary['formatted_total'] ?? app_money_format($summary['total'] ?? 0) }}
        </span>
    </div>

    <ul class="divide-y divide-gray-200 text-sm text-gray-700">
        @foreach ($cartItems as $item)
            @php
                /** @var \App\Models\CartItem $item */
                $snapshot = (array) $item->product_snapshot;
                $product = $item->product;
                $name = $snapshot['name'] ?? $product?->name ?? __('Unknown product');
                $sku = $snapshot['sku'] ?? $product?->sku;
                $subtotal = $item->calculateSubtotal();
                $quantity = (int) $item->quantity;
            @endphp
            <li class="flex items-start justify-between gap-4 py-4">
                <div>
                    <p class="font-medium text-gray-900">{{ $name }}</p>
                    @if ($sku)
                        <p class="text-xs text-gray-500">{{ __('SKU') }}: {{ $sku }}</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-500">
                        {{ trans_choice('Quantity: :count piece|Quantity: :count pieces', $quantity, ['count' => $quantity]) }}
                    </p>
                </div>
                <p class="text-sm font-semibold text-gray-900">
                    {{ app_money_format($subtotal, $summary['currency'] ?? current_currency()) }}
                </p>
            </li>
        @endforeach
    </ul>

    <dl class="space-y-3 text-sm text-gray-600">
        <div class="flex justify-between">
            <dt>{{ __('Subtotal') }}</dt>
            <dd class="font-medium text-gray-900">
                {{ $summary['formatted_subtotal'] ?? app_money_format($summary['subtotal'] ?? 0) }}
            </dd>
        </div>

        <div class="flex justify-between">
            <dt>{{ __('Shipping') }}</dt>
            <dd class="font-medium text-gray-900">
                {{ $summary['formatted_shipping_amount'] ?? app_money_format($summary['shipping_amount'] ?? 0) }}
            </dd>
        </div>

        <div class="flex justify-between">
            <dt>{{ __('Tax') }}</dt>
            <dd class="font-medium text-gray-900">
                {{ $summary['formatted_tax_amount'] ?? app_money_format($summary['tax_amount'] ?? 0) }}
            </dd>
        </div>

        @if (($summary['discount_amount'] ?? 0) > 0)
            <div class="flex justify-between text-green-600">
                <dt>{{ __('Discount') }}</dt>
                <dd class="font-medium">
                    −{{ $summary['formatted_discount_amount'] ?? app_money_format($summary['discount_amount'], $summary['currency'] ?? current_currency()) }}
                </dd>
            </div>
        @endif

        <div class="flex justify-between border-t border-gray-200 pt-3 text-base font-semibold text-gray-900">
            <dt>{{ __('Total') }}</dt>
            <dd>{{ $summary['formatted_total'] ?? app_money_format($summary['total'] ?? 0) }}</dd>
        </div>
    </dl>
</section>
