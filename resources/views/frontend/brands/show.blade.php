<x-layouts.base :title="$brand->name">
    <div class="max-w-6xl mx-auto px-4 py-10 space-y-8">
        <header class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-6 shadow-sm">
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $brand->name }}</h1>
            @if ($brand->website)
                <p class="mt-2 text-sm">
                    <a href="{{ $brand->website }}" class="text-primary-700 hover:text-primary-800" target="_blank" rel="noopener">{{ $brand->website }}</a>
                </p>
            @endif
            @if ($brand->description)
                <div class="mt-4 text-sm text-gray-600 dark:text-gray-300">{!! $brand->description !!}</div>
            @endif
        </header>

        <section>
            <h2 class="text-2xl font-semibold mb-4">{{ __('Products from this brand') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse ($products as $product)
                    <div class="p-4 border border-gray-200 rounded-xl bg-white shadow-sm dark:bg-gray-900 dark:border-white/10">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</h3>
                        <div class="mt-2 text-primary-600 font-semibold">{{ app_money_format($product->sale_price ?? $product->price ?? 0) }}</div>
                        <a href="{{ route('frontend.products.show', $product) }}" class="mt-3 inline-flex items-center text-sm text-primary-700 hover:text-primary-800">{{ __('View product') }}</a>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('This brand does not have any products yet.') }}</p>
                @endforelse
            </div>
            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </section>
    </div>
</x-layouts.base>
