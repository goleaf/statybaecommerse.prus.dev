<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductSimilarity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductSimilarityTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_similarity_can_be_created(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $similarity = ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'algorithm_type' => 'cosine_similarity',
            'similarity_score' => 0.85,
            'calculation_data' => ['features' => ['color', 'size', 'brand'], 'weights' => [0.3, 0.4, 0.3]],
            'calculated_at' => now(),
        ]);

        $this->assertInstanceOf(ProductSimilarity::class, $similarity);
        $this->assertEquals($product1->id, $similarity->product_id);
        $this->assertEquals($product2->id, $similarity->similar_product_id);
        $this->assertEquals('cosine_similarity', $similarity->algorithm_type);
        $this->assertEquals(0.85, $similarity->similarity_score);
        $this->assertIsArray($similarity->calculation_data);
        $this->assertEquals(['color', 'size', 'brand'], $similarity->calculation_data['features']);
        $this->assertEquals([0.3, 0.4, 0.3], $similarity->calculation_data['weights']);
        $this->assertInstanceOf(\Carbon\Carbon::class, $similarity->calculated_at);
    }

    public function test_product_similarity_fillable_attributes(): void
    {
        $similarity = new ProductSimilarity();
        $fillable = $similarity->getFillable();

        $expectedFillable = [
            'product_id',
            'similar_product_id',
            'algorithm_type',
            'similarity_score',
            'calculation_data',
            'calculated_at',
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_product_similarity_casts(): void
    {
        $similarity = ProductSimilarity::factory()->create([
            'similarity_score' => '0.75',
            'calculation_data' => ['test' => 'data'],
            'calculated_at' => '2024-01-01 12:00:00',
        ]);

        $this->assertIsString($similarity->similarity_score);
        $this->assertEquals('0.750000', $similarity->similarity_score);
        $this->assertIsArray($similarity->calculation_data);
        $this->assertInstanceOf(\Carbon\Carbon::class, $similarity->calculated_at);
    }

    public function test_product_similarity_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $similarity = ProductSimilarity::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $similarity->product);
        $this->assertEquals($product->id, $similarity->product->id);
    }

    public function test_product_similarity_belongs_to_similar_product(): void
    {
        $similarProduct = Product::factory()->create();
        $similarity = ProductSimilarity::factory()->create(['similar_product_id' => $similarProduct->id]);

        $this->assertInstanceOf(Product::class, $similarity->similarProduct);
        $this->assertEquals($similarProduct->id, $similarity->similarProduct->id);
    }

    public function test_product_similarity_scope_by_algorithm(): void
    {
        $cosineSimilarity = ProductSimilarity::factory()->create(['algorithm_type' => 'cosine_similarity']);
        $euclideanSimilarity = ProductSimilarity::factory()->create(['algorithm_type' => 'euclidean_distance']);

        $cosineSimilarities = ProductSimilarity::withoutGlobalScopes()->byAlgorithm('cosine_similarity')->get();
        $this->assertTrue($cosineSimilarities->contains($cosineSimilarity));
        $this->assertFalse($cosineSimilarities->contains($euclideanSimilarity));
    }

    public function test_product_similarity_scope_with_min_score(): void
    {
        $highSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.9]);
        $lowSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.3]);

        $highSimilarities = ProductSimilarity::withoutGlobalScopes()->withMinScore(0.8)->get();
        $this->assertTrue($highSimilarities->contains($highSimilarity));
        $this->assertFalse($highSimilarities->contains($lowSimilarity));
    }

    public function test_product_similarity_scope_ordered_by_similarity(): void
    {
        $lowSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.3]);
        $highSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.9]);
        $mediumSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.6]);

        $orderedSimilarities = ProductSimilarity::withoutGlobalScopes()->orderedBySimilarity()->get();
        $this->assertEquals($highSimilarity->id, $orderedSimilarities->first()->id);
        $this->assertEquals($lowSimilarity->id, $orderedSimilarities->last()->id);
    }

    public function test_product_similarity_scope_recent(): void
    {
        $recentSimilarity = ProductSimilarity::factory()->create(['calculated_at' => now()]);
        $oldSimilarity = ProductSimilarity::factory()->create(['calculated_at' => now()->subDays(10)]);

        $recentSimilarities = ProductSimilarity::withoutGlobalScopes()->recent()->get();
        $this->assertTrue($recentSimilarities->contains($recentSimilarity));
        $this->assertFalse($recentSimilarities->contains($oldSimilarity));
    }

    public function test_product_similarity_scope_recent_with_custom_days(): void
    {
        $recentSimilarity = ProductSimilarity::factory()->create(['calculated_at' => now()->subDays(5)]);
        $oldSimilarity = ProductSimilarity::factory()->create(['calculated_at' => now()->subDays(15)]);

        $recentSimilarities = ProductSimilarity::withoutGlobalScopes()->recent(10)->get();
        $this->assertTrue($recentSimilarities->contains($recentSimilarity));
        $this->assertFalse($recentSimilarities->contains($oldSimilarity));
    }

    public function test_product_similarity_table_name(): void
    {
        $similarity = new ProductSimilarity();
        $this->assertEquals('product_similarities', $similarity->getTable());
    }

    public function test_product_similarity_factory(): void
    {
        $similarity = ProductSimilarity::factory()->create();

        $this->assertInstanceOf(ProductSimilarity::class, $similarity);
        $this->assertNotEmpty($similarity->product_id);
        $this->assertNotEmpty($similarity->similar_product_id);
        $this->assertNotEmpty($similarity->algorithm_type);
        $this->assertIsString($similarity->similarity_score);
        $this->assertIsArray($similarity->calculation_data);
    }
}
