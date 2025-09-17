<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-600 via-purple-600 to-blue-600 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6">
                {{ __('translations.live_demo_title') }}
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-white/90 max-w-3xl mx-auto">
                {{ __('translations.live_demo_subtitle') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2">
                    <span class="text-sm font-medium">{{ __('translations.real_time_updates') }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2">
                    <span class="text-sm font-medium">{{ __('translations.enhanced_performance') }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2">
                    <span class="text-sm font-medium">{{ __('translations.modern_design') }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Dashboard Demo -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    {{ __('translations.live_dashboard') }}
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ __('translations.live_dashboard_description') }}
                </p>
            </div>
            
            <livewire:components.live-dashboard />
        </div>
    </section>

    <!-- Enhanced Search Demo -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    {{ __('translations.enhanced_search') }}
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ __('translations.enhanced_search_description') }}
                </p>
            </div>
            
            <div class="max-w-4xl mx-auto">
                <livewire:components.enhanced-live-search />
            </div>
        </div>
    </section>

    <!-- Live Inventory Tracker Demo -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    {{ __('translations.live_inventory_tracker') }}
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ __('translations.live_inventory_description') }}
                </p>
            </div>
            
            <livewire:components.live-inventory-tracker />
        </div>
    </section>

    <!-- Performance Features -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    {{ __('translations.performance_features') }}
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ __('translations.performance_features_description') }}
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Computed Properties -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ __('translations.computed_properties') }}
                    </h3>
                    <p class="text-gray-600 mb-4">
                        {{ __('translations.computed_properties_description') }}
                    </p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• {{ __('translations.intelligent_caching') }}</li>
                        <li>• {{ __('translations.reduced_queries') }}</li>
                        <li>• {{ __('translations.automatic_optimization') }}</li>
                    </ul>
                </div>

                <!-- Real-time Updates -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ __('translations.real_time_updates') }}
                    </h3>
                    <p class="text-gray-600 mb-4">
                        {{ __('translations.real_time_updates_description') }}
                    </p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• {{ __('translations.auto_refresh') }}</li>
                        <li>• {{ __('translations.live_polling') }}</li>
                        <li>• {{ __('translations.instant_updates') }}</li>
                    </ul>
                </div>

                <!-- Enhanced UX -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ __('translations.enhanced_ux') }}
                    </h3>
                    <p class="text-gray-600 mb-4">
                        {{ __('translations.enhanced_ux_description') }}
                    </p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• {{ __('translations.modern_design') }}</li>
                        <li>• {{ __('translations.smooth_animations') }}</li>
                        <li>• {{ __('translations.responsive_layout') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Technical Specifications -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    {{ __('translations.technical_specifications') }}
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ __('translations.technical_specifications_description') }}
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Performance Metrics -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('translations.performance_metrics') }}
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">{{ __('translations.page_load_time') }}</span>
                            <span class="font-semibold text-green-600">< 200ms</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">{{ __('translations.database_queries') }}</span>
                            <span class="font-semibold text-green-600">-60%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">{{ __('translations.cache_hit_rate') }}</span>
                            <span class="font-semibold text-green-600">95%+</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">{{ __('translations.memory_usage') }}</span>
                            <span class="font-semibold text-green-600">-40%</span>
                        </div>
                    </div>
                </div>

                <!-- Features List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('translations.implemented_features') }}
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">{{ __('translations.live_dashboard') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">{{ __('translations.enhanced_search') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">{{ __('translations.inventory_tracker') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">{{ __('translations.computed_properties') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">{{ __('translations.real_time_updates') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">{{ __('translations.advanced_caching') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16 bg-gradient-to-br from-blue-600 via-purple-600 to-blue-600 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                {{ __('translations.ready_to_experience') }}
            </h2>
            <p class="text-xl mb-8 text-white/90">
                {{ __('translations.ready_to_experience_description') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ localized_route('home') }}" 
                   class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-50 transition-colors duration-200">
                    {{ __('translations.explore_homepage') }}
                </a>
                <a href="{{ localized_route('products.index') }}" 
                   class="bg-white/10 backdrop-blur-sm text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/20 transition-colors duration-200">
                    {{ __('translations.browse_products') }}
                </a>
            </div>
        </div>
    </section>
</div>
