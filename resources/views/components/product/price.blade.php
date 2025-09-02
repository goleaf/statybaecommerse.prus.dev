@props(['product'])

@php
    $price = $product->getPrice();
@endphp

<p {{ $attributes->merge(['class' => 'inline-flex flex-col gap-0.5 text-sm']) }}>
    <span class="font-semibold text-primary-600 lining-nums slashed-zero">
        {{ $price ? shopper_money_format(amount: $price->value->amount, currency: current_currency()) : '' }}
    </span>

    @if ($price && $price->percentage && $price->percentage > 0)
        <span>
            <span class="sr-only">{{ __('Original :') }}</span>
            <span class="text-gray-400 font-normal text-[13px] line-through">
                {{ shopper_money_format(amount: $price->compare->amount, currency: current_currency()) }}
            </span>
            <x-discount-badge
                              :discount="$price->percentage"
                              class="ml-2" />
        </span>
    @endif
</p>
