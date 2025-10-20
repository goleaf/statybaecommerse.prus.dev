<x-layouts.base title="{{ __('Brands') }}">
    <div class="max-w-6xl mx-auto px-4 py-10">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Browse by brand') }}</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($brands as $brand)
                <a href="{{ route('frontend.brands.show', $brand) }}" class="block p-4 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-gray-900 shadow-sm hover:border-primary-300">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $brand->name }}</h2>
                    @if ($brand->description)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::limit(strip_tags($brand->description), 140) }}</p>
                    @endif
                    <p class="mt-2 text-sm text-gray-500">{{ trans_choice('{0}No products|{1}1 product|[2,*]:count products', $brand->products_count, ['count' => $brand->products_count]) }}</p>
                </a>
            @empty
                <p class="text-gray-500 dark:text-gray-400">{{ __('No brands available yet.') }}</p>
            @endforelse
        </div>
    </div>
</x-layouts.base>
