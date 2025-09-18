@extends('components.layouts.base')

@section('title', $variantStock->display_name)
@section('description', __('inventory.variant_stock_details'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
            <li>
                <a href="{{ route('frontend.variant-stock.index') }}" class="hover:text-gray-700 dark:hover:text-gray-300">
                    {{ __('inventory.variant_stock') }}
                </a>
            </li>
            <li>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </li>
            <li class="text-gray-900 dark:text-white">
                {{ $variantStock->display_name }}
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Stock Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ $variantStock->display_name }}
                </h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('inventory.stock_information') }}
                        </h3>
                        
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('inventory.current_stock') }}
                                </dt>
                                <dd class="text-sm text-gray-900 dark:text-white font-semibold">
                                    {{ number_format($variantStock->stock) }}
                                </dd>
                            </div>
                            
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('inventory.reserved_stock') }}
                                </dt>
                                <dd class="text-sm text-gray-900 dark:text-white">
                                    {{ number_format($variantStock->reserved) }}
                                </dd>
                            </div>
                            
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('inventory.available') }}
                                </dt>
                                <dd class="text-sm text-gray-900 dark:text-white font-semibold">
                                    {{ number_format($variantStock->available_stock) }}
                                </dd>
                            </div>
                            
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('inventory.threshold') }}
                                </dt>
                                <dd class="text-sm text-gray-900 dark:text-white">
                                    {{ number_format($variantStock->threshold) }}
                                </dd>
                            </div>
                            
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('inventory.status') }}
                                </dt>
                                <dd>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($variantStock->stock_status === 'in_stock') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($variantStock->stock_status === 'low_stock') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @elseif($variantStock->stock_status === 'out_of_stock') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                        @endif">
                                        {{ $variantStock->stock_status_label }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('inventory.additional_information') }}
                        </h3>
                        
                        <dl class="space-y-3">
                            @if($variantStock->cost_per_unit)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('inventory.cost_per_unit') }}
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">
                                        €{{ number_format($variantStock->cost_per_unit, 2) }}
                                    </dd>
                                </div>
                            @endif
                            
                            @if($variantStock->stock_value > 0)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('inventory.stock_value') }}
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-white font-semibold">
                                        €{{ number_format($variantStock->stock_value, 2) }}
                                    </dd>
                                </div>
                            @endif
                            
                            @if($variantStock->supplier)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('inventory.supplier') }}
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">
                                        {{ $variantStock->supplier->name }}
                                    </dd>
                                </div>
                            @endif
                            
                            @if($variantStock->batch_number)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('inventory.batch_number') }}
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">
                                        {{ $variantStock->batch_number }}
                                    </dd>
                                </div>
                            @endif
                            
                            @if($variantStock->expiry_date)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('inventory.expiry_date') }}
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-white
                                        @if($variantStock->isExpired()) text-red-600 dark:text-red-400
                                        @elseif($variantStock->isExpiringSoon()) text-yellow-600 dark:text-yellow-400
                                        @endif">
                                        {{ $variantStock->expiry_date->format('Y-m-d') }}
                                        @if($variantStock->isExpired())
                                            <span class="ml-1 text-xs">({{ __('inventory.expired') }})</span>
                                        @elseif($variantStock->isExpiringSoon())
                                            <span class="ml-1 text-xs">({{ __('inventory.expiring_soon') }})</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            
                            @if($variantStock->last_restocked_at)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('inventory.last_restocked') }}
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">
                                        {{ $variantStock->last_restocked_at->format('Y-m-d H:i') }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                @if($variantStock->notes)
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            {{ __('inventory.notes') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $variantStock->notes }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Stock Movements -->
            @if($variantStock->stockMovements->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('inventory.recent_movements') }}
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('inventory.quantity') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('inventory.type') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('inventory.reason') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('inventory.moved_at') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('inventory.user') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($variantStock->stockMovements->take(10) as $movement)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm font-medium
                                                @if($movement->type === 'in') text-green-600 dark:text-green-400
                                                @else text-red-600 dark:text-red-400
                                                @endif">
                                                {{ $movement->type === 'in' ? '+' : '-' }}{{ number_format($movement->quantity) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                @if($movement->type === 'in') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                @endif">
                                                {{ $movement->type_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $movement->reason_label }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $movement->moved_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $movement->user?->name ?? __('inventory.system') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Product Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('inventory.product_information') }}
                </h3>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('inventory.product') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $variantStock->product_name }}
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('inventory.variant') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $variantStock->variant_name }}
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('inventory.sku') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $variantStock->variant->sku }}
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('inventory.location') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $variantStock->location_name }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('inventory.quick_actions') }}
                </h3>
                
                <div class="space-y-3">
                    <a href="{{ route('frontend.products.show', $variantStock->variant->product) }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 text-center block">
                        {{ __('inventory.view_product') }}
                    </a>
                    
                    <a href="{{ route('frontend.variant-stock.index', ['search' => $variantStock->variant->sku]) }}" 
                       class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 text-center block">
                        {{ __('inventory.find_similar') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

