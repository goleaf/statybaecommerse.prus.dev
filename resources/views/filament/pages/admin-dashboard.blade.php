<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ __('admin.dashboard.welcome.title') }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        {{ __('admin.dashboard.welcome.description') }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('admin.dashboard.welcome.date') }}
                    </p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ now()->format('Y-m-d H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('admin.dashboard.quick_actions.title') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ \App\Filament\Resources\OrderResource::getUrl('index') }}"
                   class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-shopping-bag class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            {{ __('admin.dashboard.quick_actions.orders') }}
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">
                            {{ __('admin.dashboard.quick_actions.manage_orders') }}
                        </p>
                    </div>
                </a>

                <a href="{{ \App\Filament\Resources\ProductResource::getUrl('index') }}"
                   class="flex items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-cube class="h-8 w-8 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-900 dark:text-green-100">
                            {{ __('admin.dashboard.quick_actions.products') }}
                        </p>
                        <p class="text-xs text-green-600 dark:text-green-400">
                            {{ __('admin.dashboard.quick_actions.manage_products') }}
                        </p>
                    </div>
                </a>

                <a href="{{ \App\Filament\Resources\CampaignResource::getUrl('index') }}"
                   class="flex items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-megaphone class="h-8 w-8 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-900 dark:text-purple-100">
                            {{ __('admin.dashboard.quick_actions.campaigns') }}
                        </p>
                        <p class="text-xs text-purple-600 dark:text-purple-400">
                            {{ __('admin.dashboard.quick_actions.manage_campaigns') }}
                        </p>
                    </div>
                </a>

                <a href="{{ \App\Filament\Resources\EnumValueResource::getUrl('index') }}"
                   class="flex items-center p-4 bg-gray-50 dark:bg-gray-900/20 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900/30 transition-colors">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-cog-6-tooth class="h-8 w-8 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ __('admin.dashboard.quick_actions.system') }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ __('admin.dashboard.quick_actions.manage_system') }}
                        </p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('admin.dashboard.recent_activity.title') }}
            </h2>
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-shopping-bag class="h-5 w-5 text-blue-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ __('admin.dashboard.recent_activity.new_order') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('admin.dashboard.recent_activity.order_created') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-cube class="h-5 w-5 text-green-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ __('admin.dashboard.recent_activity.new_product') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('admin.dashboard.recent_activity.product_created') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-megaphone class="h-5 w-5 text-purple-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ __('admin.dashboard.recent_activity.new_campaign') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('admin.dashboard.recent_activity.campaign_created') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('admin.dashboard.system_status.title') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('admin.dashboard.system_status.database') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('admin.dashboard.system_status.connected') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('admin.dashboard.system_status.cache') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('admin.dashboard.system_status.operational') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('admin.dashboard.system_status.queue') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('admin.dashboard.system_status.running') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
