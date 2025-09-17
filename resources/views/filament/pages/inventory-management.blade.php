<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Inventory Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $inventoryService = app(\App\Services\InventoryService::class);
                $summary = $inventoryService->getInventorySummary();
            @endphp
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <x-heroicon-o-cube-transparent class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Total Products') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($summary['total_products']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <x-heroicon-o-check-circle class="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('In Stock') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($summary['in_stock']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Low Stock') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($summary['low_stock']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <x-heroicon-o-x-circle class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Out of Stock') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($summary['out_of_stock']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Quick Actions') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('filament.admin.resources.products.index', ['tableFilters' => ['stock_status' => ['value' => 'low_stock']]]) }}" 
                   class="flex items-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors">
                    <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-yellow-600 dark:text-yellow-400 mr-3" />
                    <div>
                        <p class="font-medium text-yellow-900 dark:text-yellow-100">{{ __('View Low Stock') }}</p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">{{ $summary['low_stock'] }} {{ __('products') }}</p>
                    </div>
                </a>

                <a href="{{ route('filament.admin.resources.products.index', ['tableFilters' => ['stock_status' => ['value' => 'out_of_stock']]]) }}" 
                   class="flex items-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                    <x-heroicon-o-x-circle class="h-8 w-8 text-red-600 dark:text-red-400 mr-3" />
                    <div>
                        <p class="font-medium text-red-900 dark:text-red-100">{{ __('View Out of Stock') }}</p>
                        <p class="text-sm text-red-700 dark:text-red-300">{{ $summary['out_of_stock'] }} {{ __('products') }}</p>
                    </div>
                </a>

                <a href="{{ route('filament.admin.resources.products.index', ['tableFilters' => ['manage_stock' => ['value' => '1']]]) }}" 
                   class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                    <x-heroicon-o-cube-transparent class="h-8 w-8 text-blue-600 dark:text-blue-400 mr-3" />
                    <div>
                        <p class="font-medium text-blue-900 dark:text-blue-100">{{ __('Tracked Products') }}</p>
                        <p class="text-sm text-blue-700 dark:text-blue-300">{{ $summary['tracked_products'] }} {{ __('products') }}</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Product Inventory') }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('Manage stock levels for all products') }}</p>
            </div>
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>