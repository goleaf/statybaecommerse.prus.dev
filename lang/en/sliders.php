<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Sliders',
    'plural'           => 'Sliders',
    'single'           => 'Slider',

    'basic_information' => 'Basic Information',
    'media'             => 'Media',
    'appearance'        => 'Appearance',
    'settings'          => 'Settings',

    'title' => 'Title',
    'description' => 'Description',
    'button_text' => 'Button Text',
    'button_url' => 'Button URL',
    'button_url_placeholder' => 'Search for content or paste an URL',
    'button_url_helper' => 'Select an internal page, product or enter an external URL.',
    'image' => 'Image',
    'background_color' => 'Background Color',
    'text_color' => 'Text Color',
    'sort_order' => 'Sort Order',
    'is_active' => 'Active',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    'link_search' => [
        'placeholder' => 'Search products, categories, collections, or paste a URL',
        'types' => [
            'static' => 'Static page',
            'product' => 'Product',
            'category' => 'Category',
            'collection' => 'Collection',
            'post' => 'Blog post',
        ],
        'static_links' => [
            'home' => [
                'route' => 'home',
                'label' => 'Homepage',
                'description' => 'Main storefront landing page.',
            ],
            'products' => [
                'route' => 'frontend.products.index',
                'label' => 'All products',
                'description' => 'Browse the full catalogue.',
            ],
            'collections' => [
                'route' => 'frontend.collections.index',
                'label' => 'Collections overview',
                'description' => 'Curated product collections.',
            ],
            'posts' => [
                'route' => 'frontend.posts.index',
                'label' => 'Blog posts',
                'description' => 'Latest articles from our team.',
            ],
            'contact' => [
                'route' => 'frontend.contact.index',
                'label' => 'Contact page',
                'description' => 'Ways for customers to reach support.',
            ],
        ],
    ],
];
