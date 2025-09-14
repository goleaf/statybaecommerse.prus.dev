<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ __('translations.advanced_filters') }}
        </h3>
        <button
                wire:click="clearFilters"
                wire:confirm="{{ __('translations.confirm_clear_search_filters') }}"
                class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            {{ __('translations.clear_all') }}
        </button>
    </div>

    <div class="space-y-6">
        <!-- Search -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('translations.search') }}
            </label>
            <input
                   wire:model.live.debounce.300ms="search"
                   type="text"
                   placeholder="{{ __('translations.search_products') }}"
                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Categories -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('translations.categories') }}
            </label>
            <div class="max-h-32 overflow-y-auto space-y-2">
                @foreach ($availableCategories as $category)
                    <label class="flex items-center">
                        <input
                               wire:model.live="categories"
                               type="checkbox"
                               value="{{ $category->id }}"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            {{ $category->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Brands -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('translations.brands') }}
            </label>
            <div class="max-h-32 overflow-y-auto space-y-2">
                @foreach ($availableBrands as $brand)
                    <label class="flex items-center">
                        <input
                               wire:model.live="brands"
                               type="checkbox"
                               value="{{ $brand->id }}"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            {{ $brand->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Price Range -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('translations.price_range') }}
            </label>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('translations.min_price') }}</label>
                    <input
                           wire:model.live.debounce.500ms="minPrice"
                           type="number"
                           min="0"
                           step="0.01"
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('translations.max_price') }}</label>
                    <input
                           wire:model.live.debounce.500ms="maxPrice"
                           type="number"
                           min="0"
                           step="0.01"
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
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

        <!-- Additional Filters -->
        <div class="space-y-3">
            <label class="flex items-center">
                <input
                       wire:model.live="inStock"
                       type="checkbox"
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ __('translations.in_stock_only') }}
                </span>
            </label>

            <label class="flex items-center">
                <input
                       wire:model.live="onSale"
                       type="checkbox"
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ __('translations.on_sale_only') }}
                </span>
            </label>
        </div>

        <!-- Sort Options -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('translations.sort_by') }}
            </label>
            <div class="grid grid-cols-2 gap-2">
                <select
                        wire:model.live="sortBy"
                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="created_at">{{ __('translations.newest') }}</option>
                    <option value="name">{{ __('translations.name') }}</option>
                    <option value="price">{{ __('translations.price') }}</option>
                    <option value="stock_quantity">{{ __('translations.stock') }}</option>
                    <option value="updated_at">{{ __('translations.recently_updated') }}</option>
                </select>
                <select
                        wire:model.live="sortDirection"
                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="asc">{{ __('translations.ascending') }}</option>
                    <option value="desc">{{ __('translations.descending') }}</option>
                </select>
            </div>
        </div>
    </div>
</div>
