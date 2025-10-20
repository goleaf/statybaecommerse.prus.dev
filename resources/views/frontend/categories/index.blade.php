<x-layouts.base title="{{ __('Categories') }}">
    <div class="max-w-5xl mx-auto px-4 py-10">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Shop by category') }}</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($categories as $category)
                <a href="{{ route('frontend.categories.show', $category) }}" class="block p-4 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-gray-900 shadow-sm hover:border-primary-300">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $category->name }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::limit(strip_tags($category->description), 120) }}</p>
                    <p class="mt-2 text-sm text-gray-500">{{ trans_choice('{0}No products|{1}1 product|[2,*]:count products', $category->products_count, ['count' => $category->products_count]) }}</p>
                </a>
            @empty
                <p class="text-gray-500 dark:text-gray-400">{{ __('No categories available yet.') }}</p>
            @endforelse
        </div>
    </div>
</x-layouts.base>
