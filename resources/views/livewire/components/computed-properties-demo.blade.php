<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('Livewire Computed Properties Demo') }}</h1>
        
        <!-- Controls -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Filters & Controls') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Time Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Time Filter') }}</label>
                    <select wire:model.live="filter" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="week">{{ __('Last Week') }}</option>
                        <option value="month">{{ __('Last Month') }}</option>
                        <option value="year">{{ __('Last Year') }}</option>
                        <option value="all">{{ __('All Time') }}</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Category') }}</label>
                    <select wire:model.live="selectedCategory" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">{{ __('All Categories') }}</option>
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
                        {{ __('Show only expensive products (>€100)') }}
                    </label>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-blue-600">{{ __('Total Users') }}</h3>
                <p class="text-2xl font-bold text-blue-900">{{ $stats['users'] }}</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-green-600">{{ __('Total Products') }}</h3>
                <p class="text-2xl font-bold text-green-900">{{ $stats['products'] }}</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-purple-600">{{ __('Categories') }}</h3>
                <p class="text-2xl font-bold text-purple-900">{{ $stats['categories'] }}</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-yellow-600">{{ __('Brands') }}</h3>
                <p class="text-2xl font-bold text-yellow-900">{{ $stats['brands'] }}</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-red-600">{{ __('Reviews') }}</h3>
                <p class="text-2xl font-bold text-red-900">{{ $stats['reviews'] }}</p>
            </div>
        </div>

        <!-- Analytics Data -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Filtered Analytics') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg p-3">
                    <h3 class="text-sm font-medium text-gray-600">{{ __('Filtered Products') }}</h3>
                    <p class="text-xl font-bold text-gray-900">{{ $analyticsData['total_products'] }}</p>
                </div>
                <div class="bg-white rounded-lg p-3">
                    <h3 class="text-sm font-medium text-gray-600">{{ __('Average Price') }}</h3>
                    <p class="text-xl font-bold text-gray-900">€{{ number_format($analyticsData['average_price'], 2) }}</p>
                </div>
                <div class="bg-white rounded-lg p-3">
                    <h3 class="text-sm font-medium text-gray-600">{{ __('Total Value') }}</h3>
                    <p class="text-xl font-bold text-gray-900">€{{ number_format($analyticsData['total_value'], 2) }}</p>
                </div>
                <div class="bg-white rounded-lg p-3">
                    <h3 class="text-sm font-medium text-gray-600">{{ __('Price Range') }}</h3>
                    <p class="text-sm text-gray-900">
                        €{{ number_format($analyticsData['price_range']['min'], 2) }} - 
                        €{{ number_format($analyticsData['price_range']['max'], 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Filtered Products -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Filtered Products') }}</h2>
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
                        <p class="text-gray-500">{{ __('No products found with current filters') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Expensive Analytics (Persistent Cache) -->
        <div class="bg-blue-50 rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Top Products & Brands (Persistent Cache)') }}</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Products -->
                <div>
                    <h3 class="font-medium text-gray-900 mb-3">{{ __('Top Products by Reviews') }}</h3>
                    <div class="space-y-2">
                        @foreach($expensiveAnalytics['top_products'] as $product)
                            <div class="flex items-center space-x-3 bg-white rounded-lg p-3">
                                @if($product['image'])
                                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" 
                                         class="w-10 h-10 object-cover rounded">
                                @endif
                                <div class="flex-1">
                                    <h4 class="font-medium text-sm text-gray-900">{{ $product['name'] }}</h4>
                                    <p class="text-xs text-gray-600">{{ $product['reviews_count'] }} {{ __('reviews') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Brands -->
                <div>
                    <h3 class="font-medium text-gray-900 mb-3">{{ __('Top Brands by Products') }}</h3>
                    <div class="space-y-2">
                        @foreach($expensiveAnalytics['top_brands'] as $brand)
                            <div class="flex items-center space-x-3 bg-white rounded-lg p-3">
                                @if($brand['image'])
                                    <img src="{{ $brand['image'] }}" alt="{{ $brand['name'] }}" 
                                         class="w-10 h-10 object-cover rounded">
                                @endif
                                <div class="flex-1">
                                    <h4 class="font-medium text-sm text-gray-900">{{ $brand['name'] }}</h4>
                                    <p class="text-xs text-gray-600">{{ $brand['products_count'] }} {{ __('products') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Stats (Global Cache) -->
        <div class="bg-green-50 rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Global Site Statistics (Global Cache)') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-900">{{ $globalSiteStats['total_products'] }}</p>
                    <p class="text-sm text-green-700">{{ __('Products') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-900">{{ $globalSiteStats['total_categories'] }}</p>
                    <p class="text-sm text-green-700">{{ __('Categories') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-900">{{ $globalSiteStats['total_brands'] }}</p>
                    <p class="text-sm text-green-700">{{ __('Brands') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-900">{{ number_format($globalSiteStats['average_rating'], 1) }}</p>
                    <p class="text-sm text-green-700">{{ __('Avg Rating') }}</p>
                </div>
            </div>
            <p class="text-xs text-green-600 mt-2 text-center">
                {{ __('Last updated') }}: {{ \Carbon\Carbon::parse($globalSiteStats['last_updated'])->format('M d, Y H:i') }}
            </p>
        </div>

        <!-- Summary Report -->
        <div class="bg-yellow-50 rounded-lg p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Summary Report') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">{{ __('Current Filters') }}</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>{{ __('Time Filter') }}: {{ ucfirst($summaryReport['filter_applied']) }}</li>
                        <li>{{ __('Category') }}: {{ $summaryReport['category_filter'] ?: __('All') }}</li>
                        <li>{{ __('Expensive Only') }}: {{ $summaryReport['expensive_only'] ? __('Yes') : __('No') }}</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">{{ __('Performance Metrics') }}</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>{{ __('Filtered Results') }}: {{ $summaryReport['filtered_count'] }}</li>
                        <li>{{ __('Percentage of Total') }}: {{ $summaryReport['percentage_of_total'] }}%</li>
                        <li>{{ __('Average Price') }}: €{{ number_format($summaryReport['average_price_vs_global'], 2) }}</li>
                    </ul>
                </div>
            </div>
            <div class="mt-4 p-3 bg-white rounded-lg">
                <h4 class="font-medium text-gray-900 mb-2">{{ __('Computed Properties Benefits') }}</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>✅ {{ __('Automatic caching during request lifecycle') }}</li>
                    <li>✅ {{ __('Reduced database queries through intelligent caching') }}</li>
                    <li>✅ {{ __('Memory optimization with computed results') }}</li>
                    <li>✅ {{ __('Persistent caching across requests when needed') }}</li>
                    <li>✅ {{ __('Global caching for shared data across instances') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
