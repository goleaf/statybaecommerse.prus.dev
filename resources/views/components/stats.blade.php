@props([
    'products' => 0,
    'categories' => 0,
    'brands' => 0,
    'reviews' => 0,
    'rating' => 0,
])

@php
    $productsCount = is_numeric($products) ? (int) $products : 0;
    $categoriesCount = is_numeric($categories) ? (int) $categories : 0;
    $brandsCount = is_numeric($brands) ? (int) $brands : 0;
    $reviewsCount = is_numeric($reviews) ? (int) $reviews : 0;
    $averageRating = is_numeric($rating) ? (float) $rating : 0.0;

    $items = [
        [
            'icon' => 'cube',
            'label' => __('frontend/home.stats.products.label'),
            'value' => number_format($productsCount),
            'caption' => __('frontend/home.stats.products.caption'),
        ],
        [
            'icon' => 'grid',
            'label' => __('frontend/home.stats.categories.label'),
            'value' => number_format($categoriesCount),
            'caption' => __('frontend/home.stats.categories.caption'),
        ],
        [
            'icon' => 'link-05',
            'label' => __('frontend/home.stats.brands.label'),
            'value' => number_format($brandsCount),
            'caption' => __('frontend/home.stats.brands.caption'),
        ],
        [
            'icon' => 'star',
            'label' => __('frontend/home.stats.reviews.label'),
            'value' => number_format($reviewsCount),
            'caption' => __('frontend/home.stats.reviews.caption', [
                'rating' => number_format($averageRating, 1),
            ]),
        ],
    ];
@endphp

<x-container class="py-10 sm:py-14 lg:py-16">
    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($items as $item)
            <div class="flex items-start gap-4 rounded-2xl bg-white px-6 py-5 shadow-sm ring-1 ring-gray-100 dark:bg-slate-900/60 dark:ring-slate-800">
                <div class="flex size-12 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-300">
                    <x-dynamic-component :component="'untitledui-' . $item['icon']" class="size-6" aria-hidden="true" />
                </div>
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ $item['label'] }}
                    </dt>
                    <dd class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $item['value'] }}
                    </dd>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $item['caption'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </dl>
</x-container>
