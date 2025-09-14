<x-filament-panels::page>
    <div class="space-y-6">
        <!-- System Statistics -->
        <x-filament::section>
            <x-slot name="heading">
                {{ __('translations.system_statistics') }}
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($this->getSystemStats() as $key => $value)
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    {{ __('translations.' . $key) }}
                                </p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ number_format($value) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <!-- Block Performance -->
        <x-filament::section>
            <x-slot name="heading">
                {{ __('translations.block_performance') }}
            </x-slot>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('translations.block_name') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('translations.status') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('translations.total_requests') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('translations.avg_ctr') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('translations.avg_conversion') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($this->getBlockPerformance() as $block)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $block['title'] }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $block['name'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($block['is_active'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            {{ __('translations.active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                            {{ __('translations.inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ number_format($block['total_requests']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ number_format($block['avg_ctr'] * 100, 2) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ number_format($block['avg_conversion'] * 100, 2) }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('translations.no_data_available') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <!-- Quick Actions -->
        <x-filament::section>
            <x-slot name="heading">
                {{ __('translations.quick_actions') }}
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-filament::button
                    wire:click="clearCache"
                    wire:confirm="{{ __('translations.confirm_clear_recommendation_cache') }}"
                    color="danger"
                    icon="heroicon-o-trash"
                    size="lg"
                    class="w-full"
                >
                    {{ __('translations.clear_recommendation_cache') }}
                </x-filament::button>

                <x-filament::button
                    wire:click="optimizeSystem"
                    color="success"
                    icon="heroicon-o-arrow-path"
                    size="lg"
                    class="w-full"
                >
                    {{ __('translations.optimize_recommendation_system') }}
                </x-filament::button>

                <x-filament::button
                    href="{{ route('filament.admin.resources.recommendation-configs.index') }}"
                    color="primary"
                    icon="heroicon-o-cog-6-tooth"
                    size="lg"
                    class="w-full"
                >
                    {{ __('translations.manage_configurations') }}
                </x-filament::button>
            </div>
        </x-filament::section>

        <!-- System Information -->
        <x-filament::section>
            <x-slot name="heading">
                {{ __('translations.system_information') }}
            </x-slot>

            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                            {{ __('translations.available_algorithms') }}
                        </h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• {{ __('translations.content_based_filtering') }}</li>
                            <li>• {{ __('translations.collaborative_filtering') }}</li>
                            <li>• {{ __('translations.hybrid_recommendation') }}</li>
                            <li>• {{ __('translations.popularity_based') }}</li>
                            <li>• {{ __('translations.trending_products') }}</li>
                            <li>• {{ __('translations.cross_sell') }}</li>
                            <li>• {{ __('translations.up_sell') }}</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                            {{ __('translations.features') }}
                        </h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• {{ __('translations.realtime_caching') }}</li>
                            <li>• {{ __('translations.user_behavior_tracking') }}</li>
                            <li>• {{ __('translations.performance_analytics') }}</li>
                            <li>• {{ __('translations.ab_testing_support') }}</li>
                            <li>• {{ __('translations.dynamic_algorithm_weights') }}</li>
                            <li>• {{ __('translations.multilanguage_support') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
