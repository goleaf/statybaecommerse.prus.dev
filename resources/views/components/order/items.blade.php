@props([
    'items',
    'currency_code',
])

@php
    $currency = (string) ($currency_code ?? 'EUR');
@endphp

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    {{-- Table header --}}
    <div class="hidden grid-cols-12 gap-4 border-b border-gray-100 bg-gray-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 sm:grid">
        <div class="col-span-6">{{ __('messages.product') }}</div>
        <div class="col-span-2 text-center">{{ __('messages.quantity') }}</div>
        <div class="col-span-2 text-right">{{ __('messages.unit_price') }}</div>
        <div class="col-span-2 text-right">{{ __('messages.total') }}</div>
    </div>

    {{-- Rows --}}
    <ul class="divide-y divide-gray-100">
        @foreach ($items as $item)
            <x-order.item :item="$item" :currency_code="$currency" />
        @endforeach
    </ul>
</div>
