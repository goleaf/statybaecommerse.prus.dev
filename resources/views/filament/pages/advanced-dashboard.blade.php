<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        <!-- Quick Stats Cards -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                {{ __('Total Products') }}
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ number_format($this->systemStats['total_products']) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                {{ __('Total Orders') }}
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ number_format($this->systemStats['total_orders']) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                {{ __('Total Customers') }}
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ number_format($this->systemStats['total_customers']) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                {{ __('Total Revenue') }}
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ app_money_format($this->systemStats['total_revenue']) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    @if($this->systemStats['pending_orders'] > 0 || $this->systemStats['low_stock_items'] > 0)
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Attention Required') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($this->systemStats['pending_orders'] > 0)
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                    {{ __('Pending Orders') }}
                                </h4>
                                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                    {{ trans_choice('You have :count pending order|You have :count pending orders', $this->systemStats['pending_orders'], ['count' => $this->systemStats['pending_orders']]) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($this->systemStats['low_stock_items'] > 0)
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    {{ __('Low Stock Alert') }}
                                </h4>
                                <p class="text-sm text-red-700 dark:text-red-300">
                                    {{ trans_choice(':count product is running low|:count products are running low', $this->systemStats['low_stock_items'], ['count' => $this->systemStats['low_stock_items']]) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Widgets Section -->
    <div class="space-y-8">
        @foreach($this->getWidgets() as $widget)
            @livewire($widget)
        @endforeach
    </div>
</x-filament-panels::page>
