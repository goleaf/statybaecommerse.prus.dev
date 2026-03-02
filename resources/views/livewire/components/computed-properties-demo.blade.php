<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('ui.livewire_computed_properties_demo') }}</h1>
        
        <!-- Controls -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('ui.filters_controls') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Time Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('ui.time_filter') }}</label>
                    <select wire:model.live="filter" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="week">{{ __('ui.last_week') }}</option>
                        <option value="month">{{ __('ui.last_month') }}</option>
                        <option value="year">{{ __('ui.last_year') }}</option>
                        <option value="all">{{ __('ui.all_time') }}</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.category') }}</label>
                    <select wire:model.live="selectedCategory" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">{{ __('messages.all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Expensive Products Toggle -->
                <div class="flex items-center">
                    <input type="checkbox" wire:model.live="showExpensiveProducts" id="expensive" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="expensive" class="ml-2 block text-sm text-gray-900">
                        {{ __('ui.show_only_expensive_products_100') }}
                    </label>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-blue-600">{{ __('ui.total_users') }}</h3>
                <p class="text-2xl font-bold text-blue-900">{{ $stats['users'] }}</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-green-600">{{ __('ui.total_products') }}</h3>
                <p class="text-2xl font-bold text-green-900">{{ $stats['products'] }}</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-purple-600">{{ __('messages.categories') }}</h3>
                <p class="text-2xl font-bold text-purple-900">{{ $stats['categories'] }}</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-yellow-600">{{ __('messages.brands') }}</h3>
                <p class="text-2xl font-bold text-yellow-900">{{ $stats['brands'] }}</p>
            </div>
        </div>

        <!-- Filtered Products -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('ui.filtered_products') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($filteredProducts as $product)
                    <div class="bg-white border rounded-lg p-4 hover:shadow-md transition-shadow">
                        @if($product->getFirstMediaUrl('images'))
                            <img src="{{ $product->getFirstMediaUrl('images') }}" alt="{{ $product->name }}" 
                                 class="w-full h-32 object-cover rounded mb-3">
                        @endif
                        <h3 class="font-medium text-gray-900 mb-2">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-600 mb-2">{{ $product->brand?->name }}</p>
                        <p class="text-lg font-bold text-green-600">€{{ number_format($product->price, 2) }}</p>
                        <p class="text-xs text-gray-500 mt-2">{{ $product->created_at->format('M d, Y') }}</p>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">{{ __('ui.no_products_found_with_current_filters') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Global Stats (Global Cache) -->
        <div class="bg-green-50 rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('ui.global_site_statistics_global_cache') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-900">{{ $globalSiteStats['total_products'] }}</p>
                    <p class="text-sm text-green-700">{{ __('messages.products') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-900">{{ $globalSiteStats['total_categories'] }}</p>
                    <p class="text-sm text-green-700">{{ __('messages.categories') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-900">{{ $globalSiteStats['total_brands'] }}</p>
                    <p class="text-sm text-green-700">{{ __('messages.brands') }}</p>
                </div>
            </div>
            <p class="text-xs text-green-600 mt-2 text-center">
                {{ __('messages.last_updated') }}: {{ \Carbon\Carbon::parse($globalSiteStats['last_updated'])->format('M d, Y H:i') }}
            </p>
        </div>

        <!-- Summary Report -->
        <div class="bg-yellow-50 rounded-lg p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('ui.summary_report') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">{{ __('ui.current_filters') }}</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>{{ __('ui.time_filter') }}: {{ ucfirst($summaryReport['filter_applied']) }}</li>
                        <li>{{ __('messages.category') }}: {{ $summaryReport['category_filter'] ?: __('ui.all') }}</li>
                        <li>{{ __('ui.expensive_only') }}: {{ $summaryReport['expensive_only'] ? __('ui.yes') : __('ui.no') }}</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">{{ __('ui.metrics') }}</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>{{ __('ui.filtered_results') }}: {{ $summaryReport['filtered_count'] }}</li>
                        <li>{{ __('ui.percentage_of_total') }}: {{ $summaryReport['percentage_of_total'] }}%</li>
                    </ul>
                </div>
            </div>
            <div class="mt-4 p-3 bg-white rounded-lg">
                <h4 class="font-medium text-gray-900 mb-2">{{ __('ui.computed_properties_benefits') }}</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>✅ {{ __('ui.automatic_caching_during_request_lifecycle') }}</li>
                    <li>✅ {{ __('ui.reduced_database_queries_through_intelligent_caching') }}</li>
                    <li>✅ {{ __('ui.memory_optimization_with_computed_results') }}</li>
                    <li>✅ {{ __('ui.persistent_caching_across_requests_when_needed') }}</li>
                    <li>✅ {{ __('ui.global_caching_for_shared_data_across_instances') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
