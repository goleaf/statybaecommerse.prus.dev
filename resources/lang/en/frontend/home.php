<?php

declare(strict_types=1);

return [
    'meta' => [
        'title'       => 'Discover modern Lithuanian e-commerce',
        'description' => 'Explore curated collections, trusted brands, and personalized recommendations crafted for Baltic shoppers.',
    ],
    'hero' => [
        'eyebrow'       => 'Seasonal highlights',
        'title'         => 'All of your favourite products in one vibrant marketplace',
        'subtitle'      => 'Find elevated essentials, local gems, and the newest arrivals selected by our merchandising team.',
        'cta_primary'   => 'Shop featured products',
        'cta_secondary' => 'Browse new arrivals',
        'featured_card' => [
            'badge'    => 'Editor’s choice',
            'title'    => 'Hand-picked looks tailored to Baltic lifestyles',
            'subtitle' => 'Discover curated outfits, lifestyle picks, and trending gear built around quality and versatility.',
            'link'     => 'See all featured collections',
        ],
        'secondary_cards' => [
            'new' => [
                'badge'    => 'Fresh in stock',
                'title'    => 'Latest product drops',
                'subtitle' => 'Updated every morning with limited releases and exclusive colourways.',
                'link'     => 'View the newest items',
            ],
            'sale' => [
                'badge'    => 'Limited offer',
                'title'    => 'Member pricing events',
                'subtitle' => 'Stretch your budget with weekly promotions and bundle-friendly deals.',
                'link'     => 'Unlock current deals',
            ],
        ],
    ],
    'stats' => [
        'products' => [
            'label'   => 'Products',
            'caption' => 'Live in catalogue',
        ],
        'categories' => [
            'label'   => 'Categories',
            'caption' => 'Curated for easy browsing',
        ],
        'brands' => [
            'label'   => 'Brands',
            'caption' => 'Verified partners',
        ],
        'reviews' => [
            'label'   => 'Reviews',
            'caption' => 'Community rating: :rating ★',
        ],
    ],
    'sections' => [
        'featured' => [
            'title'    => 'Featured selections',
            'subtitle' => 'Premium drops and editorial collections crafted by our buyers.',
        ],
        'catalogue' => [
            'title'    => 'Explore the full catalogue',
            'subtitle' => 'Navigate by category or brand and uncover experiences tailored for you.',
            'cards'    => [
                'categories' => [
                    'title'    => 'Shop by category',
                    'subtitle' => 'Compare essentials, discover niche finds, and explore seasonal edits.',
                    'link'     => 'Browse categories',
                ],
                'brands' => [
                    'title'    => 'Shop by brand',
                    'subtitle' => 'Support trusted labels and up-and-coming creators from across Europe.',
                    'link'     => 'Meet the brands',
                ],
            ],
            'lists' => [
                'categories' => [
                    'title'      => 'Top catalogue categories',
                    'subtitle'   => 'Our most visited departments from heavy-duty tools to finishing materials.',
                    'link'       => 'View all',
                    'item_count' => ':count listed products',
                    'empty'      => 'Catalogue will showcase categories once published.',
                ],
                'brands' => [
                    'title'      => 'Featured construction brands',
                    'subtitle'   => 'Leaders in professional equipment, insulation, and structural systems.',
                    'link'       => 'View all',
                    'item_count' => ':count stocked items',
                    'empty'      => 'Brand showcases will appear soon.',
                ],
            ],
        ],
        'highlights' => [
            'title'    => 'Stay inspired',
            'subtitle' => 'Keep up with trending, new-in, and best-value picks curated daily.',
            'latest'   => [
                'title' => 'Latest arrivals from the warehouse',
                'empty' => 'Fresh stock will appear shortly.',
            ],
            'brands' => [
                'fallback_description' => 'Baltic construction favourite.',
                'cta'                  => 'Explore brand',
            ],
        ],
        'discovery' => [
            'title'    => 'Why shop with us?',
            'subtitle' => 'An adaptive commerce experience focused on trust, convenience, and delight.',
            'items'    => [
                'recommendations' => [
                    'title'    => 'Personal recommendations',
                    'subtitle' => 'Smart suggestions powered by browsing habits and community favourites.',
                ],
                'security' => [
                    'title'    => 'Secure by default',
                    'subtitle' => 'Advanced fraud protection and privacy-first architecture keep you safe.',
                ],
                'payments' => [
                    'title'    => 'Flexible payments',
                    'subtitle' => 'Pay with cards, instalments, or digital wallets supporting euros.',
                ],
                'delivery' => [
                    'title'    => 'Reliable delivery',
                    'subtitle' => 'Trackable shipping across the Baltics with carbon-conscious partners.',
                ],
            ],
        ],
        'cta' => [
            'title'          => 'Join our community of modern shoppers',
            'subtitle'       => 'Get the inside scoop on product launches, loyalty perks, and editorial guides.',
            'primary'        => 'Read the latest stories',
            'secondary'      => 'Talk to our team',
            'review_badge'   => 'Loved by our customers',
            'review_copy'    => 'Over the past months shoppers consistently rated their experience above four stars.',
            'review_caption' => ':count verified reviews and counting',
        ],
    ],
    'catalogue' => [
        'badge'    => 'Catalogue',
        'title'    => 'Discover our catalogue',
        'subtitle' => 'Browse products by category, sort, and find what you need.',
        'filters'  => [
            'all_categories' => 'All categories',
            'sort_by'        => 'Sort by',
        ],
        'sort' => [
            'latest'     => 'Latest',
            'popular'    => 'Popular',
            'price_asc'  => 'Price: Low to High',
            'price_desc' => 'Price: High to Low',
        ],
        'search_placeholder' => 'Search the catalogue...',
        'empty'              => 'No products available at the moment.',
    ],
    'products' => [
        'badges' => [
            'sale'    => 'Sale',
            'new'     => 'New',
            'popular' => 'Popular',
        ],
        'stock' => [
            'in'  => 'In stock',
            'out' => 'Out of stock',
        ],
        'actions' => [
            'details'     => 'View details',
            'add_to_cart' => 'Add to cart',
        ],
        'rating_out_of_5' => 'out of 5',
        'sections'        => [
            'featured' => [
                'title'    => 'Featured products',
                'subtitle' => 'Our curated picks',
            ],
            'latest' => [
                'title'    => 'Latest arrivals',
                'subtitle' => 'Just landed products',
            ],
            'trending' => [
                'title'    => 'Trending now',
                'subtitle' => 'Most viewed and purchased',
            ],
            'sale' => [
                'title'    => 'On sale',
                'subtitle' => 'Save today',
            ],
        ],
        'empty' => 'No products found.',
    ],
    'collections' => [
        'badge'          => 'Collection',
        'open'           => 'Open collection',
        'products_count' => '{0}No products|{1}1 product|[2,*]:count products',
    ],
    'messages' => [
        'no_featured_products' => 'Featured products are coming soon.',
        'no_latest_products'   => 'New arrivals will appear here once published.',
        'no_trending_products' => 'Trending picks update soon – check back shortly.',
        'no_sale_products'     => 'Sale items will populate as promotions go live.',
    ],
];
