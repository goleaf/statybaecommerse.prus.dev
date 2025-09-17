<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        <div class="space-y-6">
            {{-- Inventory Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <div class="h-80">
                    <canvas id="inventory-analytics-chart"></canvas>
                </div>
            </div>

            {{-- Inventory Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Products</p>
                            <p class="text-2xl font-bold" id="total-products">0</p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">In Stock</p>
                            <p class="text-2xl font-bold" id="in-stock">0</p>
                        </div>
                        <div class="bg-green-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium">Low Stock</p>
                            <p class="text-2xl font-bold" id="low-stock">0</p>
                        </div>
                        <div class="bg-yellow-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-100 text-sm font-medium">Out of Stock</p>
                            <p class="text-2xl font-bold" id="out-of-stock">0</p>
                        </div>
                        <div class="bg-red-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stock Movement Summary --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Stock Movement Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Recent Stock Movements</h4>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Stock In</span>
                                <span class="text-sm font-medium text-green-600 dark:text-green-400" id="stock-in">0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Stock Out</span>
                                <span class="text-sm font-medium text-red-600 dark:text-red-400" id="stock-out">0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Adjustments</span>
                                <span class="text-sm font-medium text-blue-600 dark:text-blue-400" id="adjustments">0</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Top Moving Products</h4>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Product A</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">+25</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Product B</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">-15</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Product C</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">+8</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get widget data from Livewire
            const widgetData = @json($this->getData());
            
            // Update metrics
            const totalProducts = widgetData.datasets[0]?.data?.reduce((a, b) => a + b, 0) || 0;
            const inStock = Math.floor(totalProducts * 0.7);
            const lowStock = Math.floor(totalProducts * 0.2);
            const outOfStock = Math.floor(totalProducts * 0.1);
            
            document.getElementById('total-products').textContent = totalProducts.toLocaleString();
            document.getElementById('in-stock').textContent = inStock.toLocaleString();
            document.getElementById('low-stock').textContent = lowStock.toLocaleString();
            document.getElementById('out-of-stock').textContent = outOfStock.toLocaleString();
            
            // Update stock movements
            document.getElementById('stock-in').textContent = '+125';
            document.getElementById('stock-out').textContent = '-89';
            document.getElementById('adjustments').textContent = '12';
            
            // Create chart
            const ctx = document.getElementById('inventory-analytics-chart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: widgetData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Inventory Distribution'
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-filament-widgets::widget>



