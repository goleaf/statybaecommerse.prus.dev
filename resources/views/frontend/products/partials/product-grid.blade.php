@props([
    'products',
    'emptyMessage' => __('No products were found for your selection.'),
])

<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @forelse ($products as $product)
        <article class="group flex flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
            <a href="{{ route('frontend.products.show', $product) }}" class="flex flex-1 flex-col">
                <div class="relative aspect-square overflow-hidden bg-gray-100">
                    <img
                        src="{{ $product->thumbnail ?: 'https://via.placeholder.com/600x600.png?text=Product' }}"
                        alt="{{ $product->name }}"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy"
                    >
                    @if ($product->sale_price && $product->sale_price < $product->price)
                        <span class="absolute left-4 top-4 inline-flex items-center rounded-full bg-rose-500/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                            {{ __('Sale') }}
                        </span>
                    @elseif($product->is_featured)
                        <span class="absolute left-4 top-4 inline-flex items-center rounded-full bg-indigo-500/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                            {{ __('Featured') }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-1 flex-col gap-3 p-5">
                    <div class="flex items-center justify-between text-xs font-medium uppercase tracking-wide text-gray-500">
                        <span>{{ optional($product->brand)->name }}</span>
                        <span class="flex items-center gap-1 text-amber-500">
                            <x-untitledui-star class="h-4 w-4" />
                            <span>{{ number_format($product->average_rating, 1) }}</span>
                        </span>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 line-clamp-2 group-hover:text-indigo-600">
                        {{ $product->name }}
                    </h3>

                    <p class="text-sm text-gray-600 line-clamp-3">
                        {{ strip_tags($product->short_description ?? $product->description) }}
                    </p>

                    <div class="mt-auto flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-semibold text-gray-900">{{ $product->formatted_price }}</span>
                            @if ($product->sale_price && $product->sale_price < $product->price)
                                <span class="text-sm font-medium text-gray-400 line-through">{{ app_money_format($product->price) }}</span>
                            @endif
                        </div>
                        <x-untitledui-arrow-up-right class="h-5 w-5 text-indigo-500 transition group-hover:translate-x-1" />
                    </div>
                </div>
            </a>
        </article>
    @empty
        <div class="col-span-full rounded-3xl border border-dashed border-gray-300 bg-white/60 p-12 text-center">
            <x-untitledui-search-sm class="mx-auto h-10 w-10 text-gray-400" />
            <p class="mt-4 text-lg font-semibold text-gray-900">{{ __('Nothing to show yet') }}</p>
            <p class="mt-2 text-sm text-gray-600">{{ $emptyMessage }}</p>
        </div>
    @endforelse
</div>

@if ($products instanceof \Illuminate\Contracts\Pagination\Paginator)
    <div class="mt-10">
        {{ $products->onEachSide(1)->withQueryString()->links() }}
    </div>
@endif
