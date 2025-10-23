<x-layouts.base :title="$product->name">
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-10">
        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-6 shadow-sm">
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $product->brand?->name }}</p>
            <div class="mt-4 text-2xl font-semibold text-primary-600">{{ app_money_format($product->sale_price ?? $product->price ?? 0) }}</div>
            <div class="mt-4 prose dark:prose-invert max-w-none">{!! $product->description !!}</div>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($product->categories as $category)
                    <a href="{{ route('frontend.categories.show', $category) }}" class="px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-sm">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </section>

        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-6 shadow-sm">
            <h2 class="text-2xl font-semibold mb-4">{{ __('Customer reviews') }}</h2>
            <div class="space-y-4">
                @forelse ($product->reviews as $review)
                    <article class="border border-gray-200 dark:border-white/10 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $review->reviewer_name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Rating: :rating/5', ['rating' => $review->rating]) }}</p>
                            </div>
                            <time class="text-xs text-gray-400" datetime="{{ $review->created_at }}">{{ $review->created_at?->format('Y-m-d') }}</time>
                        </div>
                        <h3 class="mt-2 text-lg font-semibold">{{ $review->title }}</h3>
                        <p class="mt-2 text-gray-700 dark:text-gray-300">{{ $review->content }}</p>
                    </article>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Be the first to review this product.') }}</p>
                @endforelse
            </div>
        </section>

        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-6 shadow-sm">
            <h2 class="text-2xl font-semibold mb-4">{{ __('Write a review') }}</h2>
            <form method="POST" action="{{ route('frontend.products.add-review', $product) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="rating">{{ __('Rating') }}</label>
                    <input type="number" id="rating" name="rating" min="1" max="5" required value="{{ old('rating', 5) }}"
                           class="mt-1 w-24 rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('rating')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="title">{{ __('Title') }}</label>
                    <input id="title" name="title" value="{{ old('title') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('title')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="content">{{ __('Review') }}</label>
                    <textarea id="content" name="content" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800" required>{{ old('content') }}</textarea>
                    @error('content')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('Submit review') }}</button>
            </form>
        </section>

        @if ($relatedProducts->isNotEmpty())
            <section>
                <h2 class="text-2xl font-semibold mb-4">{{ __('You might also like') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($relatedProducts as $related)
                        <div class="p-4 border border-gray-200 rounded-xl bg-white shadow-sm dark:bg-gray-900 dark:border-white/10">
                            <h3 class="text-lg font-semibold">{{ $related->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $related->brand?->name }}</p>
                            <div class="mt-2 text-primary-600 font-semibold">{{ app_money_format($related->sale_price ?? $related->price ?? 0) }}</div>
                            <a href="{{ route('frontend.products.show', $related) }}" class="mt-3 inline-flex items-center text-sm text-primary-700 hover:text-primary-800">{{ __('View product') }}</a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.base>
