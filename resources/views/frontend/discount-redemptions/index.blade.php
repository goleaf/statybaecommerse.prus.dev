@extends('frontend.layouts.app')

@section('title', __('Discount Redemptions'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ __('My Discount Redemptions') }}
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ __('View and manage your discount redemptions') }}
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 6v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-6V7a2 2 0 00-2-2H5z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Total Redemptions') }}
                            </p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalRedemptions }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Total Saved') }}</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                €{{ number_format($totalSaved, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Pending') }}</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $pendingRedemptions }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Redeemed') }}</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $redeemedRedemptions }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('frontend.discount-redemptions.index') }}"
                          class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Status') }}
                            </label>
                            <select name="status" id="status"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                                    {{ __('Pending') }}</option>
                                <option value="redeemed" {{ request('status') === 'redeemed' ? 'selected' : '' }}>
                                    {{ __('Redeemed') }}</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>
                                    {{ __('Expired') }}</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>
                                    {{ __('Cancelled') }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Currency') }}
                            </label>
                            <select name="currency" id="currency"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">{{ __('All Currencies') }}</option>
                                <option value="EUR" {{ request('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="USD" {{ request('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="GBP" {{ request('currency') === 'GBP' ? 'selected' : '' }}>GBP</option>
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('From Date') }}
                            </label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>

                        <div class="flex items-end">
                            <button type="submit"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                                {{ __('Filter') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Redemptions List -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Redemptions') }}
                    </h2>
                </div>

                @if ($redemptions->count() > 0)
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($redemptions as $redemption)
                            <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                @if ($redemption->status === 'redeemed')
                                                    <div
                                                         class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400"
                                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                @elseif($redemption->status === 'pending')
                                                    <div
                                                         class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400"
                                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>
                                                @elseif($redemption->status === 'expired')
                                                    <div
                                                         class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none"
                                                             stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </div>
                                                @else
                                                    <div
                                                         class="w-10 h-10 bg-gray-100 dark:bg-gray-900 rounded-full flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400"
                                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-2">
                                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                                        {{ $redemption->discount->name }}
                                                    </h3>
                                                    <span
                                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if ($redemption->status === 'redeemed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @elseif($redemption->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                    @elseif($redemption->status === 'expired') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                        {{ ucfirst($redemption->status) }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ __('Code') }}: <span
                                                          class="font-mono">{{ $redemption->code->code }}</span>
                                                </p>
                                                @if ($redemption->order)
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ __('Order') }}: #{{ $redemption->order->id }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-4">
                                        <div class="text-right">
                                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                                €{{ number_format($redemption->amount_saved, 2) }}
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ __('Saved') }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $redemption->redeemed_at->format('M j, Y') }}
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $redemption->redeemed_at->format('g:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if ($redemption->notes)
                                    <div class="mt-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <strong>{{ __('Notes') }}:</strong> {{ $redemption->notes }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $redemptions->links() }}
                    </div>
                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 6v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-6V7a2 2 0 00-2-2H5z">
                            </path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('No redemptions found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Get started by using a discount code.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

