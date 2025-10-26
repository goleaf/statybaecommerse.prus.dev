<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::card>
            <form wire:submit="$refresh">
                {{ $this->form }}
            </form>
        </x-filament::card>

        <div class="space-y-6">
            @if($reportType === 'sales')
                @php
                    $salesData = $this->getSalesData(); // Pull the enriched sales payload once so we can safely read normalized keys.
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-filament::card>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary-600">{{ \Illuminate\Support\Number::currency(data_get($salesData, 'totalRevenue', 0), current_currency(), app()->getLocale()) }}</div>
                            <div class="text-sm text-gray-500">{{ __('admin.reports.total_revenue') }}</div>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-success-600">{{ number_format(data_get($salesData, 'totalOrders', 0)) }}</div>
                            <div class="text-sm text-gray-500">{{ __('admin.reports.total_orders') }}</div>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-info-600">{{ \Illuminate\Support\Number::currency(data_get($salesData, 'avgOrderValue', 0), current_currency(), app()->getLocale()) }}</div>
                            <div class="text-sm text-gray-500">{{ __('admin.reports.avg_order_value') }}</div>
                        </div>
                    </x-filament::card>
                </div>

                <x-filament::card>
                    <h3 class="text-lg font-semibold mb-4">{{ __('admin.reports.daily_sales') }}</h3>
                    <div class="h-64">
                        <div class="flex items-center justify-center h-full text-gray-500">
                            {{ __('admin.reports.chart_placeholder') }}
                        </div>
                    </div>
                </x-filament::card>

            @elseif($reportType === 'products')
                @php
                    $productData = $this->getProductData(); // Capture the normalized product stats so repeated lookups stay consistent.
                @endphp
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <x-filament::card>
                        <h3 class="text-lg font-semibold mb-4">{{ __('admin.reports.top_selling_products') }}</h3>
                        <div class="space-y-3">
                            @foreach(data_get($productData, 'topProducts', []) as $product)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <div class="font-medium">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold">{{ $product->order_items_count }} {{ __('admin.table.orders') }}</div>
                                        <div class="text-sm text-gray-500">{{ \Illuminate\Support\Number::currency($product->order_items_sum_total, current_currency(), app()->getLocale()) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <h3 class="text-lg font-semibold mb-4">{{ __('admin.reports.low_stock_products') }}</h3>
                        <div class="space-y-3">
                            @foreach(data_get($productData, 'lowStockProducts', []) as $product)
                                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                    <div>
                                        <div class="font-medium">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-red-600">{{ $product->stock_quantity }} {{ __('admin.table.in_stock') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-filament::card>
                </div>

            @elseif($reportType === 'customers')
                @php
                    $customerData = $this->getCustomerData(); // Snapshot customer metrics with defaults to guard against missing keys.
                @endphp
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <x-filament::card>
                        <h3 class="text-lg font-semibold mb-4">{{ __('admin.reports.new_customers') }}</h3>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-success-600">{{ number_format(data_get($customerData, 'newCustomers', 0)) }}</div>
                            <div class="text-sm text-gray-500">{{ __('admin.reports.new_registrations') }}</div>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <h3 class="text-lg font-semibold mb-4">{{ __('admin.reports.top_customers') }}</h3>
                        <div class="space-y-3">
                            @foreach(data_get($customerData, 'topCustomers', []) as $customer)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <div class="font-medium">{{ $customer->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $customer->email }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold">{{ $customer->orders_count }} {{ __('admin.table.orders') }}</div>
                                        <div class="text-sm text-gray-500">{{ \Illuminate\Support\Number::currency($customer->orders_sum_total, current_currency(), app()->getLocale()) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-filament::card>
                </div>

            @elseif($reportType === 'inventory')
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    @php
                        $inventoryData = $this->getInventoryData(); // Read the enriched inventory payload for consistent rendering.
                    @endphp
                    <x-filament::card>
                        <div class="text-center">
                            <div class="text-2xl font-bold">{{ number_format(data_get($inventoryData, 'totalProducts', 0)) }}</div>
                            <div class="text-sm text-gray-500">{{ __('admin.reports.total_products') }}</div>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-success-600">{{ number_format(data_get($inventoryData, 'inStock', 0)) }}</div>
                            <div class="text-sm text-gray-500">{{ __('admin.reports.in_stock') }}</div>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-warning-600">{{ number_format(data_get($inventoryData, 'lowStock', 0)) }}</div>
                            <div class="text-sm text-gray-500">{{ __('admin.reports.low_stock') }}</div>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-danger-600">{{ number_format(data_get($inventoryData, 'outOfStock', 0)) }}</div>
                            <div class="text-sm text-gray-500">{{ __('admin.reports.out_of_stock') }}</div>
                        </div>
                    </x-filament::card>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>

