<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSimilarity;
use App\Services\Recommendations\ContentBasedRecommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ContentBasedRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_caches_the_actual_similarity_score(): void
    {
        // Build a simple catalog slice where one product should be a perfect
        // match based on category, brand, and price so we know the expected
        // similarity score ahead of time (0.9 with the default weight mix).
        Route::name('brands.show')->get('/brands/{brand}', static fn () => 'brand');
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $primaryProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'price'    => 45.00,
        ]);
        $primaryProduct->categories()->attach($category);

        $similarProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'price'    => 44.00,
        ]);
        $similarProduct->categories()->attach($category);

        // Introduce a decoy record so the recommendation routine still needs
        // to evaluate multiple candidates while picking the best-scoring match.
        $decoyCategory = Category::factory()->create();
        $decoyProduct = Product::factory()->create(['price' => 600.00]);
        $decoyProduct->categories()->attach($decoyCategory);

        // Route already registered above, so nothing else required here.

        $service = new ContentBasedRecommendation(['use_cached_similarities' => false]);

        $recommendations = $service->getRecommendations(null, $primaryProduct);

        self::assertCount(1, $recommendations);
        self::assertTrue($recommendations->first()->is($similarProduct));

        $similarity = ProductSimilarity::withoutGlobalScopes()
            ->where('product_id', $primaryProduct->id)
            ->where('similar_product_id', $similarProduct->id)
            ->where('algorithm_type', 'content_based')
            ->first();

        self::assertNotNull($similarity);
        // The decimal cast on the model returns a string with six decimal
        // places, so compare against the canonical representation.
        self::assertSame('0.900000', $similarity->similarity_score);
    }
}
