<div class="inventory-dashboard space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Total Products') }}</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($summary['total_products'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('In Stock') }}</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($summary['in_stock'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Low Stock') }}</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($summary['low_stock'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Out of Stock') }}</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($summary['out_of_stock'] ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    @if(!empty($lowStockProducts))
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-200">{{ __('Low Stock Alert') }}</h3>
            </div>
            <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                {{ __('The following products are running low on stock:') }}
            </p>
            <div class="mt-4 space-y-2">
                @foreach($lowStockProducts as $product)
                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg p-3">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $product['name'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $product['sku'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">
                                {{ $product['stock_quantity'] }} {{ __('left') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Threshold:') }} {{ $product['low_stock_threshold'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Out of Stock Alert -->
    @if(!empty($outOfStockProducts))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <h3 class="text-lg font-medium text-red-800 dark:text-red-200">{{ __('Out of Stock Alert') }}</h3>
            </div>
            <p class="mt-2 text-sm text-red-700 dark:text-red-300">
                {{ __('The following products are out of stock:') }}
            </p>
            <div class="mt-4 space-y-2">
                @foreach($outOfStockProducts as $product)
                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg p-3">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $product['name'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $product['sku'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">
                                {{ __('Out of Stock') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Refresh Button -->
    <div class="flex justify-center">
        <button wire:click="loadData" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            {{ __('Refresh Data') }}
        </button>
    </div>
</div>
