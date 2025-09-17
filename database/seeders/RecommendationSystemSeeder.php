<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use Illuminate\Database\Seeder;

final class RecommendationSystemSeeder extends Seeder
{
    public function run(): void
    {
        // Create recommendation configurations
        $configs = [
            [
                'name' => 'Content Based - Categories',
                'type' => 'content_based',
                'config' => [
                    'feature_weights' => [
                        'category' => 0.5,
                        'brand' => 0.3,
                        'price_range' => 0.2,
                    ],
                    'use_cached_similarities' => true,
                    'recalculate_threshold' => 7,
                ],
                'filters' => [],
                'max_results' => 10,
                'min_score' => 0.3,
                'is_active' => true,
                'priority' => 100,
                'description' => 'Content-based recommendations using product categories, brands, and price ranges.',
            ],
            [
                'name' => 'Collaborative Filtering',
                'type' => 'collaborative',
                'config' => [
                    'interaction_weights' => [
                        'view' => 0.1,
                        'click' => 0.3,
                        'cart' => 0.5,
                        'purchase' => 1.0,
                        'wishlist' => 0.4,
                        'review' => 0.6,
                    ],
                    'min_interactions' => 2,
                    'neighbor_threshold' => 0.3,
                    'max_neighbors' => 50,
                ],
                'filters' => [],
                'max_results' => 10,
                'min_score' => 0.1,
                'is_active' => true,
                'priority' => 90,
                'description' => 'Collaborative filtering based on user behavior and similar users.',
            ],
            [
                'name' => 'Popular Products',
                'type' => 'popularity',
                'config' => [
                    'time_window' => 90,
                    'popularity_weights' => [
                        'sales' => 0.5,
                        'views' => 0.2,
                        'reviews' => 0.2,
                        'wishlist' => 0.1,
                    ],
                    'min_sales' => 1,
                    'min_reviews' => 0,
                ],
                'filters' => [],
                'max_results' => 10,
                'min_score' => 0.1,
                'is_active' => true,
                'priority' => 80,
                'description' => 'Popular products based on sales, views, reviews, and wishlist additions.',
            ],
            [
                'name' => 'Trending Products',
                'type' => 'trending',
                'config' => [
                    'time_window' => 7,
                    'trend_weights' => [
                        'recent_sales' => 0.4,
                        'recent_views' => 0.3,
                        'recent_reviews' => 0.2,
                        'recent_wishlist' => 0.1,
                    ],
                    'min_recent_activity' => 1,
                    'boost_new_products' => true,
                    'new_product_threshold' => 30,
                ],
                'filters' => [],
                'max_results' => 10,
                'min_score' => 0.1,
                'is_active' => true,
                'priority' => 85,
                'description' => 'Trending products based on recent activity and growth velocity.',
            ],
            [
                'name' => 'Cross-Sell Products',
                'type' => 'cross_sell',
                'config' => [
                    'time_window' => 90,
                    'min_co_purchase_count' => 2,
                    'co_purchase_weight' => 0.6,
                    'category_similarity_weight' => 0.3,
                    'price_compatibility_weight' => 0.1,
                    'max_price_ratio' => 2.0,
                    'min_price_ratio' => 0.5,
                ],
                'filters' => [],
                'max_results' => 8,
                'min_score' => 0.1,
                'is_active' => true,
                'priority' => 75,
                'description' => 'Products frequently bought together with the current product.',
            ],
            [
                'name' => 'Up-Sell Products',
                'type' => 'up_sell',
                'config' => [
                    'min_price_increase' => 1.1,
                    'max_price_increase' => 2.0,
                    'category_similarity_weight' => 0.5,
                    'price_ratio_weight' => 0.3,
                    'quality_indicators_weight' => 0.2,
                    'quality_indicators' => [
                        'review_rating' => 0.4,
                        'review_count' => 0.3,
                        'sales_count' => 0.3,
                    ],
                ],
                'filters' => [],
                'max_results' => 6,
                'min_score' => 0.1,
                'is_active' => true,
                'priority' => 70,
                'description' => 'Higher-priced products in similar categories with better quality indicators.',
            ],
            [
                'name' => 'Hybrid Recommendation',
                'type' => 'hybrid',
                'config' => [
                    'algorithm_weights' => [
                        'content_based' => 0.4,
                        'collaborative' => 0.4,
                        'popularity' => 0.2,
                    ],
                    'fallback_algorithms' => ['popularity', 'trending'],
                    'content_based_config' => [],
                    'collaborative_config' => [],
                    'popularity_config' => [],
                ],
                'filters' => [],
                'max_results' => 10,
                'min_score' => 0.1,
                'is_active' => true,
                'priority' => 95,
                'description' => 'Hybrid approach combining multiple recommendation algorithms.',
            ],
        ];

        foreach ($configs as $configData) {
            RecommendationConfig::updateOrCreate(
                ['name' => $configData['name']],
                $configData
            );
        }

        // Create recommendation blocks
        $blocks = [
            [
                'name' => 'related_products',
                'title' => 'Related Products',
                'description' => 'Products related to the current product based on categories and attributes.',
                'config_ids' => [1, 7], // Content-based and Hybrid
                'is_active' => true,
                'max_products' => 4,
                'cache_duration' => 3600,
                'display_settings' => [
                    'show_title' => true,
                    'show_prices' => true,
                    'show_brand' => true,
                    'show_stock_status' => true,
                ],
            ],
            [
                'name' => 'you_might_also_like',
                'title' => 'You Might Also Like',
                'description' => 'Personalized recommendations based on user behavior and preferences.',
                'config_ids' => [2, 7], // Collaborative and Hybrid
                'is_active' => true,
                'max_products' => 6,
                'cache_duration' => 1800,
                'display_settings' => [
                    'show_title' => true,
                    'show_prices' => true,
                    'show_brand' => true,
                    'show_stock_status' => true,
                ],
            ],
            [
                'name' => 'similar_products',
                'title' => 'Similar Products',
                'description' => 'Products similar to the current one based on features and attributes.',
                'config_ids' => [1], // Content-based
                'is_active' => true,
                'max_products' => 4,
                'cache_duration' => 7200,
                'display_settings' => [
                    'show_title' => true,
                    'show_prices' => true,
                    'show_brand' => false,
                    'show_stock_status' => true,
                ],
            ],
            [
                'name' => 'popular_products',
                'title' => 'Popular Products',
                'description' => 'Most popular products based on sales and user engagement.',
                'config_ids' => [3], // Popularity
                'is_active' => true,
                'max_products' => 8,
                'cache_duration' => 1800,
                'display_settings' => [
                    'show_title' => true,
                    'show_prices' => true,
                    'show_brand' => true,
                    'show_stock_status' => true,
                ],
            ],
            [
                'name' => 'trending_products',
                'title' => 'Trending Products',
                'description' => 'Currently trending products based on recent activity.',
                'config_ids' => [4], // Trending
                'is_active' => true,
                'max_products' => 6,
                'cache_duration' => 900,
                'display_settings' => [
                    'show_title' => true,
                    'show_prices' => true,
                    'show_brand' => true,
                    'show_stock_status' => true,
                ],
            ],
            [
                'name' => 'customers_also_bought',
                'title' => 'Customers Also Bought',
                'description' => 'Products frequently purchased together with the current product.',
                'config_ids' => [5], // Cross-sell
                'is_active' => true,
                'max_products' => 4,
                'cache_duration' => 3600,
                'display_settings' => [
                    'show_title' => true,
                    'show_prices' => true,
                    'show_brand' => true,
                    'show_stock_status' => true,
                ],
            ],
            [
                'name' => 'cross_sell',
                'title' => 'Complete Your Purchase',
                'description' => 'Additional products that complement the current product.',
                'config_ids' => [5], // Cross-sell
                'is_active' => true,
                'max_products' => 3,
                'cache_duration' => 3600,
                'display_settings' => [
                    'show_title' => true,
                    'show_prices' => true,
                    'show_brand' => false,
                    'show_stock_status' => true,
                ],
            ],
            [
                'name' => 'up_sell',
                'title' => 'Upgrade Your Choice',
                'description' => 'Premium alternatives to the current product.',
                'config_ids' => [6], // Up-sell
                'is_active' => true,
                'max_products' => 3,
                'cache_duration' => 3600,
                'display_settings' => [
                    'show_title' => true,
                    'show_prices' => true,
                    'show_brand' => true,
                    'show_stock_status' => true,
                ],
            ],
        ];

        foreach ($blocks as $blockData) {
            RecommendationBlock::updateOrCreate(
                ['name' => $blockData['name']],
                $blockData
            );
        }

        $this->command->info('Recommendation system seeded successfully!');
    }
}
