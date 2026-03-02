@props([
    'items' => [],
    'summary' => [],
    'itemCount' => null,
    'showTotalBadge' => true,
])

@php
    $normalizedItems = collect($items);
    $resolvedItemCount = is_numeric($itemCount)
        ? (int) $itemCount
        : (int) $normalizedItems->sum(static fn ($item): int => (int) data_get($item, 'quantity', 0));

    $currency = (string) ($summary['currency'] ?? current_currency());
    $formattedSubtotal = (string) ($summary['formatted_subtotal'] ?? app_money_format((float) ($summary['subtotal'] ?? 0), $currency));
    $formattedShipping = (string) ($summary['formatted_shipping_amount'] ?? app_money_format((float) ($summary['shipping_amount'] ?? 0), $currency));
    $formattedTax = (string) ($summary['formatted_tax_amount'] ?? app_money_format((float) ($summary['tax_amount'] ?? 0), $currency));
    $formattedDiscount = (string) ($summary['formatted_discount_amount'] ?? app_money_format((float) ($summary['discount_amount'] ?? 0), $currency));
    $formattedTotal = (string) ($summary['formatted_total'] ?? app_money_format((float) ($summary['total'] ?? 0), $currency));
@endphp

<section
    {{ $attributes->class(['space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm']) }}
    aria-labelledby="order-summary-heading"
>
    <div class="flex items-center justify-between">
        <div>
            <h2 id="order-summary-heading" class="text-lg font-semibold text-gray-900">
                {{ __('ui.order_summary') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ trans_choice('messages.checkout_items_count', $resolvedItemCount, ['count' => $resolvedItemCount]) }}
            </p>
        </div>

        @if ($showTotalBadge)
            <span class="text-sm font-semibold text-primary-700">{{ $formattedTotal }}</span>
        @endif
    </div>

    <ul class="divide-y divide-gray-200 text-sm text-gray-700">
        @foreach ($normalizedItems as $item)
            @php
                $quantity = (int) data_get($item, 'quantity', 0);
                $lineTotal = (float) data_get($item, 'line_total', 0);
                $formattedLineTotal = (string) data_get($item, 'formatted_line_total', app_money_format($lineTotal, $currency));
            @endphp
            <li class="flex items-start justify-between gap-4 py-4">
                <div>
                    <p class="font-medium text-gray-900">{{ (string) data_get($item, 'name', __('ui.unknown_product')) }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ trans_choice('messages.quantity_pieces', $quantity, ['count' => $quantity]) }}
                    </p>
                </div>
                <p class="text-sm font-semibold text-gray-900">{{ $formattedLineTotal }}</p>
            </li>
        @endforeach
    </ul>

    <dl class="space-y-3 text-sm text-gray-600">
        <div class="flex justify-between">
            <dt>{{ __('messages.subtotal') }}</dt>
            <dd class="font-medium text-gray-900">{{ $formattedSubtotal }}</dd>
        </div>

        <div class="flex justify-between">
            <dt>{{ __('messages.shipping') }}</dt>
            <dd class="font-medium text-gray-900">{{ $formattedShipping }}</dd>
        </div>

        <div class="flex justify-between">
            <dt>{{ __('ui.tax') }}</dt>
            <dd class="font-medium text-gray-900">{{ $formattedTax }}</dd>
        </div>

        @if ((float) ($summary['discount_amount'] ?? 0) > 0)
            <div class="flex justify-between text-green-600">
                <dt>{{ __('messages.discount') }}</dt>
                <dd class="font-medium">−{{ $formattedDiscount }}</dd>
            </div>
        @endif

        <div class="flex justify-between border-t border-gray-200 pt-3 text-base font-semibold text-gray-900">
            <dt>{{ __('messages.total') }}</dt>
            <dd>{{ $formattedTotal }}</dd>
        </div>
    </dl>
</section>
