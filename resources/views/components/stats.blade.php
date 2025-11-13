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
            'label' => __('home_stats_products_label'),
            'value' => number_format($productsCount),
            'caption' => __('home_stats_products_caption'),
        ],
        [
            'icon' => 'grid',
            'label' => __('home_stats_categories_label'),
            'value' => number_format($categoriesCount),
            'caption' => __('home_stats_categories_caption'),
        ],
        [
            'icon' => 'link-05',
            'label' => __('home_stats_brands_label'),
            'value' => number_format($brandsCount),
            'caption' => __('home_stats_brands_caption'),
        ],
        [
            'icon' => 'star',
            'label' => __('home_stats_reviews_label'),
            'value' => number_format($reviewsCount),
            'caption' => __('home_stats_reviews_caption', [
                'rating' => number_format($averageRating, 1),
            ]),
        ],
    ];
@endphp

<x-container class="py-10 sm:py-14 lg:py-16">
    <div role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($items as $item)
            <article role="listitem" class="flex items-start gap-4 rounded-2xl bg-white px-6 py-5 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-0.5 hover:shadow-lg dark:bg-slate-900/60 dark:ring-slate-800">
                <div class="flex size-12 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-300">
                    <x-dynamic-component :component="'untitledui-' . $item['icon']" class="size-6" aria-hidden="true" />
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ $item['label'] }}
                    </p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $item['value'] }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $item['caption'] }}
                    </p>
                </div>
            </article>
        @endforeach
    </div>
</x-container>
