<div class="live-inventory-tracker" 
     x-data="{
         autoRefresh: @entangle('autoRefresh'),
         refreshInterval: @entangle('refreshInterval'),
         stockFilter: @entangle('stockFilter'),
         sortBy: @entangle('sortBy'),
         lowStockThreshold: @entangle('lowStockThreshold'),
         isRefreshing: false,
         lastUpdated: new Date(),
         showFilters: false
     }"
     x-init="
         // Auto-refresh functionality
         let refreshInterval;
         
         $watch('autoRefresh', (value) => {
             if (value) {
                 refreshInterval = setInterval(() => {
                     $wire.refreshInventory();
                     isRefreshing = true;
                     setTimeout(() => isRefreshing = false, 1000);
                     lastUpdated = new Date();
                 }, refreshInterval * 1000);
             } else {
                 clearInterval(refreshInterval);
             }
         });
         
         // Listen for external refresh events
         $wire.on('refresh-inventory', () => {
             isRefreshing = true;
             setTimeout(() => isRefreshing = false, 1000);
             lastUpdated = new Date();
         });
     ">
    
    <!-- Inventory Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                    {{ __('translations.live_inventory_tracker') }}
                </h2>
                <p class="text-gray-600 mt-1">
                    {{ __('translations.real_time_stock_monitoring') }}
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Stock Filter -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">{{ __('translations.filter') }}:</label>
                    <select wire:model.live="stockFilter" 
                            class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">{{ __('translations.all_products') }}</option>
                        <option value="in_stock">{{ __('translations.in_stock') }}</option>
                        <option value="low">{{ __('translations.low_stock') }}</option>
                        <option value="out">{{ __('translations.out_of_stock') }}</option>
                    </select>
                </div>
                
                <!-- Sort By -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">{{ __('translations.sort_by') }}:</label>
                    <select wire:model.live="sortBy" 
                            class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="stock_quantity">{{ __('translations.stock_quantity') }}</option>
                        <option value="name">{{ __('translations.name') }}</option>
                        <option value="last_updated">{{ __('translations.last_updated') }}</option>
                    </select>
                </div>
                
                <!-- Auto Refresh Toggle -->
                <button wire:click="toggleAutoRefresh"
                        wire:confirm="{{ __('translations.confirm_toggle_auto_refresh') }}"
                        :class="autoRefresh ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors duration-200">
                    <svg class="w-4 h-4" :class="autoRefresh ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ __('translations.auto_refresh') }}
                </button>
                
                <!-- Manual Refresh -->
                <button wire:click="refreshInventory"
                        :disabled="isRefreshing"
                        class="flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                    <svg class="w-4 h-4" :class="isRefreshing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ __('translations.refresh') }}
                </button>
            </div>
        </div>
        
        <!-- Last Updated -->
        <div class="mt-4 text-xs text-gray-500 flex items-center gap-2">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span x-text="'{{ __('translations.last_updated') }}: ' + lastUpdated.toLocaleTimeString()"></span>
        </div>
    </div>

    <!-- Inventory Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">{{ __('translations.total_products') }}</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($this->inventoryStats['total_products']) }}</p>
                    <p class="text-sm text-blue-600 flex items-center gap-1 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        {{ $this->inventoryStats['stock_health_percentage'] }}% {{ __('translations.in_stock') }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">{{ __('translations.in_stock') }}</p>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($this->inventoryStats['in_stock']) }}</p>
                    <p class="text-sm text-green-600 flex items-center gap-1 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('translations.available') }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">{{ __('translations.low_stock') }}</p>
                    <p class="text-3xl font-bold text-orange-600">{{ number_format($this->inventoryStats['low_stock']) }}</p>
                    <p class="text-sm text-orange-600 flex items-center gap-1 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        {{ __('translations.needs_attention') }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">{{ __('translations.out_of_stock') }}</p>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($this->inventoryStats['out_of_stock']) }}</p>
                    <p class="text-sm text-red-600 flex items-center gap-1 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ __('translations.unavailable') }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    @if(count($this->lowStockAlerts) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                    {{ __('translations.low_stock_alerts') }}
                </h3>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">{{ __('translations.threshold') }}:</label>
                    <input wire:model.live="lowStockThreshold" 
                           type="number" 
                           min="1" 
                           max="100"
                           class="w-16 text-sm border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->lowStockAlerts as $alert)
                    <div class="p-4 border border-orange-200 rounded-lg bg-orange-50 hover:bg-orange-100 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-gray-900">{{ $alert['name'] }}</h4>
                                <p class="text-xs text-gray-600">{{ $alert['sku'] }} • {{ $alert['brand'] }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-lg font-bold text-orange-600">{{ $alert['stock_quantity'] }}</span>
                                    <span class="text-xs text-gray-500">/ {{ $alert['threshold'] }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($alert['urgency'] === 'critical') bg-red-100 text-red-800
                                    @elseif($alert['urgency'] === 'high') bg-orange-100 text-orange-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($alert['urgency']) }}
                                </span>
                                <span class="text-xs text-gray-500 mt-1">{{ $alert['last_updated']->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Inventory Items Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                {{ __('translations.inventory_items') }}
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('translations.product') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('translations.sku') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('translations.brand') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('translations.stock') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('translations.price') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('translations.total_value') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('translations.status') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->inventoryItems as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($item['image'])
                                        <img class="h-10 w-10 rounded-lg object-cover" src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $item['category'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item['sku'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item['brand'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-sm font-medium text-gray-900">{{ $item['stock_quantity'] }}</span>
                                    <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $item['stock_percentage'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Illuminate\Support\Number::currency($item['price'], 'EUR') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Illuminate\Support\Number::currency($item['total_value'], 'EUR') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($item['stock_status'] === 'out_of_stock') bg-red-100 text-red-800
                                    @elseif($item['stock_status'] === 'low_stock') bg-orange-100 text-orange-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    @if($item['stock_status'] === 'out_of_stock')
                                        {{ __('translations.out_of_stock') }}
                                    @elseif($item['stock_status'] === 'low_stock')
                                        {{ __('translations.low_stock') }}
                                    @else
                                        {{ __('translations.in_stock') }}
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p>{{ __('translations.no_inventory_items_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
