@php
    $productUrl = route('frontend.products.show', $product);
    $description = $product->short_description ?: $product->description;
@endphp

<div class="flex flex-col bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
    <a href="{{ $productUrl }}" class="aspect-[4/3] bg-white dark:bg-gray-900 flex items-center justify-center">
        <img
            src="{{ $product->thumbnail ?: asset('images/placeholder-product.jpg') }}"
            alt="{{ $product->name }}"
            class="w-full h-full object-cover"
            loading="lazy"
        >
    </a>

    <div class="flex flex-col flex-1 p-5 space-y-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white leading-tight">
                <a href="{{ $productUrl }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                    {{ $product->name }}
                </a>
            </h3>
            @if ($description)
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ \Illuminate\Support\Str::limit(strip_tags($description), 120) }}
                </p>
            @endif
        </div>

        <div class="mt-auto flex items-center justify-between">
            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $product->formatted_price ?? '' }}
            </div>
            <a
                href="{{ $productUrl }}"
                class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500"
            >
                {{ __('frontend.search.view_product') }}
            </a>
        </div>
    </div>
</div>
