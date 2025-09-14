<div class="live-dashboard" 
     x-data="{
         autoRefresh: @entangle('autoRefresh'),
         refreshInterval: @entangle('refreshInterval'),
         selectedMetrics: @entangle('selectedMetrics'),
         timeRange: @entangle('timeRange'),
         isRefreshing: false,
         lastUpdated: new Date()
     }"
     x-init="
         // Auto-refresh functionality
         let refreshInterval;
         
         $watch('autoRefresh', (value) => {
             if (value) {
                 refreshInterval = setInterval(() => {
                     $wire.refreshDashboard();
                     isRefreshing = true;
                     setTimeout(() => isRefreshing = false, 1000);
                     lastUpdated = new Date();
                 }, refreshInterval * 1000);
             } else {
                 clearInterval(refreshInterval);
             }
         });
         
         // Listen for external refresh events
         $wire.on('refresh-dashboard', () => {
             isRefreshing = true;
             setTimeout(() => isRefreshing = false, 1000);
             lastUpdated = new Date();
         });
     ">
    
    <!-- Dashboard Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    {{ __('translations.live_dashboard') }}
                </h2>
                <p class="text-gray-600 mt-1">
                    {{ __('translations.real_time_analytics') }}
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Time Range Selector -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">{{ __('translations.time_range') }}:</label>
                    <select wire:model.live="timeRange" 
                            class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="1h">{{ __('translations.last_hour') }}</option>
                        <option value="24h">{{ __('translations.last_24_hours') }}</option>
                        <option value="7d">{{ __('translations.last_7_days') }}</option>
                        <option value="30d">{{ __('translations.last_30_days') }}</option>
                    </select>
                </div>
                
                <!-- Auto Refresh Toggle -->
                <button wire:click="toggleAutoRefresh"
                        :class="autoRefresh ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors duration-200">
                    <svg class="w-4 h-4" :class="autoRefresh ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ __('translations.auto_refresh') }}
                </button>
                
                <!-- Manual Refresh -->
                <button wire:click="refreshDashboard"
                        :disabled="isRefreshing"
                        class="flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                    <svg class="w-4 h-4" :class="isRefreshing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ __('translations.refresh') }}
                </button>
            </div>
        </div>
        
        <!-- Last Updated -->
        <div class="mt-4 text-xs text-gray-500 flex items-center gap-2">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span x-text="'{{ __('translations.last_updated') }}: ' + lastUpdated.toLocaleTimeString()"></span>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @if(in_array('products', $selectedMetrics))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('translations.products') }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($this->realTimeStats['products']['total']) }}</p>
                        <p class="text-sm text-green-600 flex items-center gap-1 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            +{{ $this->realTimeStats['products']['new_today'] }} {{ __('translations.today') }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">{{ __('translations.featured') }}:</span>
                        <span class="font-semibold">{{ $this->realTimeStats['products']['featured'] }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('translations.low_stock') }}:</span>
                        <span class="font-semibold text-orange-600">{{ $this->realTimeStats['products']['low_stock'] }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if(in_array('orders', $selectedMetrics))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('translations.orders') }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($this->realTimeStats['orders']['total']) }}</p>
                        <p class="text-sm text-green-600 flex items-center gap-1 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            +{{ $this->realTimeStats['orders']['today'] }} {{ __('translations.today') }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">{{ __('translations.pending') }}:</span>
                        <span class="font-semibold text-yellow-600">{{ $this->realTimeStats['orders']['pending'] }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('translations.revenue') }}:</span>
                        <span class="font-semibold text-green-600">{{ \Illuminate\Support\Number::currency($this->realTimeStats['orders']['revenue'], 'EUR') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if(in_array('users', $selectedMetrics))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('translations.users') }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($this->realTimeStats['users']['total']) }}</p>
                        <p class="text-sm text-green-600 flex items-center gap-1 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            +{{ $this->realTimeStats['users']['new_today'] }} {{ __('translations.today') }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">{{ __('translations.active') }}:</span>
                        <span class="font-semibold text-green-600">{{ $this->realTimeStats['users']['active'] }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('translations.new_today') }}:</span>
                        <span class="font-semibold">{{ $this->realTimeStats['users']['new_today'] }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if(in_array('reviews', $selectedMetrics))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('translations.reviews') }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($this->realTimeStats['reviews']['total']) }}</p>
                        <p class="text-sm text-green-600 flex items-center gap-1 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            +{{ $this->realTimeStats['reviews']['today'] }} {{ __('translations.today') }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">{{ __('translations.avg_rating') }}:</span>
                        <span class="font-semibold text-yellow-600">{{ number_format($this->realTimeStats['reviews']['avg_rating'], 1) }}/5</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('translations.pending') }}:</span>
                        <span class="font-semibold text-orange-600">{{ $this->realTimeStats['reviews']['pending'] }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Live Activity Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    {{ __('translations.recent_orders') }}
                </h3>
                <a href="{{ route('orders.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    {{ __('translations.view_all') }}
                </a>
            </div>
            
            <div class="space-y-3">
                @forelse($this->liveActivity['recent_orders'] as $order)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">#{{ $order['id'] }} - {{ $order['user_name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $order['created_at'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ \Illuminate\Support\Number::currency($order['total'], 'EUR') }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                @if($order['status'] === 'completed') bg-green-100 text-green-800
                                @elseif($order['status'] === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($order['status']) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p>{{ __('translations.no_recent_orders') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div>
                    {{ __('translations.recent_reviews') }}
                </h3>
                <a href="{{ route('reviews.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    {{ __('translations.view_all') }}
                </a>
            </div>
            
            <div class="space-y-3">
                @forelse($this->liveActivity['recent_reviews'] as $review)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $review['product_name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $review['user_name'] }} • {{ $review['created_at'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3 h-3 {{ $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        <p>{{ __('translations.no_recent_reviews') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
            {{ __('translations.performance_metrics') }}
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($this->performanceMetrics['page_views']) }}</p>
                <p class="text-sm text-gray-600">{{ __('translations.page_views') }}</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $this->performanceMetrics['bounce_rate'] }}%</p>
                <p class="text-sm text-gray-600">{{ __('translations.bounce_rate') }}</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($this->performanceMetrics['avg_session_duration'] / 60, 1) }}m</p>
                <p class="text-sm text-gray-600">{{ __('translations.avg_session_duration') }}</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $this->performanceMetrics['conversion_rate'] }}%</p>
                <p class="text-sm text-gray-600">{{ __('translations.conversion_rate') }}</p>
            </div>
        </div>
    </div>
</div>
