<div class="space-y-6">
    <!-- Enhanced Clear Filters Button -->
    <div class="flex items-center justify-between">
        <button
                wire:click="clearFilters"
                wire:confirm="{{ __('translations.confirm_clear_search_filters') ?? 'Are you sure you want to clear all filters?' }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white text-sm font-semibold rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                </path>
            </svg>
            {{ __('translations.clear_all') ?? 'Clear All' }}
        </button>
    </div>

    <div class="space-y-6">
        <!-- Enhanced Search -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                {{ __('translations.search') ?? 'Search' }}
            </label>
            <div class="relative">
                <input
                       wire:model.live.debounce.300ms="search"
                       type="text"
                       placeholder="{{ __('translations.search_products') ?? 'Search products...' }}"
                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-200">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Enhanced Categories -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
                {{ __('translations.categories') ?? 'Categories' }}
            </label>
            <div class="max-h-40 overflow-y-auto space-y-2 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                @foreach ($availableCategories as $category)
                    <label
                           class="flex items-center p-2 rounded-lg hover:bg-white dark:hover:bg-gray-600 transition-colors duration-200 cursor-pointer">
                        <input
                               wire:model.live="categories"
                               type="checkbox"
                               value="{{ $category->id }}"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $category->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Enhanced Brands -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                    </path>
                </svg>
                {{ __('translations.brands') ?? 'Brands' }}
            </label>
            <div class="max-h-40 overflow-y-auto space-y-2 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                @foreach ($availableBrands as $brand)
                    <label
                           class="flex items-center p-2 rounded-lg hover:bg-white dark:hover:bg-gray-600 transition-colors duration-200 cursor-pointer">
                        <input
                               wire:model.live="brands"
                               type="checkbox"
                               value="{{ $brand->id }}"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $brand->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Enhanced Price Range -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                    </path>
                </svg>
                {{ __('translations.price_range') ?? 'Price Range' }}
            </label>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                               class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('translations.min_price') ?? 'Min Price' }}</label>
                        <div class="relative">
                            <input
                                   wire:model.live.debounce.500ms="minPrice"
                                   type="number"
                                   min="0"
                                   step="0.01"
                                   class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm transition-all duration-200">
                            <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-sm">€</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label
                               class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('translations.max_price') ?? 'Max Price' }}</label>
                        <div class="relative">
                            <input
                                   wire:model.live.debounce.500ms="maxPrice"
                                   type="number"
                                   min="0"
                                   step="0.01"
                                   class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm transition-all duration-200">
                            <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-sm">€</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attributes -->
        @foreach ($availableAttributes as $attribute)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ $attribute->name }}
                </label>
                <div class="max-h-32 overflow-y-auto space-y-2">
                    @foreach ($attribute->values as $value)
                        <label class="flex items-center">
                            <input
                                   wire:model.live="selectedAttributes.{{ $attribute->id }}"
                                   type="checkbox"
                                   value="{{ $value->id }}"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ $value->value }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Enhanced Additional Filters -->
        <div class="space-y-4">
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0014 13v6l-4-2v-4a1 1 0 00-.293-.707L3.293 6.707A1 1 0 013 6V4z">
                        </path>
                    </svg>
                    {{ __('translations.quick_filters') ?? 'Quick Filters' }}
                </h4>
                <div class="space-y-3">
                    <label
                           class="flex items-center p-2 rounded-lg hover:bg-white dark:hover:bg-gray-600 transition-colors duration-200 cursor-pointer">
                        <input
                               wire:model.live="inStock"
                               type="checkbox"
                               class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring-2 focus:ring-green-200 focus:ring-opacity-50">
                        <span
                              class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('translations.in_stock_only') ?? 'In Stock Only' }}
                        </span>
                    </label>

                    <label
                           class="flex items-center p-2 rounded-lg hover:bg-white dark:hover:bg-gray-600 transition-colors duration-200 cursor-pointer">
                        <input
                               wire:model.live="onSale"
                               type="checkbox"
                               class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring-2 focus:ring-red-200 focus:ring-opacity-50">
                        <span
                              class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                </path>
                            </svg>
                            {{ __('translations.on_sale_only') ?? 'On Sale Only' }}
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Enhanced Sort Options -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                </svg>
                {{ __('translations.sort_by') ?? 'Sort By' }}
            </label>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                               class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('translations.sort_field') ?? 'Field' }}</label>
                        <select
                                wire:model.live="sortBy"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm transition-all duration-200">
                            <option value="created_at">{{ __('translations.newest') ?? 'Newest' }}</option>
                            <option value="name">{{ __('translations.name') ?? 'Name' }}</option>
                            <option value="price">{{ __('translations.price') ?? 'Price' }}</option>
                            <option value="stock_quantity">{{ __('translations.stock') ?? 'Stock' }}</option>
                            <option value="updated_at">{{ __('translations.recently_updated') ?? 'Recently Updated' }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                               class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('translations.order') ?? 'Order' }}</label>
                        <select
                                wire:model.live="sortDirection"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm transition-all duration-200">
                            <option value="asc">{{ __('translations.ascending') ?? 'Ascending' }}</option>
                            <option value="desc">{{ __('translations.descending') ?? 'Descending' }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
