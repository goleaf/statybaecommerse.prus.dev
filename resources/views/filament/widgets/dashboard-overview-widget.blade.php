<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        <div class="space-y-6">
            {{-- Key Metrics Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Total Revenue --}}
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Revenue</p>
                            <p class="text-3xl font-bold" id="total-revenue">€0</p>
                            <p class="text-blue-100 text-sm" id="revenue-change">+0% from last month</p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Total Orders --}}
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Total Orders</p>
                            <p class="text-3xl font-bold" id="total-orders">0</p>
                            <p class="text-green-100 text-sm" id="orders-change">+0% from last month</p>
                        </div>
                        <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Total Customers --}}
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Total Customers</p>
                            <p class="text-3xl font-bold" id="total-customers">0</p>
                            <p class="text-purple-100 text-sm" id="customers-change">+0% from last month</p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Total Products --}}
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Total Products</p>
                            <p class="text-3xl font-bold" id="total-products">0</p>
                            <p class="text-orange-100 text-sm" id="products-change">+0% from last month</p>
                        </div>
                        <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('filament.admin.resources.products.create') }}"
                       class="flex items-center justify-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                        <div class="text-center">
                            <svg class="h-8 w-8 text-blue-600 dark:text-blue-400 mx-auto mb-2" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Add Product</p>
                        </div>
                    </a>

                    <a href="{{ route('filament.admin.resources.orders.index') }}"
                       class="flex items-center justify-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                        <div class="text-center">
                            <svg class="h-8 w-8 text-green-600 dark:text-green-400 mx-auto mb-2" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <p class="text-sm font-medium text-green-900 dark:text-green-100">View Orders</p>
                        </div>
                    </a>

                    <a href="{{ route('filament.admin.resources.users.index') }}"
                       class="flex items-center justify-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                        <div class="text-center">
                            <svg class="h-8 w-8 text-purple-600 dark:text-purple-400 mx-auto mb-2" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                </path>
                            </svg>
                            <p class="text-sm font-medium text-purple-900 dark:text-purple-100">Manage Users</p>
                        </div>
                    </a>

                    <a href="#"
                       class="flex items-center justify-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors">
                        <div class="text-center">
                            <svg class="h-8 w-8 text-orange-600 dark:text-orange-400 mx-auto mb-2" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                            <p class="text-sm font-medium text-orange-900 dark:text-orange-100">Analytics</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </x-filament::section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Update metrics with real data
                // This would typically come from the widget's data method
                const metrics = {
                    revenue: 0,
                    orders: 0,
                    customers: 0,
                    products: 0
                };

                // Update the display
                document.getElementById('total-revenue').textContent = '€' + metrics.revenue.toLocaleString();
                document.getElementById('total-orders').textContent = metrics.orders.toLocaleString();
                document.getElementById('total-customers').textContent = metrics.customers.toLocaleString();
                document.getElementById('total-products').textContent = metrics.products.toLocaleString();
            });
        </script>
    @endpush
</x-filament-widgets::widget>



