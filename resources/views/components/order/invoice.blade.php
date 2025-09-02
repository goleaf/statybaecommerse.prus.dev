@props(['order'])

<div class="max-w-2xl mx-auto p-6 text-sm text-gray-900">
    <h1 class="text-xl font-semibold mb-4">{{ __('Invoice') }} #{{ $order->number }}</h1>
    <p class="mb-2">{{ __('Date') }}: {{ format_datetime($order->created_at) }}</p>
    <p class="mb-6">{{ __('Total') }}: {{ format_money($order->grand_total_amount, $order->currency_code) }}</p>

    <table class="w-full text-left border-t border-b border-gray-200">
        <thead>
            <tr>
                <th class="py-2">{{ __('Product') }}</th>
                <th class="py-2">{{ __('Qty') }}</th>
                <th class="py-2 text-right">{{ __('Price') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr class="border-t border-gray-100">
                    <td class="py-1">{{ $item->product?->trans('name') ?? $item->product_name }}</td>
                    <td class="py-1">{{ $item->quantity }}</td>
                    <td class="py-1 text-right">{{ format_money($item->total, $order->currency_code) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
