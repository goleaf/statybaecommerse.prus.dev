@props(['order'])

@php
    $currency = (string) ($order->currency ?? 'EUR');
    $total = (float) ($order->total ?? 0) + (float) ($order->shippingOption?->price ?? 0);
    $itemsCount = $order->items->count();
    $invoice = $order->currentInvoice;
    $invoiceStatus = $invoice?->status;
    $invoiceStatusLabel = null;

    if ($invoiceStatus !== null) {
        $statusKey = 'messages.invoice_status_' . \Illuminate\Support\Str::snake((string) $invoiceStatus);
        $invoiceStatusLabel = __($statusKey);

        if ($invoiceStatusLabel === $statusKey) {
            $invoiceStatusLabel = \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $invoiceStatus));
        }
    }
@endphp

<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <div class="text-sm">
            <dt class="font-medium text-gray-700">
                {{ __('frontend.account.order_detail.order_number_label') }}
            </dt>
            <dd class="mt-1 font-semibold uppercase text-gray-900">
                {{ $order->number }}
            </dd>
        </div>
        <div class="text-sm">
            <dt class="font-medium text-gray-700">
                {{ __('frontend.account.order_detail.placed_on') }}
            </dt>
            <dd class="mt-1 text-gray-600 capitalize">
                <time datetime="{{ format_datetime($order->created_at) }}">
                    {{ format_datetime($order->created_at) }}
                </time>
            </dd>
        </div>
        <div class="text-sm">
            <dt class="font-medium text-gray-700">
                {{ __('messages.total') }}
            </dt>
            <dd class="mt-1 text-base font-semibold text-gray-900">
                {{ \Illuminate\Support\Number::currency($total, $currency, app()->getLocale()) }}
            </dd>
        </div>
        <div class="text-sm">
            <dt class="font-medium text-gray-700">{{ __('messages.status') }}</dt>
            <dd class="mt-1 text-gray-500">
                <x-order.status :status="$order->status" />
            </dd>
        </div>
        <div class="text-sm">
            <dt class="font-medium text-gray-700">{{ __('messages.quantity') }}</dt>
            <dd class="mt-1 text-gray-600">{{ $itemsCount }}</dd>
        </div>
        <div class="text-sm">
            <dt class="font-medium text-gray-700">{{ __('frontend.account.documents_table.title') }}</dt>
            <dd class="mt-1 text-gray-600">
                @if ($invoiceStatusLabel !== null)
                    <span class="inline-flex items-center rounded bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700">
                        {{ $invoiceStatusLabel }}
                    </span>
                @else
                    <span class="text-gray-500">{{ __('frontend.order_summary.not_available') }}</span>
                @endif
            </dd>
        </div>
    </div>
    <div class="mt-5 flex flex-wrap gap-2">
        <x-buttons.default :href="route('account.orders.detail', ['number' => $order->number])">
            {{ __('ui.view_details') }}
        </x-buttons.default>
    </div>
</div>
