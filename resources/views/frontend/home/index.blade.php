<x-layouts.base title="{{ __('Home') }}">
    <div class="max-w-7xl mx-auto px-4 py-10 space-y-12">
        <section>
            <h1 class="text-3xl font-semibold mb-4">{{ __('Featured products') }}</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse ($featuredProducts as $product)
                    <div class="p-4 border border-gray-200 rounded-xl bg-white shadow-sm dark:bg-gray-900 dark:border-white/10">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->brand?->name }}</p>
                        <p class="mt-2 text-primary-600 font-semibold">{{ app_money_format($product->sale_price ?? $product->price ?? 0) }}</p>
                        <a class="mt-3 inline-flex items-center text-sm text-primary-700 hover:text-primary-800"
                           href="{{ route('frontend.products.show', $product) }}">{{ __('View product') }}</a>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No featured products available.') }}</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold mb-4">{{ __('Latest arrivals') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @forelse ($latestProducts as $product)
                    <div class="p-4 rounded-xl border border-gray-200 bg-white dark:bg-gray-900 dark:border-white/10">
                        <h3 class="text-base font-semibold">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->brand?->name }}</p>
                        <div class="mt-2 text-primary-600 font-medium">{{ app_money_format($product->sale_price ?? $product->price ?? 0) }}</div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Products will appear here soon.') }}</p>
                @endforelse
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
                <h2 class="text-2xl font-semibold mb-4">{{ __('Popular categories') }}</h2>
                <ul class="space-y-3">
                    @forelse ($popularCategories as $category)
                        <li class="flex items-center justify-between">
                            <a href="{{ route('frontend.categories.show', $category) }}" class="text-primary-700 hover:text-primary-800">
                                {{ $category->name }}
                            </a>
                            <span class="text-sm text-gray-500">{{ trans_choice('{0}No products|{1}1 product|[2,*]:count products', $category->products_count, ['count' => $category->products_count]) }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500 dark:text-gray-400">{{ __('No categories available yet.') }}</li>
                    @endforelse
                </ul>
            </div>
            <div>
                <h2 class="text-2xl font-semibold mb-4">{{ __('Brands customers love') }}</h2>
                <ul class="space-y-3">
                    @forelse ($popularBrands as $brand)
                        <li class="flex items-center justify-between">
                            <a href="{{ route('frontend.brands.show', $brand) }}" class="text-primary-700 hover:text-primary-800">
                                {{ $brand->name }}
                            </a>
                            <span class="text-sm text-gray-500">{{ trans_choice('{0}No products|{1}1 product|[2,*]:count products', $brand->products_count, ['count' => $brand->products_count]) }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500 dark:text-gray-400">{{ __('No brands available yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold mb-4">{{ __('Active discounts') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @forelse ($activeDiscounts as $discount)
                    <article class="p-4 rounded-xl border border-dashed border-primary-200 bg-primary-50 dark:bg-primary-900/10">
                        <h3 class="text-lg font-semibold text-primary-900 dark:text-primary-200">{{ $discount->name }}</h3>
                        <p class="text-sm text-primary-700 dark:text-primary-300">{{ $discount->description }}</p>
                        <p class="mt-2 text-sm text-primary-600">{{ __('Value: :value', ['value' => $discount->value]) }}</p>
                    </article>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('There are no active discounts right now.') }}</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.base>
