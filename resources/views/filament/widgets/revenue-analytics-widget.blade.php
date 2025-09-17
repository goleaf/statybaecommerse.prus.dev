<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        <div class="space-y-6">
            {{-- Revenue Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <div class="h-80">
                    <canvas id="revenue-analytics-chart"></canvas>
                </div>
            </div>

            {{-- Revenue Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-100 text-sm font-medium">Total Revenue</p>
                            <p class="text-3xl font-bold" id="total-revenue">€0</p>
                            <p class="text-emerald-100 text-sm" id="revenue-change">+0% from last month</p>
                        </div>
                        <div class="bg-emerald-400 bg-opacity-30 rounded-full p-3">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Average Order Value</p>
                            <p class="text-3xl font-bold" id="avg-order-value">€0</p>
                            <p class="text-blue-100 text-sm" id="aov-change">+0% from last month</p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Conversion Rate</p>
                            <p class="text-3xl font-bold" id="conversion-rate">0%</p>
                            <p class="text-purple-100 text-sm" id="conversion-change">+0% from last month</p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Revenue Breakdown --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue Breakdown</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">By Payment Method</h4>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Credit Card</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white"
                                      id="cc-revenue">€0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">PayPal</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white"
                                      id="paypal-revenue">€0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Bank Transfer</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white"
                                      id="bank-revenue">€0</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">By Product Category</h4>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Electronics</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white"
                                      id="electronics-revenue">€0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Clothing</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white"
                                      id="clothing-revenue">€0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Home & Garden</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white"
                                      id="home-revenue">€0</span>
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
                const totalRevenue = widgetData.datasets[0]?.data?.reduce((a, b) => a + b, 0) || 0;
                const avgOrderValue = totalRevenue > 0 ? (totalRevenue / 100).toFixed(2) : 0;
                const conversionRate = 2.5; // Example

                document.getElementById('total-revenue').textContent = '€' + totalRevenue.toLocaleString();
                document.getElementById('avg-order-value').textContent = '€' + avgOrderValue;
                document.getElementById('conversion-rate').textContent = conversionRate + '%';

                // Update breakdown
                document.getElementById('cc-revenue').textContent = '€' + (totalRevenue * 0.6).toLocaleString();
                document.getElementById('paypal-revenue').textContent = '€' + (totalRevenue * 0.3).toLocaleString();
                document.getElementById('bank-revenue').textContent = '€' + (totalRevenue * 0.1).toLocaleString();

                document.getElementById('electronics-revenue').textContent = '€' + (totalRevenue * 0.4)
            .toLocaleString();
                document.getElementById('clothing-revenue').textContent = '€' + (totalRevenue * 0.35).toLocaleString();
                document.getElementById('home-revenue').textContent = '€' + (totalRevenue * 0.25).toLocaleString();

                // Create chart
                const ctx = document.getElementById('revenue-analytics-chart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: widgetData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Revenue Analytics'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '€' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-filament-widgets::widget>



