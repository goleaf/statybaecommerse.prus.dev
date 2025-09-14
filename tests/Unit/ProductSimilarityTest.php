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

        $this->assertIsFloat($similarity->similarity_score);
        $this->assertEquals(0.75, $similarity->similarity_score);
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

    public function test_product_similarity_scope_by_product(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $similarity1 = ProductSimilarity::factory()->create(['product_id' => $product1->id]);
        $similarity2 = ProductSimilarity::factory()->create(['product_id' => $product2->id]);

        $product1Similarities = ProductSimilarity::withoutGlobalScopes()->byProduct($product1->id)->get();
        $this->assertTrue($product1Similarities->contains($similarity1));
        $this->assertFalse($product1Similarities->contains($similarity2));
    }

    public function test_product_similarity_scope_by_similar_product(): void
    {
        $similarProduct1 = Product::factory()->create();
        $similarProduct2 = Product::factory()->create();
        
        $similarity1 = ProductSimilarity::factory()->create(['similar_product_id' => $similarProduct1->id]);
        $similarity2 = ProductSimilarity::factory()->create(['similar_product_id' => $similarProduct2->id]);

        $similarProduct1Similarities = ProductSimilarity::withoutGlobalScopes()->bySimilarProduct($similarProduct1->id)->get();
        $this->assertTrue($similarProduct1Similarities->contains($similarity1));
        $this->assertFalse($similarProduct1Similarities->contains($similarity2));
    }

    public function test_product_similarity_scope_high_similarity(): void
    {
        $highSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.9]);
        $lowSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.3]);

        $highSimilarities = ProductSimilarity::withoutGlobalScopes()->highSimilarity()->get();
        $this->assertTrue($highSimilarities->contains($highSimilarity));
        $this->assertFalse($highSimilarities->contains($lowSimilarity));
    }

    public function test_product_similarity_scope_medium_similarity(): void
    {
        $mediumSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.6]);
        $lowSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.3]);
        $highSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.9]);

        $mediumSimilarities = ProductSimilarity::withoutGlobalScopes()->mediumSimilarity()->get();
        $this->assertTrue($mediumSimilarities->contains($mediumSimilarity));
        $this->assertFalse($mediumSimilarities->contains($lowSimilarity));
        $this->assertFalse($mediumSimilarities->contains($highSimilarity));
    }

    public function test_product_similarity_scope_low_similarity(): void
    {
        $lowSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.2]);
        $highSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.8]);

        $lowSimilarities = ProductSimilarity::withoutGlobalScopes()->lowSimilarity()->get();
        $this->assertTrue($lowSimilarities->contains($lowSimilarity));
        $this->assertFalse($lowSimilarities->contains($highSimilarity));
    }

    public function test_product_similarity_scope_recently_calculated(): void
    {
        $recentSimilarity = ProductSimilarity::factory()->create(['calculated_at' => now()]);
        $oldSimilarity = ProductSimilarity::factory()->create(['calculated_at' => now()->subDays(10)]);

        $recentSimilarities = ProductSimilarity::withoutGlobalScopes()->recentlyCalculated()->get();
        $this->assertTrue($recentSimilarities->contains($recentSimilarity));
        $this->assertFalse($recentSimilarities->contains($oldSimilarity));
    }

    public function test_product_similarity_scope_by_score_range(): void
    {
        $similarity1 = ProductSimilarity::factory()->create(['similarity_score' => 0.4]);
        $similarity2 = ProductSimilarity::factory()->create(['similarity_score' => 0.6]);
        $similarity3 = ProductSimilarity::factory()->create(['similarity_score' => 0.8]);

        $rangeSimilarities = ProductSimilarity::withoutGlobalScopes()->byScoreRange(0.5, 0.7)->get();
        $this->assertFalse($rangeSimilarities->contains($similarity1));
        $this->assertTrue($rangeSimilarities->contains($similarity2));
        $this->assertFalse($rangeSimilarities->contains($similarity3));
    }

    public function test_product_similarity_get_similarity_level_method(): void
    {
        $highSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.9]);
        $mediumSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.6]);
        $lowSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.3]);

        $this->assertEquals('high', $highSimilarity->getSimilarityLevel());
        $this->assertEquals('medium', $mediumSimilarity->getSimilarityLevel());
        $this->assertEquals('low', $lowSimilarity->getSimilarityLevel());
    }

    public function test_product_similarity_get_similarity_percentage_method(): void
    {
        $similarity = ProductSimilarity::factory()->create(['similarity_score' => 0.75]);

        $this->assertEquals(75.0, $similarity->getSimilarityPercentage());
    }

    public function test_product_similarity_is_high_similarity_method(): void
    {
        $highSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.8]);
        $lowSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.4]);

        $this->assertTrue($highSimilarity->isHighSimilarity());
        $this->assertFalse($lowSimilarity->isHighSimilarity());
    }

    public function test_product_similarity_is_medium_similarity_method(): void
    {
        $mediumSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.6]);
        $highSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.8]);
        $lowSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.3]);

        $this->assertTrue($mediumSimilarity->isMediumSimilarity());
        $this->assertFalse($highSimilarity->isMediumSimilarity());
        $this->assertFalse($lowSimilarity->isMediumSimilarity());
    }

    public function test_product_similarity_is_low_similarity_method(): void
    {
        $lowSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.3]);
        $highSimilarity = ProductSimilarity::factory()->create(['similarity_score' => 0.8]);

        $this->assertTrue($lowSimilarity->isLowSimilarity());
        $this->assertFalse($highSimilarity->isLowSimilarity());
    }

    public function test_product_similarity_get_algorithm_label_method(): void
    {
        $cosineSimilarity = ProductSimilarity::factory()->create(['algorithm_type' => 'cosine_similarity']);
        $euclideanSimilarity = ProductSimilarity::factory()->create(['algorithm_type' => 'euclidean_distance']);

        $this->assertEquals('Cosine Similarity', $cosineSimilarity->getAlgorithmLabel());
        $this->assertEquals('Euclidean Distance', $euclideanSimilarity->getAlgorithmLabel());
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
        $this->assertIsFloat($similarity->similarity_score);
        $this->assertIsArray($similarity->calculation_data);
    }
}
