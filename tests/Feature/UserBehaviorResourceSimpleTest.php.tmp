<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\UserBehavior;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UserBehaviorResource Simple Test
 *
 * Simple test to verify UserBehaviorResource basic functionality.
 */
final class UserBehaviorResourceSimpleTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_create_user_behavior(): void
    {
        $this->actingAs($this->adminUser);

        $product = Product::factory()->create();
        $category = Category::factory()->create();

        $userBehavior = UserBehavior::create([
            'user_id' => $this->adminUser->id,
            'behavior_type' => 'view',
            'product_id' => $product->id,
            'category_id' => $category->id,
            'session_id' => 'test-session-123',
            'referrer' => 'https://example.com',
            'user_agent' => 'Mozilla/5.0 (Test Browser)',
            'ip_address' => '192.168.1.1',
            'metadata' => ['test_key' => 'test_value'],
        ]);

        $this->assertDatabaseHas('user_behaviors', [
            'id' => $userBehavior->id,
            'user_id' => $this->adminUser->id,
            'behavior_type' => 'view',
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);

        $this->assertEquals($this->adminUser->id, $userBehavior->user->id);
        $this->assertEquals($product->id, $userBehavior->product->id);
        $this->assertEquals($category->id, $userBehavior->category->id);
        $this->assertIsArray($userBehavior->metadata);
        $this->assertEquals('test_value', $userBehavior->metadata['test_key']);
    }

    public function test_user_behavior_model_scopes_work(): void
    {
        $this->actingAs($this->adminUser);

        $product = Product::factory()->create();
        $category = Category::factory()->create();

        // Create behaviors with different types and dates
        UserBehavior::create([
            'user_id' => $this->adminUser->id,
            'behavior_type' => 'view',
            'product_id' => $product->id,
            'category_id' => $category->id,
            'created_at' => now()->subDays(5),
        ]);

        UserBehavior::create([
            'user_id' => $this->adminUser->id,
            'behavior_type' => 'click',
            'product_id' => $product->id,
            'category_id' => $category->id,
            'created_at' => now()->subDays(10),
        ]);

        UserBehavior::create([
            'user_id' => $this->adminUser->id,
            'behavior_type' => 'purchase',
            'product_id' => $product->id,
            'category_id' => $category->id,
            'created_at' => now()->subDays(35),
        ]);

        // Test scopeRecent
        $recentBehaviors = UserBehavior::recent(30)->get();
        $this->assertCount(2, $recentBehaviors);

        // Test scopeByType
        $viewBehaviors = UserBehavior::byType('view')->get();
        $this->assertCount(1, $viewBehaviors);
        $this->assertEquals('view', $viewBehaviors->first()->behavior_type);

        // Test scopeByUser
        $userBehaviors = UserBehavior::byUser($this->adminUser->id)->get();
        $this->assertCount(3, $userBehaviors);
    }

    public function test_user_behavior_factory_works(): void
    {
        $userBehavior = UserBehavior::factory()->create([
            'behavior_type' => 'view',
        ]);

        $this->assertInstanceOf(UserBehavior::class, $userBehavior);
        $this->assertEquals('view', $userBehavior->behavior_type);
        $this->assertIsArray($userBehavior->metadata);
    }

    public function test_user_behavior_factory_states_work(): void
    {
        $viewBehavior = UserBehavior::factory()->view()->create();
        $this->assertEquals('view', $viewBehavior->behavior_type);

        $clickBehavior = UserBehavior::factory()->click()->create();
        $this->assertEquals('click', $clickBehavior->behavior_type);

        $addToCartBehavior = UserBehavior::factory()->addToCart()->create();
        $this->assertEquals('add_to_cart', $addToCartBehavior->behavior_type);

        $purchaseBehavior = UserBehavior::factory()->purchase()->create();
        $this->assertEquals('purchase', $purchaseBehavior->behavior_type);

        $searchBehavior = UserBehavior::factory()->search()->create();
        $this->assertEquals('search', $searchBehavior->behavior_type);

        $recentBehavior = UserBehavior::factory()->recent()->create();
        $this->assertTrue($recentBehavior->created_at->isAfter(now()->subDays(8)));

        $todayBehavior = UserBehavior::factory()->today()->create();
        $this->assertTrue($todayBehavior->created_at->isToday());
    }
}
