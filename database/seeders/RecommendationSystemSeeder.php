<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductFeature;
use App\Models\ProductSimilarity;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Models\RecommendationConfig;
use App\Models\RecommendationConfigSimple;
use App\Models\User;
use App\Models\UserBehavior;
use App\Models\UserPreference;
use App\Models\UserProductInteraction;
use Illuminate\Database\Seeder;

class RecommendationSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Recommendation System...');

        // Get or create users
        $users = User::all();
        if ($users->isEmpty()) {
            $users = User::factory()->count(10)->create();
            $this->command->info('Created '.$users->count().' users');
        } else {
            $this->command->info('Using existing '.$users->count().' users');
        }

        // Get or create categories
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $categories = Category::factory()->count(5)->create();
            $this->command->info('Created '.$categories->count().' categories');
        } else {
            $this->command->info('Using existing '.$categories->count().' categories');
        }

        // Get or create products
        $products = Product::all();
        if ($products->isEmpty()) {
            $products = Product::factory()->count(50)->create();
            $this->command->info('Created '.$products->count().' products');
        } else {
            $this->command->info('Using existing '.$products->count().' products');
        }

        // Create recommendation blocks
        $this->createRecommendationBlocks();
        $this->command->info('Created recommendation blocks');

        // Create recommendation configs
        $this->createRecommendationConfigs();
        $this->command->info('Created recommendation configs');

        // Create recommendation configs simple
        $this->createRecommendationConfigsSimple();
        $this->command->info('Created recommendation configs simple');

        // Create recommendation caches
        $this->createRecommendationCaches();
        $this->command->info('Created recommendation caches');

        // Create recommendation analytics
        $this->createRecommendationAnalytics();
        $this->command->info('Created recommendation analytics');

        // Create user behaviors
        $this->createUserBehaviors($users, $products);
        $this->command->info('Created user behaviors');

        // Create product similarities
        $this->createProductSimilarities($products);
        $this->command->info('Created product similarities');

        // Create user preferences
        $this->createUserPreferences($users, $categories);
        $this->command->info('Created user preferences');

        // Create product features
        $this->createProductFeatures($products, $categories);
        $this->command->info('Created product features');

        // Create user product interactions
        $this->createUserProductInteractions($users, $products);
        $this->command->info('Created user product interactions');

        $this->command->info('Recommendation System seeding completed!');
    }

    private function createRecommendationBlocks(): void
    {
        $blocks = [
            [
                'name' => 'featured_products',
                'title' => 'Featured Products',
                'description' => 'Showcase featured products on homepage',
                'type' => 'featured',
                'position' => 'top',
                'is_active' => true,
                'max_products' => 6,
                'cache_duration' => 3600,
                'sort_order' => 1,
            ],
            [
                'name' => 'related_products',
                'title' => 'Related Products',
                'description' => 'Show related products on product pages',
                'type' => 'related',
                'position' => 'bottom',
                'is_active' => true,
                'max_products' => 4,
                'cache_duration' => 1800,
                'sort_order' => 2,
            ],
            [
                'name' => 'similar_products',
                'title' => 'Similar Products',
                'description' => 'Show similar products based on content',
                'type' => 'similar',
                'position' => 'sidebar',
                'is_active' => true,
                'max_products' => 3,
                'cache_duration' => 1800,
                'sort_order' => 3,
            ],
            [
                'name' => 'trending_products',
                'title' => 'Trending Products',
                'description' => 'Show trending products based on popularity',
                'type' => 'trending',
                'position' => 'inline',
                'is_active' => true,
                'max_products' => 5,
                'cache_duration' => 3600,
                'sort_order' => 4,
            ],
            [
                'name' => 'recent_products',
                'title' => 'Recently Viewed',
                'description' => 'Show recently viewed products',
                'type' => 'recent',
                'position' => 'sidebar',
                'is_active' => true,
                'max_products' => 4,
                'cache_duration' => 900,
                'sort_order' => 5,
            ],
        ];

        foreach ($blocks as $blockData) {
            RecommendationBlock::factory()
                ->state($blockData)
                ->create();
        }
    }

    private function createRecommendationConfigs(): void
    {
        $configs = [
            [
                'name' => 'Collaborative Filtering',
                'type' => 'collaborative',
                'description' => 'Recommend products based on user behavior similarity',
                'is_active' => true,
                'priority' => 100,
                'max_results' => 10,
                'min_score' => 0.1,
                'cache_ttl' => 3600,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Content-Based Filtering',
                'type' => 'content_based',
                'description' => 'Recommend products based on product features',
                'is_active' => true,
                'priority' => 90,
                'max_results' => 8,
                'min_score' => 0.15,
                'cache_ttl' => 1800,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Hybrid Recommendation',
                'type' => 'hybrid',
                'description' => 'Combine collaborative and content-based filtering',
                'is_active' => true,
                'priority' => 95,
                'max_results' => 12,
                'min_score' => 0.12,
                'cache_ttl' => 3600,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Popularity-Based',
                'type' => 'popularity',
                'description' => 'Recommend popular products',
                'is_active' => true,
                'priority' => 80,
                'max_results' => 6,
                'min_score' => 0.2,
                'cache_ttl' => 7200,
                'is_default' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Trending Products',
                'type' => 'trending',
                'description' => 'Recommend trending products',
                'is_active' => true,
                'priority' => 85,
                'max_results' => 8,
                'min_score' => 0.18,
                'cache_ttl' => 1800,
                'is_default' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($configs as $configData) {
            RecommendationConfig::factory()
                ->state($configData)
                ->create();
        }
    }

    private function createRecommendationConfigsSimple(): void
    {
        $configs = [
            [
                'name' => 'Simple Collaborative',
                'code' => 'simple_collab',
                'description' => 'Simple collaborative filtering configuration',
                'algorithm_type' => 'collaborative',
                'min_score' => 0.1,
                'max_results' => 10,
                'decay_factor' => 0.9,
                'exclude_out_of_stock' => true,
                'exclude_inactive' => true,
                'price_weight' => 0.2,
                'rating_weight' => 0.3,
                'popularity_weight' => 0.2,
                'recency_weight' => 0.1,
                'category_weight' => 0.2,
                'custom_weight' => 0.0,
                'cache_duration' => 60,
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
                'notes' => 'Default simple configuration',
            ],
            [
                'name' => 'Simple Content-Based',
                'code' => 'simple_content',
                'description' => 'Simple content-based filtering configuration',
                'algorithm_type' => 'content_based',
                'min_score' => 0.15,
                'max_results' => 8,
                'decay_factor' => 0.85,
                'exclude_out_of_stock' => true,
                'exclude_inactive' => true,
                'price_weight' => 0.3,
                'rating_weight' => 0.4,
                'popularity_weight' => 0.1,
                'recency_weight' => 0.1,
                'category_weight' => 0.1,
                'custom_weight' => 0.0,
                'cache_duration' => 30,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
                'notes' => 'Content-based configuration',
            ],
            [
                'name' => 'Simple Hybrid',
                'code' => 'simple_hybrid',
                'description' => 'Simple hybrid recommendation configuration',
                'algorithm_type' => 'hybrid',
                'min_score' => 0.12,
                'max_results' => 12,
                'decay_factor' => 0.88,
                'exclude_out_of_stock' => true,
                'exclude_inactive' => true,
                'price_weight' => 0.25,
                'rating_weight' => 0.35,
                'popularity_weight' => 0.15,
                'recency_weight' => 0.1,
                'category_weight' => 0.15,
                'custom_weight' => 0.0,
                'cache_duration' => 45,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
                'notes' => 'Hybrid configuration',
            ],
        ];

        foreach ($configs as $configData) {
            RecommendationConfigSimple::factory()
                ->state($configData)
                ->create();
        }
    }

    private function createRecommendationCaches(): void
    {
        $blocks = RecommendationBlock::all();
        $users = User::all();
        $products = Product::all();

        RecommendationCache::factory()
            ->count(20)
            ->state(function () use ($blocks, $users, $products) {
                return [
                    'cache_key' => 'cache_'.fake()->uuid().'_'.now()->timestamp,
                    'block_id' => $blocks->random()->id,
                    'user_id' => $users->random()->id,
                    'product_id' => $products->random()->id,
                    'context_type' => fake()->randomElement(['homepage', 'product', 'category', 'cart', 'checkout']),
                    'context_data' => [
                        'page_type' => fake()->randomElement(['homepage', 'product', 'category']),
                        'category_id' => fake()->optional()->numberBetween(1, 5),
                        'search_query' => fake()->optional()->words(2, true),
                    ],
                    'recommendations' => $products->random(5)->map(function ($product) {
                        return [
                            'product_id' => $product->id,
                            'score' => fake()->randomFloat(2, 0.1, 1.0),
                            'reason' => fake()->randomElement(['similar_users', 'similar_products', 'popular', 'trending']),
                        ];
                    })->toArray(),
                    'hit_count' => fake()->numberBetween(0, 100),
                    'expires_at' => now()->addHours(fake()->numberBetween(1, 24)),
                ];
            })
            ->create();
    }

    private function createRecommendationAnalytics(): void
    {
        $blocks = RecommendationBlock::all();
        $configs = RecommendationConfig::all();
        $users = User::all();
        $products = Product::all();

        RecommendationAnalytics::factory()
            ->count(50)
            ->state(function () use ($blocks, $configs, $users, $products) {
                return [
                    'block_id' => $blocks->random()->id,
                    'config_id' => $configs->random()->id,
                    'user_id' => $users->random()->id,
                    'product_id' => $products->random()->id,
                    'action' => fake()->randomElement(['view', 'click', 'add_to_cart', 'purchase']),
                    'ctr' => fake()->randomFloat(4, 0.01, 0.5),
                    'conversion_rate' => fake()->randomFloat(4, 0.01, 0.3),
                    'metrics' => [
                        'impressions' => fake()->numberBetween(100, 10000),
                        'clicks' => fake()->numberBetween(10, 1000),
                        'conversions' => fake()->numberBetween(1, 100),
                        'revenue' => fake()->randomFloat(2, 10, 1000),
                    ],
                    'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
                ];
            })
            ->create();
    }

    private function createUserBehaviors($users, $products): void
    {
        UserBehavior::factory()
            ->count(200)
            ->state(function () use ($users, $products) {
                return [
                    'user_id' => $users->random()->id,
                    'session_id' => fake()->uuid(),
                    'product_id' => $products->random()->id,
                    'category_id' => fake()->optional()->numberBetween(1, 5),
                    'behavior_type' => fake()->randomElement(['view', 'click', 'add_to_cart', 'purchase', 'wishlist', 'search']),
                    'metadata' => [
                        'page_url' => fake()->url(),
                        'referrer' => fake()->optional()->url(),
                        'search_query' => fake()->optional()->words(2, true),
                    ],
                    'referrer' => fake()->optional()->url(),
                    'user_agent' => fake()->userAgent(),
                    'ip_address' => fake()->ipv4(),
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ];
            })
            ->create();
    }

    private function createProductSimilarities($products): void
    {
        $algorithmTypes = ['content_based', 'collaborative', 'hybrid'];

        ProductSimilarity::factory()
            ->count(100)
            ->state(function () use ($products, $algorithmTypes) {
                $product1 = $products->random();
                $product2 = $products->where('id', '!=', $product1->id)->random();

                return [
                    'product_id' => $product1->id,
                    'similar_product_id' => $product2->id,
                    'algorithm_type' => fake()->randomElement($algorithmTypes),
                    'similarity_score' => fake()->randomFloat(6, 0.1, 1.0),
                    'calculation_data' => [
                        'features_matched' => fake()->numberBetween(1, 10),
                        'weighted_score' => fake()->randomFloat(6, 0.1, 1.0),
                        'confidence' => fake()->randomFloat(2, 0.5, 1.0),
                    ],
                    'calculated_at' => fake()->dateTimeBetween('-7 days', 'now'),
                ];
            })
            ->create();
    }

    private function createUserPreferences($users, $categories): void
    {
        $preferenceTypes = ['category_preference', 'brand_preference', 'price_range', 'style_preference'];

        foreach ($users as $user) {
            foreach ($preferenceTypes as $type) {
                UserPreference::factory()
                    ->for($user)
                    ->state([
                        'preference_type' => $type,
                        'preference_key' => fake()->randomElement(['category_'.fake()->numberBetween(1, 5), 'brand_'.fake()->word(), 'price_'.fake()->randomElement(['low', 'medium', 'high'])]),
                        'preference_score' => fake()->randomFloat(6, 0.1, 1.0),
                        'metadata' => [
                            'source' => fake()->randomElement(['explicit', 'implicit', 'inferred']),
                            'confidence' => fake()->randomFloat(2, 0.5, 1.0),
                        ],
                        'last_updated' => fake()->dateTimeBetween('-30 days', 'now'),
                    ])
                    ->create();
            }
        }
    }

    private function createProductFeatures($products, $categories): void
    {
        $featureTypes = ['category', 'brand', 'price_range', 'attributes', 'tags'];

        foreach ($products as $product) {
            foreach ($featureTypes as $type) {
                ProductFeature::factory()
                    ->for($product)
                    ->state([
                        'feature_type' => $type,
                        'feature_key' => fake()->word(),
                        'feature_value' => fake()->randomFloat(6, 0, 1),
                        'weight' => fake()->randomFloat(4, 0.5, 1.0),
                    ])
                    ->create();
            }
        }
    }

    private function createUserProductInteractions($users, $products): void
    {
        $interactionTypes = ['view', 'cart', 'purchase', 'wishlist', 'review'];

        UserProductInteraction::factory()
            ->count(300)
            ->state(function () use ($users, $products, $interactionTypes) {
                $user = $users->random();
                $product = $products->random();
                $interactionType = fake()->randomElement($interactionTypes);

                return [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'interaction_type' => $interactionType,
                    'rating' => $interactionType === 'review' ? fake()->randomFloat(2, 1, 5) : null,
                    'count' => fake()->numberBetween(1, 10),
                    'first_interaction' => fake()->dateTimeBetween('-30 days', 'now'),
                    'last_interaction' => fake()->dateTimeBetween('-7 days', 'now'),
                ];
            })
            ->create();
    }
}
