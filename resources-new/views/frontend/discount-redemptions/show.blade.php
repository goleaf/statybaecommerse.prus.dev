@extends('frontend.layouts.app')

@section('title', __('Discount Redemption Details'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ __('Redemption Details') }}
                        </h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            {{ __('View detailed information about your discount redemption') }}
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('frontend.discount-redemptions.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            {{ __('Back to Redemptions') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Redemption Details -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ $redemption->discount->name }}
                        </h2>
                        <span
                              class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if ($redemption->status === 'redeemed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($redemption->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($redemption->status === 'expired') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                            {{ ucfirst($redemption->status) }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Discount Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ __('Discount Information') }}
                            </h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('Discount Name') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $redemption->discount->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('Discount Code') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white font-mono">
                                        {{ $redemption->code->code }}</dd>
                                </div>
                                @if ($redemption->discount->description)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('Description') }}</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">
                                            {{ $redemption->discount->description }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Financial Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ __('Financial Information') }}
                            </h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('Amount Saved') }}</dt>
                                    <dd class="text-2xl font-bold text-green-600 dark:text-green-400">
                                        €{{ number_format($redemption->amount_saved, 2) }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Currency') }}
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $redemption->currency_code }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Redemption Details -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ __('Redemption Details') }}
                            </h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('Redeemed At') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">
                                        {{ $redemption->redeemed_at->format('F j, Y \a\t g:i A') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ ucfirst($redemption->status) }}
                                    </dd>
                                </div>
                                @if ($redemption->order)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('Order') }}</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">
                                            <a href="{{ route('frontend.orders.show', $redemption->order) }}"
                                               class="text-blue-600 dark:text-blue-400 hover:underline">
                                                #{{ $redemption->order->id }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Additional Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ __('Additional Information') }}
                            </h3>
                            <dl class="space-y-3">
                                @if ($redemption->notes)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('Notes') }}</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $redemption->notes }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('IP Address') }}
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-white font-mono">
                                        {{ $redemption->ip_address }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('User Agent') }}
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-white break-all">
                                        {{ $redemption->user_agent }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    @if ($redemption->notes)
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                {{ __('Notes') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $redemption->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Status Information -->
            @if ($redemption->status === 'pending')
                <div
                     class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                {{ __('Redemption Pending') }}
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p>{{ __('This redemption is currently pending and will be processed soon.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($redemption->status === 'expired')
                <div class="mt-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                {{ __('Redemption Expired') }}
                            </h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                <p>{{ __('This redemption has expired and is no longer valid.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($redemption->status === 'cancelled')
                <div class="mt-6 bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ __('Redemption Cancelled') }}
                            </h3>
                            <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                <p>{{ __('This redemption has been cancelled.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($redemption->status === 'redeemed')
                <div
                     class="mt-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ __('Redemption Successful') }}
                            </h3>
                            <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                <p>{{ __('This redemption has been successfully processed.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

