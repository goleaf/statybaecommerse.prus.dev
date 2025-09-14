<div class="enhanced-live-search relative" 
     x-data="{ 
         showResults: @entangle('showResults'),
         showSuggestions: @entangle('showSuggestions'),
         query: @entangle('query'),
         isSearching: @entangle('isSearching'),
         selectedIndex: -1,
         results: @entangle('results'),
         suggestions: @entangle('suggestions'),
         showFilters: false,
         selectedCategory: @entangle('selectedCategory'),
         selectedBrand: @entangle('selectedBrand'),
         minPrice: @entangle('minPrice'),
         maxPrice: @entangle('maxPrice'),
         inStockOnly: @entangle('inStockOnly'),
         sortBy: @entangle('sortBy')
     }" 
     x-init="
         $watch('query', value => {
             if (value.length < {{ $minQueryLength }}) {
                 showResults = false;
                 if (value.length === 0) {
                     showSuggestions = true;
                 }
             }
         });
         
         // Close results when clicking outside
         $el.addEventListener('clickoutside', () => {
             showResults = false;
             showSuggestions = false;
             selectedIndex = -1;
             showFilters = false;
         });
         
         // Keyboard navigation
         $el.addEventListener('keydown', (e) => {
             const totalItems = showResults ? results.length : suggestions.length;
             
             if (e.key === 'ArrowDown') {
                 e.preventDefault();
                 selectedIndex = Math.min(selectedIndex + 1, totalItems - 1);
             } else if (e.key === 'ArrowUp') {
                 e.preventDefault();
                 selectedIndex = Math.max(selectedIndex - 1, -1);
             } else if (e.key === 'Enter') {
                 e.preventDefault();
                 if (selectedIndex >= 0) {
                     if (showResults) {
                         $wire.selectResult(results[selectedIndex]);
                     } else if (showSuggestions) {
                         $wire.selectSuggestion(suggestions[selectedIndex]);
                     }
                 }
             } else if (e.key === 'Escape') {
                 showResults = false;
                 showSuggestions = false;
                 selectedIndex = -1;
                 showFilters = false;
             }
         });
     ">
    
    <!-- Enhanced Search Input -->
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        
        <input
            wire:model.live.debounce.500ms="query"
            type="text"
            placeholder="{{ __('frontend.search.enhanced_placeholder') }}"
            class="block w-full pl-12 pr-20 py-4 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition-all duration-200"
            autocomplete="off"
            x-ref="searchInput"
        />
        
        <!-- Search Actions -->
        <div class="absolute inset-y-0 right-0 flex items-center gap-2 pr-3">
            <!-- Filters Toggle -->
            <button
                @click="showFilters = !showFilters"
                :class="showFilters ? 'bg-blue-100 text-blue-600' : 'text-gray-400 hover:text-gray-600'"
                type="button"
                class="p-2 rounded-lg transition-colors duration-200"
                title="{{ __('frontend.search.filters') }}"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
            </button>
            
            <!-- Loading Spinner -->
            <div wire:loading class="flex items-center">
                <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            
            <!-- Clear Button -->
            <div wire:loading.remove>
                <button
                    wire:click="clearQuery"
                    x-show="query.length > 0"
                    type="button"
                    class="p-2 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors duration-200"
                    title="{{ __('frontend.search.clear') }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Panel -->
    <div 
        x-show="showFilters"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-40 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg p-6"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('frontend.search.category') }}</label>
                <select wire:model.live="selectedCategory" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">{{ __('frontend.search.all_categories') }}</option>
                    @foreach(\App\Models\Category::where('is_visible', true)->get() as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Brand Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('frontend.search.brand') }}</label>
                <select wire:model.live="selectedBrand" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">{{ __('frontend.search.all_brands') }}</option>
                    @foreach(\App\Models\Brand::where('is_enabled', true)->get() as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Price Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('frontend.search.price_range') }}</label>
                <div class="flex gap-2">
                    <input wire:model.live="minPrice" type="number" placeholder="Min" class="flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <input wire:model.live="maxPrice" type="number" placeholder="Max" class="flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Sort By -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('frontend.search.sort_by') }}</label>
                <select wire:model.live="sortBy" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="relevance">{{ __('frontend.search.relevance') }}</option>
                    <option value="price_asc">{{ __('frontend.search.price_low_high') }}</option>
                    <option value="price_desc">{{ __('frontend.search.price_high_low') }}</option>
                    <option value="newest">{{ __('frontend.search.newest') }}</option>
                    <option value="rating">{{ __('frontend.search.rating') }}</option>
                </select>
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex flex-wrap gap-2">
                <span class="text-sm font-medium text-gray-700 mr-2">{{ __('frontend.search.quick_filters') }}:</span>
                
                <button wire:click="quickFilter('stock', 'in_stock')" 
                        :class="inStockOnly ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                        class="px-3 py-1 rounded-full text-xs font-medium transition-colors duration-200">
                    {{ __('frontend.search.in_stock_only') }}
                </button>
                
                <button wire:click="quickFilter('price_range', 'under_50')" 
                        class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium hover:bg-gray-200 transition-colors duration-200">
                    {{ __('frontend.search.under_50') }}
                </button>
                
                <button wire:click="quickFilter('price_range', '50_100')" 
                        class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium hover:bg-gray-200 transition-colors duration-200">
                    {{ __('frontend.search.50_100') }}
                </button>
                
                <button wire:click="quickFilter('price_range', '100_500')" 
                        class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium hover:bg-gray-200 transition-colors duration-200">
                    {{ __('frontend.search.100_500') }}
                </button>
                
                <button wire:click="resetFilters" 
                        class="px-3 py-1 bg-red-100 text-red-600 rounded-full text-xs font-medium hover:bg-red-200 transition-colors duration-200">
                    {{ __('frontend.search.clear_filters') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Enhanced Search Results Dropdown -->
    <div 
        x-show="showResults && (query.length >= {{ $minQueryLength }})"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-96 overflow-y-auto"
    >
        @if($isSearching)
            <!-- Enhanced Loading State -->
            <div class="flex items-center justify-center py-12">
                <div class="flex flex-col items-center space-y-3 text-gray-500">
                    <svg class="animate-spin h-8 w-8" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ __('frontend.search.searching') }}</span>
                    <span class="text-xs text-gray-400">{{ __('frontend.search.please_wait') }}</span>
                </div>
            </div>
        @elseif(count($results) > 0)
            <!-- Enhanced Search Results -->
            <div class="py-2">
                <!-- Results Header -->
                <div class="px-4 py-2 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-gray-900">
                            {{ __('frontend.search.results_for') }} "{{ $query }}"
                        </h3>
                        <span class="text-xs text-gray-500">
                            {{ count($results) }} {{ __('frontend.search.results_found') }}
                        </span>
                    </div>
                </div>

                @foreach($results as $index => $result)
                    <button
                        wire:click="selectResult({{ json_encode($result) }})"
                        class="w-full px-4 py-4 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none border-b border-gray-100 last:border-b-0"
                        :class="{ 'bg-gray-50': selectedIndex === {{ $index }} }"
                    >
                        <div class="flex items-center space-x-4">
                            <!-- Enhanced Result Image -->
                            <div class="flex-shrink-0">
                                @if($result['image'])
                                    <img 
                                        src="{{ $result['image'] }}" 
                                        alt="{{ $result['title'] }}"
                                        class="w-16 h-16 object-cover rounded-lg shadow-sm"
                                    />
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                        @if($result['type'] === 'product')
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        @elseif($result['type'] === 'category')
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                        @elseif($result['type'] === 'brand')
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Enhanced Result Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate">
                                            {{ $result['title'] }}
                                        </h3>
                                        
                                        @if($result['subtitle'])
                                            <p class="text-sm text-gray-600 truncate mt-1">
                                                {{ $result['subtitle'] }}
                                            </p>
                                        @endif
                                        
                                        @if($result['description'])
                                            <p class="text-xs text-gray-500 truncate mt-1">
                                                {{ Str::limit($result['description'], 80) }}
                                            </p>
                                        @endif
                                        
                                        <!-- Enhanced Type Badge -->
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($result['type'] === 'product') bg-blue-100 text-blue-800
                                                @elseif($result['type'] === 'category') bg-green-100 text-green-800
                                                @elseif($result['type'] === 'brand') bg-purple-100 text-purple-800
                                                @elseif($result['type'] === 'collection') bg-orange-100 text-orange-800
                                                @endif
                                            ">
                                                {{ __('frontend.search.type_' . $result['type']) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Price and Actions -->
                                    <div class="flex flex-col items-end space-y-2">
                                        @if(isset($result['formatted_price']))
                                            <span class="text-sm font-bold text-blue-600">
                                                {{ $result['formatted_price'] }}
                                            </span>
                                        @endif
                                        
                                        @if(isset($result['rating']))
                                            <div class="flex items-center gap-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-3 h-3 {{ $i <= $result['rating'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @endfor
                                                <span class="text-xs text-gray-500 ml-1">{{ $result['rating'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Arrow Icon -->
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
            
            <!-- Enhanced View All Results Link -->
            <div class="border-t border-gray-200 bg-gray-50">
                <a 
                    href="{{ route('localized.search', ['locale' => app()->getLocale(), 'q' => $query]) }}"
                    class="block w-full px-4 py-3 text-center text-sm font-medium text-blue-600 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none transition-colors duration-200"
                >
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ __('frontend.search.view_all_results') }}
                    </div>
                </a>
            </div>
        @elseif(strlen($query) >= $minQueryLength)
            <!-- Enhanced No Results -->
            <div class="px-4 py-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">
                    {{ __('frontend.search.no_results_found') }}
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    {{ __('frontend.search.try_different_keywords') }}
                </p>
                <div class="mt-4">
                    <button wire:click="clearQuery" 
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        {{ __('frontend.search.clear_search') }}
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Enhanced Suggestions Dropdown -->
    <div 
        x-show="showSuggestions && (query.length < {{ $minQueryLength }} || query.length === 0)"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-96 overflow-y-auto"
    >
        @if(count($suggestions) > 0)
            <!-- Enhanced Suggestions Header -->
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-900">
                        {{ __('frontend.search.suggestions') }}
                    </h3>
                    @if($enableRecentSearches)
                        <button
                            wire:click="clearRecentSearches"
                            wire:confirm="{{ __('translations.confirm_clear_recent_searches') }}"
                            type="button"
                            class="text-xs text-gray-500 hover:text-gray-700 transition-colors duration-200"
                        >
                            {{ __('frontend.search.clear_recent') }}
                        </button>
                    @endif
                </div>
            </div>
            
            <!-- Enhanced Suggestions List -->
            <div class="py-2">
                @foreach($suggestions as $index => $suggestion)
                    <button
                        wire:click="selectSuggestion({{ json_encode($suggestion) }})"
                        class="w-full px-4 py-3 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none transition-colors duration-200"
                        :class="{ 'bg-gray-50': selectedIndex === {{ $index }} }"
                    >
                        <div class="flex items-center space-x-3">
                            <!-- Enhanced Suggestion Icon -->
                            <div class="flex-shrink-0">
                                @if(isset($suggestion['is_recent']))
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                @elseif(isset($suggestion['is_popular']))
                                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Enhanced Suggestion Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-gray-900 truncate">
                                        {{ $suggestion['title'] }}
                                    </h3>
                                    @if(isset($suggestion['is_recent']))
                                        <span class="text-xs text-blue-600 font-medium">
                                            {{ __('frontend.search.recent') }}
                                        </span>
                                    @elseif(isset($suggestion['is_popular']))
                                        <span class="text-xs text-orange-600 font-medium">
                                            {{ __('frontend.search.popular') }}
                                        </span>
                                    @endif
                                </div>
                                
                                @if($suggestion['subtitle'])
                                    <p class="text-sm text-gray-500 truncate mt-1">
                                        {{ $suggestion['subtitle'] }}
                                    </p>
                                @endif
                            </div>
                            
                            <!-- Arrow Icon -->
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
            
            <!-- Enhanced Search Tips -->
            <div class="border-t border-gray-200 px-4 py-3 bg-gray-50">
                <div class="text-xs text-gray-500">
                    <div class="flex items-center justify-between">
                        <span>{{ __('frontend.search.tip_1') }}</span>
                        <span>{{ __('frontend.search.tip_2') }}</span>
                    </div>
                </div>
            </div>
        @else
            <!-- Enhanced No Suggestions -->
            <div class="px-4 py-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">
                    {{ __('frontend.search.no_suggestions') }}
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    {{ __('frontend.search.start_typing') }}
                </p>
            </div>
        @endif
    </div>
</div>
