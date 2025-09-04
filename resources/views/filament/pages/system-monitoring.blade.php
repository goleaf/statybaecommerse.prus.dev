<x-filament-panels::page>
    <div class="space-y-6">
        <!-- System Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-filament::card>
                <div class="text-center">
                    <x-heroicon-o-server class="w-12 h-12 mx-auto mb-3 text-blue-500" />
                    <h3 class="text-lg font-semibold">{{ __('admin.monitoring.system_info') }}</h3>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('admin.monitoring.php_version') }}:</span>
                            <span class="font-medium">{{ $systemStats['php_version'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('admin.monitoring.laravel_version') }}:</span>
                            <span class="font-medium">{{ $systemStats['laravel_version'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('admin.monitoring.memory_usage') }}:</span>
                            <span class="font-medium">{{ $systemStats['memory_usage'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('admin.monitoring.memory_peak') }}:</span>
                            <span class="font-medium">{{ $systemStats['memory_peak'] ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <x-heroicon-o-circle-stack class="w-12 h-12 mx-auto mb-3 text-green-500" />
                    <h3 class="text-lg font-semibold">{{ __('admin.monitoring.database_info') }}</h3>
                    <div class="mt-3 space-y-2 text-sm">
                        @if(isset($databaseStats['error']))
                            <div class="text-red-600">{{ $databaseStats['error'] }}</div>
                        @else
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('admin.monitoring.connection') }}:</span>
                                <span class="font-medium">{{ $databaseStats['connection'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('admin.monitoring.total_tables') }}:</span>
                                <span class="font-medium">{{ $databaseStats['total_tables'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('admin.monitoring.total_products') }}:</span>
                                <span class="font-medium">{{ number_format($databaseStats['total_products'] ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('admin.monitoring.total_orders') }}:</span>
                                <span class="font-medium">{{ number_format($databaseStats['total_orders'] ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('admin.monitoring.total_users') }}:</span>
                                <span class="font-medium">{{ number_format($databaseStats['total_users'] ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('admin.monitoring.database_size') }}:</span>
                                <span class="font-medium">{{ $databaseStats['database_size'] ?? 'N/A' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <x-heroicon-o-queue-list class="w-12 h-12 mx-auto mb-3 text-purple-500" />
                    <h3 class="text-lg font-semibold">{{ __('admin.monitoring.queue_info') }}</h3>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('admin.monitoring.pending_jobs') }}:</span>
                            <span class="font-medium">{{ number_format($queueStats['pending_jobs'] ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('admin.monitoring.failed_jobs') }}:</span>
                            <span class="font-medium text-red-600">{{ number_format($queueStats['failed_jobs'] ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('admin.monitoring.cache_driver') }}:</span>
                            <span class="font-medium">{{ $systemStats['cache_driver'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('admin.monitoring.queue_driver') }}:</span>
                            <span class="font-medium">{{ $systemStats['queue_driver'] ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </x-filament::card>
        </div>

        <!-- Performance Metrics -->
        <x-filament::card>
            <h3 class="text-lg font-semibold mb-4">{{ __('admin.monitoring.performance_metrics') }}</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($databaseStats['total_products'] ?? 0) }}</div>
                    <div class="text-sm text-gray-600">{{ __('admin.monitoring.products') }}</div>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($databaseStats['total_orders'] ?? 0) }}</div>
                    <div class="text-sm text-gray-600">{{ __('admin.monitoring.orders') }}</div>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($databaseStats['total_users'] ?? 0) }}</div>
                    <div class="text-sm text-gray-600">{{ __('admin.monitoring.users') }}</div>
                </div>
                <div class="text-center p-4 bg-orange-50 rounded-lg">
                    <div class="text-2xl font-bold text-orange-600">{{ $systemStats['memory_usage'] ?? 'N/A' }}</div>
                    <div class="text-sm text-gray-600">{{ __('admin.monitoring.memory_usage') }}</div>
                </div>
            </div>
        </x-filament::card>

        <!-- System Actions -->
        <x-filament::card>
            <h3 class="text-lg font-semibold mb-4">{{ __('admin.monitoring.system_actions') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button 
                    wire:click="clearCache"
                    class="flex items-center justify-center gap-2 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <x-heroicon-o-trash class="w-5 h-5 text-orange-500" />
                    <span>{{ __('admin.actions.clear_cache') }}</span>
                </button>
                
                <button 
                    wire:click="optimizeSystem"
                    class="flex items-center justify-center gap-2 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <x-heroicon-o-rocket-launch class="w-5 h-5 text-green-500" />
                    <span>{{ __('admin.actions.optimize_system') }}</span>
                </button>
                
                <button 
                    wire:click="$refresh"
                    class="flex items-center justify-center gap-2 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <x-heroicon-o-arrow-path class="w-5 h-5 text-blue-500" />
                    <span>{{ __('admin.actions.refresh_stats') }}</span>
                </button>
            </div>
        </x-filament::card>
    </div>

    <script>
        // Auto-refresh every 30 seconds
        setInterval(() => {
            @this.loadSystemStats();
        }, 30000);
    </script>
</x-filament-panels::page>
