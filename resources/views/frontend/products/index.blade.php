<x-layouts.base title="{{ __('Products') }}">
    <div class="max-w-7xl mx-auto px-4 py-10 space-y-8">
        <section class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-white/10 p-6">
            <form method="GET" action="{{ route('frontend.products.search') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="q">{{ __('Search products') }}</label>
                    <input id="q" name="q" value="{{ $activeFilters['search'] ?? '' }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800"
                           placeholder="{{ __('Search by name or SKU') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="category">{{ __('Category') }}</label>
                    <select id="category" name="category" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(($activeFilters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="brand">{{ __('Brand') }}</label>
                    <select id="brand" name="brand" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        <option value="">{{ __('All brands') }}</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->slug }}" @selected(($activeFilters['brand'] ?? '') === $brand->slug)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="sort">{{ __('Sort by') }}</label>
                    <select id="sort" name="sort" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        <option value="">{{ __('Most recent') }}</option>
                        <option value="price-asc" @selected(($activeFilters['sort'] ?? '') === 'price-asc')>{{ __('Price: low to high') }}</option>
                        <option value="price-desc" @selected(($activeFilters['sort'] ?? '') === 'price-desc')>{{ __('Price: high to low') }}</option>
                        <option value="newest" @selected(($activeFilters['sort'] ?? '') === 'newest')>{{ __('Newest first') }}</option>
                    </select>
                </div>
                <div class="md:col-span-4 flex items-center gap-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('Apply filters') }}</button>
                    <a href="{{ route('frontend.products.index') }}" class="text-sm text-gray-500 hover:text-gray-700">{{ __('Reset') }}</a>
                </div>
            </form>
        </section>

        @isset($currentCategory)
            <div class="p-4 bg-primary-50 border border-primary-200 rounded-lg dark:bg-primary-900/10 dark:border-primary-700">
                <p class="text-sm text-primary-700 dark:text-primary-200">
                    {{ __('Showing products in :category', ['category' => $currentCategory->name]) }}
                </p>
            </div>
        @endisset

        @isset($currentBrand)
            <div class="p-4 bg-primary-50 border border-primary-200 rounded-lg dark:bg-primary-900/10 dark:border-primary-700">
                <p class="text-sm text-primary-700 dark:text-primary-200">
                    {{ __('Filtered by brand :brand', ['brand' => $currentBrand->name]) }}
                </p>
            </div>
        @endisset

        <section>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse ($products as $product)
                    <div class="p-4 border border-gray-200 rounded-xl bg-white shadow-sm dark:bg-gray-900 dark:border-white/10">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->brand?->name }}</p>
                        <div class="mt-2 text-primary-600 font-semibold">{{ app_money_format($product->sale_price ?? $product->price ?? 0) }}</div>
                        <a href="{{ route('frontend.products.show', $product) }}" class="mt-3 inline-flex items-center text-sm text-primary-700 hover:text-primary-800">{{ __('View details') }}</a>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No products found for the selected filters.') }}</p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </section>
    </div>
</x-layouts.base>
