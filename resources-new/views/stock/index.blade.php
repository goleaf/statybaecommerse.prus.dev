@extends('components.layouts.base')

@section('title', __('inventory.stock_management'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('inventory.stock_management') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                {{ __('inventory.stock_management_description') }}
            </p>
        </div>
        
        <div class="flex space-x-4">
            <a href="{{ route('stock.report') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span>{{ __('inventory.stock_report') }}</span>
            </a>
            
            <a href="{{ route('stock.export') }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>{{ __('inventory.export_stock') }}</span>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('stock.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('inventory.search') }}
                </label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="{{ __('inventory.search_placeholder') }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label for="location_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('inventory.location') }}
                </label>
                <select id="location_id" 
                        name="location_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('inventory.all_locations') }}</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="supplier_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('inventory.supplier') }}
                </label>
                <select id="supplier_id" 
                        name="supplier_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('inventory.all_suppliers') }}</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="stock_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('inventory.stock_status') }}
                </label>
                <select id="stock_status" 
                        name="stock_status"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('inventory.all_statuses') }}</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>
                        {{ __('inventory.low_stock') }}
                    </option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>
                        {{ __('inventory.out_of_stock') }}
                    </option>
                    <option value="needs_reorder" {{ request('stock_status') == 'needs_reorder' ? 'selected' : '' }}>
                        {{ __('inventory.needs_reorder') }}
                    </option>
                    <option value="expiring_soon" {{ request('stock_status') == 'expiring_soon' ? 'selected' : '' }}>
                        {{ __('inventory.expiring_soon') }}
                    </option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>{{ __('inventory.filter') }}</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Stock Items Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('inventory.product') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('inventory.location') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('inventory.stock') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('inventory.available') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('inventory.status') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('inventory.stock_value') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('inventory.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($stockItems as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $item->variant->product->name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $item->variant->display_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $item->location->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">{{ $item->stock }}</span>
                                    @if($item->reserved > 0)
                                        <span class="text-xs text-orange-600 dark:text-orange-400">
                                            ({{ $item->reserved }} {{ __('inventory.reserved') }})
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium {{ $item->available_stock <= 0 ? 'text-red-600 dark:text-red-400' : ($item->available_stock <= 10 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                                    {{ $item->available_stock }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'in_stock' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'low_stock' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'out_of_stock' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        'needs_reorder' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'not_tracked' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$item->stock_status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $item->stock_status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <div class="font-medium">€{{ number_format($item->stock_value, 2) }}</div>
                                @if($item->cost_per_unit)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        €{{ number_format($item->cost_per_unit, 2) }}/{{ __('inventory.unit') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('stock.show', $item) }}" 
                                       class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ __('inventory.view') }}
                                    </a>
                                    <button onclick="adjustStock({{ $item->id }})" 
                                            class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                        {{ __('inventory.adjust') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                {{ __('inventory.no_stock_items_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($stockItems->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $stockItems->links() }}
            </div>
        @endif
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

<script>
let currentStockId = null;

function adjustStock(stockId) {
    currentStockId = stockId;
    document.getElementById('adjustStockModal').classList.remove('hidden');
}

function closeAdjustStockModal() {
    document.getElementById('adjustStockModal').classList.add('hidden');
    document.getElementById('adjustStockForm').reset();
    currentStockId = null;
}

document.getElementById('adjustStockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/stock/${currentStockId}/adjust`, {
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
</script>
@endsection

