<x-layouts.base :title="$category->name">
    <div class="max-w-6xl mx-auto px-4 py-10 space-y-8">
        <header class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-6 shadow-sm">
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $category->name }}</h1>
            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">{!! $category->description !!}</div>
            @if ($category->children->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($category->children as $child)
                        <a href="{{ route('frontend.categories.show', $child) }}" class="px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-sm">{{ $child->name }}</a>
                    @endforeach
                </div>
            @endif
        </header>

        <section>
            <h2 class="text-2xl font-semibold mb-4">{{ __('Products in this category') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse ($products as $product)
                    <div class="p-4 border border-gray-200 rounded-xl bg-white shadow-sm dark:bg-gray-900 dark:border-white/10">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->brand?->name }}</p>
                        <div class="mt-2 text-primary-600 font-semibold">{{ app_money_format($product->sale_price ?? $product->price ?? 0) }}</div>
                        <a href="{{ route('frontend.products.show', $product) }}" class="mt-3 inline-flex items-center text-sm text-primary-700 hover:text-primary-800">{{ __('View product') }}</a>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('There are no products in this category yet.') }}</p>
                @endforelse
            </div>
            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </section>
    </div>
</x-layouts.base>
