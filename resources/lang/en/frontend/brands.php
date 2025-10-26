<?php

declare(strict_types=1);

return [
    'meta' => [
        'title'       => 'Brands',
        'description' => 'Browse our trusted partner directory and discover premium construction brands available through StatyBae.',
    ],
    'hero' => [
        'title'       => 'Trusted construction brands',
        'description' => 'Discover reliable suppliers, tooling innovators, and material partners curated for Baltic building professionals.',
        'cta'         => 'Explore collections',
    ],
    'stats' => [
        'brands' => [
            'caption' => 'Active partners',
        ],
        'products' => [
            'caption' => 'Available products',
        ],
        'promise' => [
            'label'   => 'Our promise',
            'caption' => 'Premium quality',
        ],
    ],
    'filters' => [
        'title'              => 'Refine the brand list',
        'description'        => 'Use search and smart sorting to highlight the partners that fit your project.',
        'search_label'       => 'Search brands',
        'search_placeholder' => 'Search brands…',
        'sort_label'         => 'Sort by',
        'options'            => [
            'name'           => 'Name A-Z',
            'name_desc'      => 'Name Z-A',
            'products_count' => 'Most products',
            'created_at'     => 'Newest',
            'featured'       => 'Featured first',
        ],
        'status' => [
            'none'      => 'No filters applied',
            'none_hint' => 'Showing the full list of enabled brands.',
            'some'      => '{1}1 filter active|[2,*]:count filters active',
            'some_hint' => 'Filters update instantly for a smoother browsing experience.',
        ],
        'sync_notice' => 'Filters sync automatically',
        'quick'       => [
            'featured' => 'Featured first',
            'products' => 'Most products',
        ],
    ],
    'list' => [
        'title'       => 'Brand directory',
        'description' => 'Explore industry-leading suppliers curated by the StatyBae team.',
        'badges'      => [
            'brands'   => '{0}No brands|{1}1 brand|[2,*]:count brands',
            'products' => '{0}No products|{1}1 product|[2,*]:count products',
            'featured' => 'Featured',
        ],
        'featured' => [
            'title'    => 'Highlighted partners',
            'subtitle' => 'Our most trusted collaborators for demanding worksites.',
            'count'    => '{1}1 brand highlighted|[2,*]:count brands highlighted',
        ],
        'visit'         => 'View brand profile',
        'fallback_logo' => 'Placeholder logo for :name',
        'empty'         => [
            'title'       => 'No brands available',
            'description' => 'New partners will appear soon. Check back shortly.',
        ],
    ],
];
