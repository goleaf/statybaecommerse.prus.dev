<div class="advanced-search relative" 
     x-data="{
         showResults: @entangle('showResults'),
         showFilters: @entangle('showFilters'),
         query: @entangle('query'),
         isSearching: @entangle('isSearching'),
         results: @entangle('results'),
         selectedCategories: @entangle('selectedCategories'),
         selectedBrands: @entangle('selectedBrands'),
         selectedCollections: @entangle('selectedCollections'),
         minPrice: @entangle('minPrice'),
         maxPrice: @entangle('maxPrice'),
         inStockOnly: @entangle('inStockOnly'),
         sortBy: @entangle('sortBy')
     }" 
     x-init="
         $watch('query', value => {
             if (value.length < {{ $minQueryLength }}) {
                 showResults = false;
             }
         });
         
         // Close results when clicking outside
         $el.addEventListener('clickoutside', () => {
             showResults = false;
         });
     ">
    
    {{-- Search Input with Filters Toggle --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        
        <input
            wire:model.live.debounce.300ms="query"
            type="text"
            placeholder="{{ __('frontend.search.advanced_placeholder') }}"
            class="block w-full pl-10 pr-20 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
            autocomplete="off"
            x-ref="searchInput"
        />
        
        {{-- Filters Toggle Button --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
            <button
                wire:click="toggleFilters"
                wire:confirm="{{ __('translations.confirm_toggle_filters') }}"
                type="button"
                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-gray-600 focus:outline-none"
                :class="{ 'text-blue-600 bg-blue-100': showFilters, 'hover:bg-gray-100': !showFilters }"
                :title="showFilters ? '{{ __('frontend.search.hide_filters') }}' : '{{ __('frontend.search.show_filters') }}'"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                </svg>
            </button>
        </div>
    </div>
    
    {{-- Advanced Filters Panel --}}
    <div x-show="showFilters" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         x-cloak
         class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg p-4">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Categories Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('frontend.search.categories') }}
                </label>
                <div class="space-y-2 max-h-32 overflow-y-auto">
                    @foreach($availableCategories as $category)
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                wire:model="selectedCategories"
                                value="{{ $category['id'] }}"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="ml-2 text-sm text-gray-700">{{ $category['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            
            {{-- Brands Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('frontend.search.brands') }}
                </label>
                <div class="space-y-2 max-h-32 overflow-y-auto">
                    @foreach($availableBrands as $brand)
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                wire:model="selectedBrands"
                                value="{{ $brand['id'] }}"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="ml-2 text-sm text-gray-700">{{ $brand['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            
            {{-- Collections Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('frontend.search.collections') }}
                </label>
                <div class="space-y-2 max-h-32 overflow-y-auto">
                    @foreach($availableCollections as $collection)
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                wire:model="selectedCollections"
                                value="{{ $collection['id'] }}"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="ml-2 text-sm text-gray-700">{{ $collection['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            
            {{-- Price Range Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('frontend.search.price_range') }}
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <input
                        type="number"
                        wire:model.live.debounce.500ms="minPrice"
                        placeholder="{{ __('frontend.search.min_price') }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <input
                        type="number"
                        wire:model.live.debounce.500ms="maxPrice"
                        placeholder="{{ __('frontend.search.max_price') }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
            </div>
            
            {{-- Stock Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('frontend.search.availability') }}
                </label>
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        wire:model="inStockOnly"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="ml-2 text-sm text-gray-700">{{ __('frontend.search.in_stock_only') }}</span>
                </label>
            </div>
            
            {{-- Sort Options --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('frontend.search.sort_by') }}
                </label>
                <select
                    wire:model.live="sortBy"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="relevance">{{ __('frontend.search.relevance') }}</option>
                    <option value="name">{{ __('frontend.search.name') }}</option>
                    <option value="price_low">{{ __('frontend.search.price_low_to_high') }}</option>
                    <option value="price_high">{{ __('frontend.search.price_high_to_low') }}</option>
                </select>
            </div>
        </div>
        
        {{-- Filter Actions --}}
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
            <button
                wire:click="clearFilters"
                wire:confirm="{{ __('translations.confirm_clear_search_filters') }}"
                type="button"
                class="text-sm text-gray-500 hover:text-gray-700 focus:outline-none"
            >
                {{ __('frontend.search.clear_filters') }}
            </button>
            
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">
                    {{ count($results) }} {{ __('frontend.search.results') }}
                </span>
            </div>
        </div>
    </div>
    
    {{-- Search Results --}}
    <div x-show="showResults && results.length > 0"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-40 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-96 overflow-y-auto"
         x-cloak>
        
        <div class="py-2">
            @foreach($results as $result)
                <button
                    wire:click="selectResult({{ json_encode($result) }})"
                    class="w-full px-4 py-3 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none border-b border-gray-100 last:border-b-0"
                >
                    <div class="flex items-center space-x-3">
                        {{-- Result Image --}}
                        <div class="flex-shrink-0">
                            @if($result['image'])
                                <img src="{{ $result['image'] }}" 
                                     alt="{{ $result['title'] }}"
                                     class="w-12 h-12 rounded-lg object-cover bg-gray-100" />
                            @else
                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Result Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900 truncate">
                                    {{ $result['title'] }}
                                </h3>
                                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                    {{ __('frontend.search.type_' . $result['type']) }}
                                </span>
                            </div>
                            
                            @if($result['subtitle'])
                                <p class="text-sm text-gray-500 truncate">
                                    {{ $result['subtitle'] }}
                                </p>
                            @endif
                            
                            @if(isset($result['formatted_price']))
                                <p class="text-sm font-medium text-green-600">
                                    {{ $result['formatted_price'] }}
                                </p>
                            @endif
                        </div>
                        
                        {{-- Arrow Icon --}}
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>
    </div>
    
    {{-- No Results --}}
    <div x-show="showResults && results.length === 0 && !isSearching"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-40 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg p-8 text-center"
         x-cloak>
        
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">
            {{ __('frontend.search.no_results') }}
        </h3>
        <p class="mt-1 text-sm text-gray-500">
            {{ __('frontend.search.try_different_keywords') }}
        </p>
    </div>
</div>
