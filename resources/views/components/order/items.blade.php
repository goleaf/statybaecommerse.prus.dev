@props([
    'items',
    'currency_code',
])

@php($currency = (string) ($currency_code ?? 'EUR'))

<div class="space-y-4">
    @foreach($items as $item)
        @php($unitPrice = (float) ($item->unit_price_amount ?? $item->unit_price ?? $item->price ?? 0))
        <div class="relative flex gap-3 rounded-lg border border-gray-200 p-3">
            <x-product.thumbnail :product="$item->product" class="size-28" />
            <div class="flex-1 space-y-0.5">
                <h4 class="font-heading text-sm font-medium leading-5 text-gray-900">
                    {{ $item->name }}
                </h4>
                <p class="text-sm text-gray-700">
                    <span class="text-gray-500">{{ __('ui.unit_price') }}</span> : {{ \Illuminate\Support\Number::currency($unitPrice, $currency, app()->getLocale()) }}
                </p>
                <p class="text-sm text-gray-700">
                    <span class="text-gray-500">{{ __('messages.quantity') }}</span> : {{ $item->quantity }}
                </p>
            </div>
        </div>
    @endforeach
</div>
