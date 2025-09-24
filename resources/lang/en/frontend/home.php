<?php declare(strict_types=1);

return [
    'catalogue' => [
        'badge' => 'Catalogue',
        'title' => 'Discover our catalogue',
        'subtitle' => 'Browse products by category, sort, and find what you need.',
        'filters' => [
            'all_categories' => 'All categories',
            'sort_by' => 'Sort by',
        ],
        'sort' => [
            'latest' => 'Latest',
            'popular' => 'Popular',
            'price_asc' => 'Price: Low to High',
            'price_desc' => 'Price: High to Low',
        ],
        'search_placeholder' => 'Search the catalogue...',
        'empty' => 'No products available at the moment.',
    ],
    'products' => [
        'badges' => [
            'sale' => 'Sale',
            'new' => 'New',
            'popular' => 'Popular',
        ],
        'stock' => [
            'in' => 'In stock',
            'out' => 'Out of stock',
        ],
        'actions' => [
            'details' => 'View details',
            'add_to_cart' => 'Add to cart',
        ],
        'rating_out_of_5' => 'out of 5',
        'sections' => [
            'featured' => [
                'title' => 'Featured products',
                'subtitle' => 'Our curated picks',
            ],
            'latest' => [
                'title' => 'Latest arrivals',
                'subtitle' => 'Just landed products',
            ],
            'trending' => [
                'title' => 'Trending now',
                'subtitle' => 'Most viewed and purchased',
            ],
            'sale' => [
                'title' => 'On sale',
                'subtitle' => 'Save today',
            ],
        ],
        'empty' => 'No products found.',
    ],
    'collections' => [
        'badge' => 'Collection',
        'open' => 'Open collection',
        'products_count' => '{0}No products|{1}1 product|[2,*]:count products',
    ],
];
