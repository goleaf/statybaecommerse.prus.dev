@php
    $productUrl = route('frontend.products.show', $product);
    $description = $product->short_description ?: $product->description;
@endphp

<div class="flex flex-col bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
    <a href="{{ $productUrl }}" class="aspect-[4/3] bg-white dark:bg-gray-900 flex items-center justify-center">
        @if ($product->thumbnail)
            <img
                src="{{ $product->thumbnail }}"
                alt="{{ $product->name }}"
                class="w-full h-full object-cover"
                loading="lazy"
            >
        @else
            <div class="flex flex-col items-center justify-center text-gray-400 text-sm">
                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M3 19h18M4 5l2 14m12-14 2 14M8 5l1 14m6-14-1 14" />
                </svg>
                {{ __('No image available') }}
            </div>
        @endif
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
                {{ __('View product') }}
            </a>
        </div>
    </div>
</div>
