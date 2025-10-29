@extends('components.layouts.base')

@section('title', __('frontend.discount_redemptions.create.title'))
@section('description', __('frontend.discount_redemptions.create.description'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ __('frontend.discount_redemptions.create.title') }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ __('frontend.discount_redemptions.create.description') }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('frontend.discount-redemptions.index') }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                        {{ __('frontend.discount_redemptions.actions.back_to_list') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('frontend.discount_redemptions.create.form_title') }}
                </h2>
            </div>

            <form method="POST" action="{{ route('frontend.discount-redemptions.store') }}" class="p-6">
                @csrf

                <div class="grid grid-cols-1 gap-6">
                    <!-- Discount Selection -->
                    <div>
                        <label for="discount_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('frontend.discount_redemptions.fields.discount') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="discount_id" id="discount_id" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('discount_id') border-red-500 @enderror">
                            <option value="">{{ __('frontend.discount_redemptions.create.select_discount') }}</option>
                            @foreach($availableDiscounts as $discount)
                                <option value="{{ $discount->id }}" {{ old('discount_id') == $discount->id ? 'selected' : '' }}>
                                    {{ $discount->name }} 
                                    @if($discount->type === 'percentage')
                                        ({{ $discount->value }}%)
                                    @else
                                        (€{{ number_format($discount->value, 2) }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('discount_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Discount Code Selection -->
                    <div>
                        <label for="code_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('frontend.discount_redemptions.fields.discount_code') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="code_id" id="code_id" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('code_id') border-red-500 @enderror">
                            <option value="">{{ __('frontend.discount_redemptions.create.select_code') }}</option>
                        </select>
                        @error('code_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Order Selection (Optional) -->
                    <div>
                        <label for="order_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('frontend.discount_redemptions.fields.order') }}
                        </label>
                        <select name="order_id" id="order_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('order_id') border-red-500 @enderror">
                            <option value="">{{ __('frontend.discount_redemptions.create.select_order') }}</option>
                            @foreach($userOrders as $order)
                                <option value="{{ $order->id }}" {{ old('order_id') == $order->id ? 'selected' : '' }}>
                                    {{ $order->order_number }} - {{ $order->created_at->format('d.m.Y') }}
                                </option>
                            @endforeach
                        </select>
                        @error('order_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Amount Saved -->
                        <div>
                            <label for="amount_saved" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('frontend.discount_redemptions.fields.amount_saved') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">€</span>
                                </div>
                                <input type="number" name="amount_saved" id="amount_saved" step="0.01" min="0.01" required
                                       value="{{ old('amount_saved') }}"
                                       class="w-full pl-8 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('amount_saved') border-red-500 @enderror"
                                       placeholder="0.00">
                            </div>
                            @error('amount_saved')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Currency Code -->
                        <div>
                            <label for="currency_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('frontend.discount_redemptions.fields.currency_code') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="currency_code" id="currency_code" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('currency_code') border-red-500 @enderror">
                                <option value="EUR" {{ old('currency_code', 'EUR') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="USD" {{ old('currency_code') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="GBP" {{ old('currency_code') === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                            </select>
                            @error('currency_code')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('frontend.discount_redemptions.fields.notes') }}
                        </label>
                        <textarea name="notes" id="notes" rows="4"
                                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-500 @enderror"
                                  placeholder="{{ __('frontend.discount_redemptions.create.notes_placeholder') }}">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('frontend.discount-redemptions.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition duration-200">
                        {{ __('frontend.discount_redemptions.actions.cancel') }}
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                        {{ __('frontend.discount_redemptions.actions.create') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Section -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        {{ __('frontend.discount_redemptions.create.help.title') }}
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <ul class="list-disc list-inside space-y-1">
                            <li>{{ __('frontend.discount_redemptions.create.help.tip1') }}</li>
                            <li>{{ __('frontend.discount_redemptions.create.help.tip2') }}</li>
                            <li>{{ __('frontend.discount_redemptions.create.help.tip3') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const discountSelect = document.getElementById('discount_id');
    const codeSelect = document.getElementById('code_id');

    discountSelect.addEventListener('change', function() {
        const discountId = this.value;
        
        // Clear code select
        codeSelect.innerHTML = '<option value="">{{ __('frontend.discount_redemptions.create.select_code') }}</option>';
        
        if (discountId) {
            // Fetch available codes for selected discount
            fetch(`{{ route('frontend.discount-redemptions.codes') }}?discount_id=${discountId}`)
                .then(response => response.json())
                .then(codes => {
                    codes.forEach(code => {
                        const option = document.createElement('option');
                        option.value = code.id;
                        option.textContent = code.code;
                        if (code.description_lt || code.description_en) {
                            option.textContent += ` - ${code.description_lt || code.description_en}`;
                        }
                        if (code.usage_limit) {
                            option.textContent += ` (${code.usage_count}/${code.usage_limit})`;
                        }
                        codeSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching discount codes:', error);
                });
        }
    });

    // Trigger change event if discount is pre-selected
    if (discountSelect.value) {
        discountSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection

