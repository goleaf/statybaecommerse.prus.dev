@extends('frontend.layouts.app')

@section('title', __('Redeem Discount Code'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ __('Redeem Discount Code') }}
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ __('Enter your discount code to redeem it and save money on your purchase.') }}
                </p>
            </div>

            <!-- Form -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Enter Discount Code') }}
                    </h2>
                </div>

                <form method="POST" action="{{ route('frontend.discount-redemptions.store') }}" class="p-6">
                    @csrf

                    <div class="space-y-6">
                        <!-- Discount Code Input -->
                        <div>
                            <label for="discount_code"
                                   class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Discount Code') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                       name="discount_code"
                                       id="discount_code"
                                       value="{{ old('discount_code') }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('discount_code') border-red-500 @enderror"
                                       placeholder="{{ __('Enter your discount code here') }}"
                                       required
                                       autofocus>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 6v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-6V7a2 2 0 00-2-2H5z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            @error('discount_code')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Enter the discount code exactly as it appears on your coupon or email.') }}
                            </p>
                        </div>

                        <!-- Order ID (Optional) -->
                        <div>
                            <label for="order_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Order ID') }} <span class="text-gray-500">({{ __('Optional') }})</span>
                            </label>
                            <input type="text"
                                   name="order_id"
                                   id="order_id"
                                   value="{{ old('order_id') }}"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('order_id') border-red-500 @enderror"
                                   placeholder="{{ __('Enter order ID if applicable') }}">
                            @error('order_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('If you have an existing order, enter the order ID to apply the discount.') }}
                            </p>
                        </div>

                        <!-- Information Box -->
                        <div
                             class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                              clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                        {{ __('How it works') }}
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>{{ __('Enter your discount code in the field above') }}</li>
                                            <li>{{ __('The system will validate your code and show the discount amount') }}
                                            </li>
                                            <li>{{ __('Your discount will be applied to your account') }}</li>
                                            <li>{{ __('You can use the discount on your next purchase') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                {{ __('Terms and Conditions') }}
                            </h3>
                            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                                <p>{{ __('By redeeming a discount code, you agree to the following terms:') }}</p>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li>{{ __('Discount codes are subject to availability and may have expiration dates') }}
                                    </li>
                                    <li>{{ __('Each discount code can only be used once per user unless otherwise specified') }}
                                    </li>
                                    <li>{{ __('Discount codes cannot be combined with other offers unless explicitly stated') }}
                                    </li>
                                    <li>{{ __('We reserve the right to modify or cancel discount codes at any time') }}
                                    </li>
                                    <li>{{ __('Discount codes are non-transferable and cannot be exchanged for cash') }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('frontend.discount-redemptions.index') }}"
                           class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 ease-in-out">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            {{ __('Redeem Code') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Help Section -->
            <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Need Help?') }}
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                {{ __('Common Issues') }}
                            </h3>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li>• {{ __('Make sure you enter the code exactly as shown') }}</li>
                                <li>• {{ __('Check that the code hasn\'t expired') }}</li>
                                <li>• {{ __('Ensure you haven\'t already used this code') }}</li>
                                <li>• {{ __('Verify the code is still active') }}</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                {{ __('Contact Support') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {{ __('If you\'re having trouble redeeming your code, please contact our support team.') }}
                            </p>
                            <a href="{{ route('frontend.contact') }}"
                               class="inline-flex items-center text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                {{ __('Contact Support') }}
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

