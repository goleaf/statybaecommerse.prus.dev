@props([
    'products',
    'emptyMessage' => __('No products found for your selection.'),
])

@php
    $isPaginator = $products instanceof \Illuminate\Contracts\Pagination\Paginator;
@endphp

@if($products->count() > 0)
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($products as $product)
            @php
                // Use ProductImage model instead of MediaLibrary
                $primaryImage = $product->relationLoaded('primaryImage') 
                    ? $product->primaryImage 
                    : $product->primaryImage()->first();
                if (!$primaryImage) {
                    $primaryImage = $product->images()->ordered()->first();
                }
                $imageUrl = $primaryImage ? $primaryImage->url : asset('images/placeholder-product.png');
                $priceRecord = $product->prices->first();
                $priceAmount = $priceRecord->amount ?? $product->price;
                $currencySymbol = $priceRecord?->currency?->symbol ?? $priceRecord?->currency?->code ?? '€';
                $compareAmount = $priceRecord?->compare_amount ?? $product->compare_price;
                $shortDescription = $product->short_description ?? $product->summary ?? '';
            @endphp

            <article class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
                <a href="{{ route('frontend.products.show', $product) }}" class="relative block aspect-[4/3] overflow-hidden">
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $product->name }}"
                        class="h-full w-full object-cover transition duration-300 ease-out hover:scale-105"
                        loading="lazy"
                    >
                </a>

                <div class="flex flex-1 flex-col space-y-3 p-5">
                    <div class="flex flex-col gap-1">
                        @if($product->brand)
                            <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $product->brand->name }}</span>
                        @endif

                        <h3 class="text-lg font-semibold text-gray-900">
                            <a href="{{ route('frontend.products.show', $product) }}" class="hover:text-indigo-600">
                                {{ $product->name }}
                            </a>
                        </h3>

                        @if($shortDescription)
                            <p class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($shortDescription), 110) }}</p>
                        @endif
                    </div>

                    <div class="mt-auto flex items-center justify-between">
                        <div class="flex items-baseline gap-2">
                            @if($priceAmount !== null)
                                <span class="text-xl font-bold text-gray-900">{{ $currencySymbol }}{{ number_format((float) $priceAmount, 2) }}</span>
                            @endif

                            @if($compareAmount && $priceAmount !== null && (float) $compareAmount > (float) $priceAmount)
                                <span class="text-sm text-gray-500 line-through">{{ $currencySymbol }}{{ number_format((float) $compareAmount, 2) }}</span>
                            @endif
                        </div>

                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                            {{ __('View product') }}
                        </span>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    @if($isPaginator && $products->hasPages())
        <div class="mt-8">
            {{ $products->onEachSide(1)->links() }}
        </div>
    @endif
@else
    <p class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-sm text-gray-600">
        {{ $emptyMessage }}
    </p>
@endif
