@extends('components.layouts.base')

@section('title', __('inventory.stock_item_details'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('stock.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            {{ __('inventory.stock_management') }}
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-gray-500 md:ml-2 dark:text-gray-400">{{ $stock->variant->product->name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">
                {{ $stock->variant->product->name }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                {{ $stock->variant->display_name }} - {{ $stock->location->name }}
            </p>
        </div>
        
        <div class="flex space-x-4">
            <button onclick="adjustStock()" 
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                </svg>
                <span>{{ __('inventory.adjust_stock') }}</span>
            </button>
            
            @if($stock->available_stock > 0)
                <button onclick="reserveStock()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>{{ __('inventory.reserve_stock') }}</span>
                </button>
            @endif
            
            @if($stock->reserved > 0)
                <button onclick="unreserveStock()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                    <span>{{ __('inventory.unreserve_stock') }}</span>
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Stock Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('inventory.basic_information') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.product') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->variant->product->name }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.variant') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->variant->display_name }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.location') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->location->name }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.supplier') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->supplier?->name ?? __('inventory.no_supplier') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stock Levels -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('inventory.stock_levels') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold {{ $stock->isOutOfStock() ? 'text-red-600 dark:text-red-400' : ($stock->isLowStock() ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                            {{ $stock->stock }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('inventory.current_stock') }}
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                            {{ $stock->reserved }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('inventory.reserved') }}
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-3xl font-bold {{ $stock->available_stock <= 0 ? 'text-red-600 dark:text-red-400' : ($stock->available_stock <= 10 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                            {{ $stock->available_stock }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('inventory.available') }}
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $stock->incoming }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('inventory.incoming') }}
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.low_stock_threshold') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->threshold }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.reorder_point') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->reorder_point }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.max_stock_level') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->max_stock_level ?? __('inventory.not_set') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('inventory.financial_information') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.cost_per_unit') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->cost_per_unit ? '€' . number_format($stock->cost_per_unit, 2) : __('inventory.not_set') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.stock_value') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
                            €{{ number_format($stock->stock_value, 2) }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.reserved_value') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            €{{ number_format($stock->reserved_value, 2) }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.total_value') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
                            €{{ number_format($stock->total_value, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('inventory.additional_information') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.batch_number') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->batch_number ?? __('inventory.not_set') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.expiry_date') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->expiry_date?->format('Y-m-d') ?? __('inventory.no_expiry') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.status') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ ucfirst($stock->status) }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.tracked') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->is_tracked ? __('inventory.yes') : __('inventory.no') }}
                        </p>
                    </div>
                </div>
                
                @if($stock->notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.notes') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->notes }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Stock Status -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('inventory.stock_status') }}
                </h3>
                
                @php
                    $statusColors = [
                        'in_stock' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'low_stock' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        'out_of_stock' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        'needs_reorder' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'not_tracked' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                    ];
                @endphp
                
                <div class="text-center">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-lg font-medium {{ $statusColors[$stock->stock_status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $stock->stock_status_label }}
                    </span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('inventory.quick_actions') }}
                </h3>
                
                <div class="space-y-3">
                    <button onclick="adjustStock()" 
                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        <span>{{ __('inventory.adjust_stock') }}</span>
                    </button>
                    
                    @if($stock->available_stock > 0)
                        <button onclick="reserveStock()" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <span>{{ __('inventory.reserve_stock') }}</span>
                        </button>
                    @endif
                    
                    @if($stock->reserved > 0)
                        <button onclick="unreserveStock()" 
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ __('inventory.unreserve_stock') }}</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Timestamps -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('inventory.timestamps') }}
                </h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.last_restocked_at') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->last_restocked_at?->format('Y-m-d H:i') ?? __('inventory.never') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.last_sold_at') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->last_sold_at?->format('Y-m-d H:i') ?? __('inventory.never') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.created_at') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->created_at->format('Y-m-d H:i') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('inventory.updated_at') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $stock->updated_at->format('Y-m-d H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Stock Movements -->
    <div class="mt-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('inventory.recent_stock_movements') }}
            </h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('inventory.moved_at') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('inventory.movement_type') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('inventory.quantity') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('inventory.reason') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('inventory.user') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($stock->stockMovements->take(10) as $movement)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $movement->moved_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $movement->type === 'in' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                        {{ $movement->type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $movement->type === 'in' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $movement->reason_label }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $movement->user?->name ?? __('inventory.system') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    {{ __('inventory.no_movements_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div id="adjustStockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ __('inventory.adjust_stock') }}
            </h3>
            
            <form id="adjustStockForm">
                @csrf
                <div class="mb-4">
                    <label for="adjustment_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('inventory.adjustment_quantity') }}
                    </label>
                    <input type="number" 
                           id="adjustment_quantity" 
                           name="quantity" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('inventory.adjustment_quantity_help') }}
                    </p>
                </div>

                <div class="mb-4">
                    <label for="adjustment_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('inventory.adjustment_reason') }}
                    </label>
                    <select id="adjustment_reason" 
                            name="reason" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('inventory.select_reason') }}</option>
                        <option value="manual_adjustment">{{ __('inventory.reason_manual_adjustment') }}</option>
                        <option value="damage">{{ __('inventory.reason_damage') }}</option>
                        <option value="theft">{{ __('inventory.reason_theft') }}</option>
                        <option value="return">{{ __('inventory.reason_return') }}</option>
                        <option value="restock">{{ __('inventory.reason_restock') }}</option>
                        <option value="transfer">{{ __('inventory.reason_transfer') }}</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="adjustment_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('inventory.adjustment_notes') }}
                    </label>
                    <textarea id="adjustment_notes" 
                              name="notes" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeAdjustStockModal()"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md">
                        {{ __('inventory.cancel') }}
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        {{ __('inventory.adjust') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reserve Stock Modal -->
<div id="reserveStockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ __('inventory.reserve_stock') }}
            </h3>
            
            <form id="reserveStockForm">
                @csrf
                <div class="mb-4">
                    <label for="reserve_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('inventory.reserve_quantity') }}
                    </label>
                    <input type="number" 
                           id="reserve_quantity" 
                           name="quantity" 
                           required
                           min="1"
                           max="{{ $stock->available_stock }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('inventory.available_stock') }}: {{ $stock->available_stock }}
                    </p>
                </div>

                <div class="mb-6">
                    <label for="reserve_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('inventory.reserve_notes') }}
                    </label>
                    <textarea id="reserve_notes" 
                              name="notes" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeReserveStockModal()"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md">
                        {{ __('inventory.cancel') }}
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        {{ __('inventory.reserve') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Unreserve Stock Modal -->
<div id="unreserveStockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ __('inventory.unreserve_stock') }}
            </h3>
            
            <form id="unreserveStockForm">
                @csrf
                <div class="mb-4">
                    <label for="unreserve_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('inventory.unreserve_quantity') }}
                    </label>
                    <input type="number" 
                           id="unreserve_quantity" 
                           name="quantity" 
                           required
                           min="1"
                           max="{{ $stock->reserved }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('inventory.reserved_stock') }}: {{ $stock->reserved }}
                    </p>
                </div>

                <div class="mb-6">
                    <label for="unreserve_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('inventory.unreserve_notes') }}
                    </label>
                    <textarea id="unreserve_notes" 
                              name="notes" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeUnreserveStockModal()"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md">
                        {{ __('inventory.cancel') }}
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md">
                        {{ __('inventory.unreserve') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function adjustStock() {
    document.getElementById('adjustStockModal').classList.remove('hidden');
}

function closeAdjustStockModal() {
    document.getElementById('adjustStockModal').classList.add('hidden');
    document.getElementById('adjustStockForm').reset();
}

function reserveStock() {
    document.getElementById('reserveStockModal').classList.remove('hidden');
}

function closeReserveStockModal() {
    document.getElementById('reserveStockModal').classList.add('hidden');
    document.getElementById('reserveStockForm').reset();
}

function unreserveStock() {
    document.getElementById('unreserveStockModal').classList.remove('hidden');
}

function closeUnreserveStockModal() {
    document.getElementById('unreserveStockModal').classList.add('hidden');
    document.getElementById('unreserveStockForm').reset();
}

// Stock Adjustment Form
document.getElementById('adjustStockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/stock/{{ $stock->id }}/adjust`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("inventory.error_occurred") }}');
    });
    
    closeAdjustStockModal();
});

// Reserve Stock Form
document.getElementById('reserveStockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/stock/{{ $stock->id }}/reserve`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("inventory.error_occurred") }}');
    });
    
    closeReserveStockModal();
});

// Unreserve Stock Form
document.getElementById('unreserveStockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/stock/{{ $stock->id }}/unreserve`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("inventory.error_occurred") }}');
    });
    
    closeUnreserveStockModal();
});
</script>
@endsection
