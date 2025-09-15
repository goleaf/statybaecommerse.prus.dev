<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('admin.search_insights') }}
                </h3>
                <div class="flex items-center space-x-2">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="query"
                        placeholder="{{ __('admin.enter_search_query') }}"
                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                    @if($isLoading)
                        <div class="animate-spin h-4 w-4 border-2 border-blue-600 border-t-transparent rounded-full"></div>
                    @endif
                </div>
            </div>
        </x-slot>

        <div class="space-y-6">
            @if($hasQuery)
                <!-- Query Analysis -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                        <div class="text-sm font-medium text-blue-600 dark:text-blue-400">
                            {{ __('admin.word_count') }}
                        </div>
                        <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                            {{ $insights['query_analysis']['word_count'] ?? 0 }}
                        </div>
                    </div>
                    
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                        <div class="text-sm font-medium text-green-600 dark:text-green-400">
                            {{ __('admin.complexity_score') }}
                        </div>
                        <div class="text-2xl font-bold text-green-900 dark:text-green-100">
                            {{ number_format($insights['query_analysis']['complexity_score'] ?? 0, 1) }}/10
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                        <div class="text-sm font-medium text-purple-600 dark:text-purple-400">
                            {{ __('admin.language') }}
                        </div>
                        <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                            {{ strtoupper($insights['query_analysis']['language_detection'] ?? 'unknown') }}
                        </div>
                    </div>
                    
                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg">
                        <div class="text-sm font-medium text-orange-600 dark:text-orange-400">
                            {{ __('admin.intent') }}
                        </div>
                        <div class="text-2xl font-bold text-orange-900 dark:text-orange-100">
                            {{ ucfirst($insights['query_analysis']['intent_classification'] ?? 'general') }}
                        </div>
                    </div>
                </div>

                <!-- Search Trends -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('admin.search_trends') }}
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('admin.popularity_score') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($insights['search_trends']['popularity_score'] ?? 0, 1) }}%
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('admin.trend_direction') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ ucfirst($insights['search_trends']['trend_direction'] ?? 'stable') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('admin.search_frequency') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($insights['search_trends']['search_frequency'] ?? 0) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('admin.performance_metrics') }}
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('admin.response_time') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($insights['performance_metrics']['average_response_time'] ?? 0, 3) }}s
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('admin.cache_hit_rate') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ number_format(($insights['performance_metrics']['cache_hit_rate'] ?? 0) * 100, 1) }}%
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('admin.error_rate') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ number_format(($insights['performance_metrics']['error_rate'] ?? 0) * 100, 2) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                @if(!empty($recommendations['similar_searches']))
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('admin.similar_searches') }}
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach(array_slice($recommendations['similar_searches'], 0, 6) as $search)
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $search['query'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ number_format($search['similarity_score'] * 100, 1) }}% similarity
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($recommendations['trending_searches']))
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('admin.trending_searches') }}
                        </h4>
                        <div class="space-y-2">
                            @foreach(array_slice($recommendations['trending_searches'], 0, 5) as $trend)
                                <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $trend['query'] }}</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ number_format($trend['growth_rate'], 1) }}%
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                                            @if($trend['trend_direction'] === 'rising') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($trend['trend_direction'] === 'falling') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                            {{ ucfirst($trend['trend_direction']) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            @else
                <!-- Default State -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                        {{ __('admin.search_insights_title') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('admin.search_insights_description') }}
                    </p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
