@php
    $isPaginator = $products instanceof \Illuminate\Contracts\Pagination\Paginator;
@endphp

<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($products as $product)
        <article class="group flex flex-col rounded-xl bg-white/80 p-5 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-1 hover:shadow-md dark:bg-slate-900/70 dark:ring-slate-800">
            <a href="{{ route('frontend.products.show', $product) }}" class="flex flex-1 flex-col space-y-3">
                <div class="aspect-video w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-slate-800">
                    @if ($product->thumbnail)
                        <img src="{{ $product->thumbnail }}" alt="{{ $product->trans('name') ?? $product->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-sm text-gray-400">
                            {{ __('Image coming soon') }}
                        </div>
                    @endif
                </div>
                <div class="space-y-1">
                    <p class="text-xs uppercase tracking-wide text-blue-600 dark:text-blue-300">
                        {{ optional($product->brand)?->trans('name') ?? optional($product->brand)->name ?? __('Independent brand') }}
                    </p>
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300">
                        {{ $product->trans('name') ?? $product->name }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                        {{ strip_tags($product->trans('short_description') ?? $product->short_description ?? $product->trans('description') ?? $product->description) }}
                    </p>
                </div>
                <div class="mt-auto flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                    <span>{{ __('In stock') }}</span>
                    @if ($product->price)
                        <span class="text-base font-semibold text-gray-900 dark:text-white">
                            €{{ number_format((float) $product->price, 2) }}
                        </span>
                    @endif
                </div>
            </a>
        </article>
    @empty
        <p class="col-span-full rounded-lg bg-white/80 p-6 text-center text-sm text-gray-600 ring-1 ring-gray-100 dark:bg-slate-900/60 dark:text-gray-300 dark:ring-slate-800">
            {{ $emptyMessage ?? __('No products match your filters. Try adjusting your search or browse other categories.') }}
        </p>
    @endforelse
</div>

@if ($isPaginator && $products->hasPages())
    <div class="mt-8">
        {{ $products->withQueryString()->links() }}
    </div>
@endif
