    <x-container class="py-10">
        <h1 class="text-2xl font-semibold mb-6">{{ __('Preview Discount') }} #{{ $discount->id }}</h1>

        <form wire:submit.prevent="compute" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Currency') }}</label>
                <select wire:model="currency_code" class="w-full border-gray-300">
                    @foreach ($currencies as $code)
                        <option value="{{ $code }}">{{ $code }}</option>
                    @endforeach
                </select>
                @error('currency_code')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Zone') }}</label>
                <select wire:model="zone_id" class="w-full border-gray-300">
                    <option value="">—</option>
                    @foreach ($zones as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Subtotal') }}</label>
                <input type="number" step="0.01" wire:model="subtotal" class="w-full border-gray-300" />
                @error('subtotal')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Code (optional)') }}</label>
                <input type="text" wire:model="code" class="w-full border-gray-300" />
            </div>
            <div>
                <label
                       class="block text-sm font-medium mb-1">{{ __('Items CSV (product_id:qty:unit_price,...)') }}</label>
                <input type="text" wire:model="items" class="w-full border-gray-300" />
            </div>
            <div class="md:col-span-5 flex items-end">
                <button class="px-4 py-2 bg-primary-600 text-white rounded"
                        wire:loading.attr="disabled">{{ __('Compute') }}</button>
            </div>
        </form>

        @if ($result)
            <div class="space-y-3">
                <div>{{ __('Discount total') }}:
                    <strong>{{ shopper_money_format(amount: $result['discount_total_amount'] ?? 0, currency: $currency_code) }}</strong>
                </div>
                @if (!empty($result['line_discounts']))
                    <div>
                        <div class="font-medium mb-1">{{ __('Line discounts') }}</div>
                        <ul class="text-sm">
                            @foreach ($result['line_discounts'] as $row)
                                <li>Item #{{ $row['item_index'] }}:
                                    -{{ shopper_money_format(amount: $row['amount'], currency: $currency_code) }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (!empty($result['cart_discounts']))
                    <div>
                        <div class="font-medium mb-1">{{ __('Cart discounts') }}</div>
                        <ul class="text-sm">
                            @foreach ($result['cart_discounts'] as $row)
                                <li>-{{ shopper_money_format(amount: $row['amount'], currency: $currency_code) }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (!empty($result['shipping']['discount_amount']))
                    <div>{{ __('Shipping discount') }}:
                        -{{ shopper_money_format(amount: $result['shipping']['discount_amount'], currency: $currency_code) }}
                    </div>
                @endif
            </div>
        @endif
    </x-container>
