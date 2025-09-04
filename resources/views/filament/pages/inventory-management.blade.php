<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Inventory Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format(\App\Models\Product::where('stock_quantity', '>', 10)->count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('admin.inventory.good_stock') }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('admin.inventory.above_threshold') }}</div>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ number_format(\App\Models\Product::whereBetween('stock_quantity', [1, 10])->count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('admin.inventory.low_stock') }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('admin.inventory.needs_attention') }}</div>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ number_format(\App\Models\Product::where('stock_quantity', '<=', 0)->count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('admin.inventory.out_of_stock') }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('admin.inventory.needs_restock') }}</div>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format(\App\Models\Product::sum('stock_quantity')) }}</div>
                    <div class="text-sm text-gray-500">{{ __('admin.inventory.total_units') }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('admin.inventory.across_all_products') }}</div>
                </div>
            </x-filament::card>
        </div>

        <!-- Quick Actions -->
        <x-filament::card>
            <div class="flex flex-wrap gap-4">
                <button 
                    wire:click="$set('stockFilter', 'all')"
                    @class([
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-blue-100 text-blue-800' => $stockFilter === 'all',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200' => $stockFilter !== 'all',
                    ])
                >
                    {{ __('admin.stock_filters.all') }}
                </button>
                
                <button 
                    wire:click="$set('stockFilter', 'good')"
                    @class([
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-green-100 text-green-800' => $stockFilter === 'good',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200' => $stockFilter !== 'good',
                    ])
                >
                    {{ __('admin.stock_filters.good_stock') }}
                </button>
                
                <button 
                    wire:click="$set('stockFilter', 'low')"
                    @class([
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-yellow-100 text-yellow-800' => $stockFilter === 'low',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200' => $stockFilter !== 'low',
                    ])
                >
                    {{ __('admin.stock_filters.low_stock') }}
                </button>
                
                <button 
                    wire:click="$set('stockFilter', 'out')"
                    @class([
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-red-100 text-red-800' => $stockFilter === 'out',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200' => $stockFilter !== 'out',
                    ])
                >
                    {{ __('admin.stock_filters.out_of_stock') }}
                </button>
            </div>
        </x-filament::card>

        <!-- Inventory Table -->
        <x-filament::card>
            {{ $this->table }}
        </x-filament::card>
    </div>
</x-filament-panels::page>
