<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use App\Models\User;
use App\Services\RecommendationService;
use Database\Seeders\RecommendationSystemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecommendationSystemTest extends TestCase
{
    use RefreshDatabase;

    private RecommendationService $recommendationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recommendationService = app(RecommendationService::class);
        
        // Seed the recommendation system
        $this->seed(RecommendationSystemSeeder::class);
    }

    public function test_can_get_related_products_recommendations(): void
    {
        // Create test data
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $product1 = Product::factory()->create(['is_visible' => true]);
        $product2 = Product::factory()->create(['is_visible' => true]);
        $product3 = Product::factory()->create(['is_visible' => true]);
        
        $product1->categories()->attach($category->id);
        $product2->categories()->attach($category->id);
        $product3->categories()->attach($category->id);

        // Test recommendations
        $recommendations = $this->recommendationService->getRecommendations(
            'related_products',
            $user,
            $product1
        );

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $recommendations);
        $this->assertLessThanOrEqual(4, $recommendations->count());
        
        // Should not include the source product
        $this->assertFalse($recommendations->contains('id', $product1->id));
    }

    public function test_can_get_popular_products_recommendations(): void
    {
        // Create test data
        $user = User::factory()->create();
        
        $product1 = Product::factory()->create(['is_visible' => true]);
        $product2 = Product::factory()->create(['is_visible' => true]);
        $product3 = Product::factory()->create(['is_visible' => true]);

        // Test recommendations
        $recommendations = $this->recommendationService->getRecommendations(
            'popular_products',
            $user,
            null
        );

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $recommendations);
        $this->assertLessThanOrEqual(8, $recommendations->count());
    }

    public function test_can_get_trending_products_recommendations(): void
    {
        // Create test data
        $user = User::factory()->create();
        
        $product1 = Product::factory()->create(['is_visible' => true]);
        $product2 = Product::factory()->create(['is_visible' => true]);

        // Test recommendations
        $recommendations = $this->recommendationService->getRecommendations(
            'trending_products',
            $user,
            null
        );

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $recommendations);
        $this->assertLessThanOrEqual(6, $recommendations->count());
    }

    public function test_can_track_user_interaction(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Track interaction
        $this->recommendationService->trackUserInteraction(
            $user,
            $product,
            'view'
        );

        // Verify interaction was tracked
        $this->assertDatabaseHas('user_behaviors', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'behavior_type' => 'view',
        ]);
    }

    public function test_recommendation_blocks_exist(): void
    {
        $blocks = $this->recommendationService->getRecommendationBlocks();
        
        $this->assertGreaterThan(0, $blocks->count());
        
        // Check for common blocks
        $blockNames = $blocks->pluck('name')->toArray();
        $this->assertContains('related_products', $blockNames);
        $this->assertContains('you_might_also_like', $blockNames);
        $this->assertContains('similar_products', $blockNames);
    }

    public function test_can_clear_cache(): void
    {
        // This should not throw an exception
        $this->recommendationService->clearCache();
        
        $this->assertTrue(true);
    }

    public function test_can_optimize_system(): void
    {
        // This should not throw an exception
        $this->recommendationService->optimizeRecommendations();
        
        $this->assertTrue(true);
    }

    public function test_can_get_analytics(): void
    {
        $analytics = $this->recommendationService->getAnalytics('related_products', 30);
        
        $this->assertIsArray($analytics);
        $this->assertArrayHasKey('block_name', $analytics);
        $this->assertEquals('related_products', $analytics['block_name']);
    }
}
