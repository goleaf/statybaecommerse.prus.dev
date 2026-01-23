@extends('frontend.layouts.app')

@section('title', __('frontend.discount_redemptions.redeem_title'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ __('frontend.discount_redemptions.redeem_title') }}
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ __('frontend.discount_redemptions.redeem_subtitle') }}
                </p>
            </div>

            <!-- Form -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('frontend.discount_redemptions.form_title') }}
                    </h2>
                </div>

                <form method="POST" action="{{ route('frontend.discount-redemptions.store') }}" class="p-6">
                    @csrf

                    <div class="space-y-6">
                        <!-- Discount Code Input -->
                        <div>
                            <label for="discount_code"
                                   class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('frontend.discount_redemptions.fields.code') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                       name="discount_code"
                                       id="discount_code"
                                       value="{{ old('discount_code') }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('discount_code') border-red-500 @enderror"
                                       placeholder="{{ __('frontend.discount_redemptions.placeholders.code') }}"
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
                                {{ __('frontend.discount_redemptions.help.code') }}
                            </p>
                        </div>

                        <!-- Order ID (Optional) -->
                        <div>
                            <label for="order_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('frontend.discount_redemptions.fields.order_id') }} <span class="text-gray-500">({{ __('frontend.discount_redemptions.optional') }})</span>
                            </label>
                            <input type="text"
                                   name="order_id"
                                   id="order_id"
                                   value="{{ old('order_id') }}"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('order_id') border-red-500 @enderror"
                                   placeholder="{{ __('frontend.discount_redemptions.placeholders.order_id') }}">
                            @error('order_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('frontend.discount_redemptions.help.order_id') }}
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
                                        {{ __('frontend.discount_redemptions.how_it_works.title') }}
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>{{ __('frontend.discount_redemptions.how_it_works.step_1') }}</li>
                                            <li>{{ __('frontend.discount_redemptions.how_it_works.step_2') }}</li>
                                            <li>{{ __('frontend.discount_redemptions.how_it_works.step_3') }}</li>
                                            <li>{{ __('frontend.discount_redemptions.how_it_works.step_4') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                {{ __('frontend.discount_redemptions.terms.title') }}
                            </h3>
                            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                                <p>{{ __('frontend.discount_redemptions.terms.intro') }}</p>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li>{{ __('frontend.discount_redemptions.terms.bullet_1') }}</li>
                                    <li>{{ __('frontend.discount_redemptions.terms.bullet_2') }}</li>
                                    <li>{{ __('frontend.discount_redemptions.terms.bullet_3') }}</li>
                                    <li>{{ __('frontend.discount_redemptions.terms.bullet_4') }}</li>
                                    <li>{{ __('frontend.discount_redemptions.terms.bullet_5') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('frontend.discount-redemptions.index') }}"
                           class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 ease-in-out">
                            {{ __('frontend.discount_redemptions.actions.cancel') }}
                        </a>
                        <button type="submit"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            {{ __('frontend.discount_redemptions.actions.redeem') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Help Section -->
            <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('frontend.discount_redemptions.help_section.title') }}
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                {{ __('frontend.discount_redemptions.help_section.common_issues') }}
                            </h3>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li>• {{ __('frontend.discount_redemptions.help_section.issue_1') }}</li>
                                <li>• {{ __('frontend.discount_redemptions.help_section.issue_2') }}</li>
                                <li>• {{ __('frontend.discount_redemptions.help_section.issue_3') }}</li>
                                <li>• {{ __('frontend.discount_redemptions.help_section.issue_4') }}</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                {{ __('frontend.discount_redemptions.help_section.contact_support') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {{ __('frontend.discount_redemptions.help_section.contact_support_description') }}
                            </p>
                            <a href="{{ route('frontend.contact.index') }}"
                               class="inline-flex items-center text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                {{ __('frontend.discount_redemptions.help_section.contact_support') }}
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
