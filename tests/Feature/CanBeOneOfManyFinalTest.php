<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CanBeOneOfManyFinalTest extends TestCase
{
    use RefreshDatabase;

    // Note: DiscountRedemption and OrderTranslation tests removed due to missing factories

    public function test_product_latest_variant_relationship(): void
    {
        $product = Product::factory()->create();
        
        // Create multiple variants
        $oldVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_enabled' => true,
            'status' => 'active',
            'created_at' => now()->subDays(5),
        ]);
        
        $latestVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_enabled' => true,
            'status' => 'active',
            'created_at' => now()->subDays(1),
        ]);

        // Refresh the product to clear any cached relationships
        $product->refresh();

        // Test the latestVariant relationship
        $this->assertInstanceOf(ProductVariant::class, $product->latestVariant);
        $this->assertEquals($latestVariant->id, $product->latestVariant->id);
        $this->assertNotEquals($oldVariant->id, $product->latestVariant->id);
    }

    public function test_category_latest_child_relationship(): void
    {
        $parentCategory = Category::factory()->create();
        
        // Create multiple child categories
        $oldChild = Category::factory()->create([
            'parent_id' => $parentCategory->id,
            'created_at' => now()->subDays(5),
        ]);
        
        $latestChild = Category::factory()->create([
            'parent_id' => $parentCategory->id,
            'created_at' => now()->subDays(1),
        ]);

        // Refresh the parent category to clear any cached relationships
        $parentCategory->refresh();

        // Test the latestChild relationship
        $this->assertInstanceOf(Category::class, $parentCategory->latestChild);
        $this->assertEquals($latestChild->id, $parentCategory->latestChild->id);
        $this->assertNotEquals($oldChild->id, $parentCategory->latestChild->id);
    }
}
