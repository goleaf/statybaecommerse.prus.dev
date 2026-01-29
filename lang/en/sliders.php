<?php

declare(strict_types=1);

return [
    'link_search' => [
        'placeholder' => 'Search products, categories, collections, or paste a URL',
        'static'      => [
            'collections' => [
                'description' => 'Curated product collections.',
                'label'       => 'Collections overview',
            ],
            'contact' => [
                'description' => 'Ways for customers to reach support.',
                'label'       => 'Contact page',
            ],
            'home' => [
                'description' => 'Main storefront landing page.',
                'label'       => 'Homepage',
            ],
            'posts' => [
                'description' => 'Latest articles from our team.',
                'label'       => 'Blog posts',
            ],
            'products' => [
                'description' => 'Browse the full catalogue.',
                'label'       => 'All products',
            ],
        ],
        'types' => [
            'category'   => 'Category',
            'collection' => 'Collection',
            'post'       => 'Blog post',
            'product'    => 'Product',
            'static'     => 'Static page',
        ],
    ],
];
