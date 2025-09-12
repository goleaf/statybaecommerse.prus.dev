<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Stock Status Overview -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Stock Status Overview') }}</h4>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('In Stock') }}</span>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($summary['in_stock']) }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Low Stock') }}</span>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($summary['low_stock']) }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Out of Stock') }}</span>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($summary['out_of_stock']) }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Not Tracked') }}</span>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-gray-500 rounded-full mr-2"></div>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($summary['not_tracked']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Statistics -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Inventory Statistics') }}</h4>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Products') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($summary['total_products']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Tracked Products') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($summary['tracked_products']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Tracking Rate') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ $summary['total_products'] > 0 ? number_format(($summary['tracked_products'] / $summary['total_products']) * 100, 1) : 0 }}%
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Stock Health') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        @php
                            $totalTracked = $summary['tracked_products'];
                            $healthyStock = $totalTracked > 0 ? (($summary['in_stock'] / $totalTracked) * 100) : 0;
                        @endphp
                        {{ number_format($healthyStock, 1) }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Health Chart -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Stock Health Distribution') }}</h4>
        <div class="space-y-4">
            @php
                $totalTracked = $summary['tracked_products'];
                $inStockPercent = $totalTracked > 0 ? ($summary['in_stock'] / $totalTracked) * 100 : 0;
                $lowStockPercent = $totalTracked > 0 ? ($summary['low_stock'] / $totalTracked) * 100 : 0;
                $outOfStockPercent = $totalTracked > 0 ? ($summary['out_of_stock'] / $totalTracked) * 100 : 0;
            @endphp
            
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600 dark:text-gray-400">{{ __('In Stock') }}</span>
                    <span class="text-gray-900 dark:text-white">{{ number_format($inStockPercent, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $inStockPercent }}%"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Low Stock') }}</span>
                    <span class="text-gray-900 dark:text-white">{{ number_format($lowStockPercent, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $lowStockPercent }}%"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Out of Stock') }}</span>
                    <span class="text-gray-900 dark:text-white">{{ number_format($outOfStockPercent, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ $outOfStockPercent }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
