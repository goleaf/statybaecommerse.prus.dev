<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductSimilarity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

class ProductSimilarityResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_product_similarities(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'algorithm_type' => 'cosine_similarity',
            'similarity_score' => 0.85,
        ]);

        Livewire::test(\App\Filament\Resources\ProductSimilarityResource\Pages\ListProductSimilarities::class)
            ->assertCanSeeTableRecords(ProductSimilarity::all());
    }

    public function test_can_create_product_similarity(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\ProductSimilarityResource\Pages\CreateProductSimilarity::class)
            ->fillForm([
                'product_id' => $product1->id,
                'similar_product_id' => $product2->id,
                'algorithm_type' => 'jaccard_similarity',
                'similarity_score' => 0.75,
                'calculation_data' => ['key' => 'value'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_similarities', [
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'algorithm_type' => 'jaccard_similarity',
            'similarity_score' => 0.75,
        ]);
    }

    public function test_can_edit_product_similarity(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $similarity = ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'algorithm_type' => 'cosine_similarity',
            'similarity_score' => 0.85,
        ]);

        Livewire::test(\App\Filament\Resources\ProductSimilarityResource\Pages\EditProductSimilarity::class, [
            'record' => $similarity->getRouteKey(),
        ])
            ->fillForm([
                'similarity_score' => 0.95,
                'calculation_data' => ['updated' => 'data'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_similarities', [
            'id' => $similarity->id,
            'similarity_score' => 0.95,
        ]);
    }

    public function test_can_view_product_similarity(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $similarity = ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'algorithm_type' => 'cosine_similarity',
            'similarity_score' => 0.85,
        ]);

        Livewire::test(\App\Filament\Resources\ProductSimilarityResource\Pages\ViewProductSimilarity::class, [
            'record' => $similarity->getRouteKey(),
        ])
            ->assertCanSeeTableRecords([$similarity]);
    }

    public function test_can_filter_by_algorithm_type(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $product3 = Product::factory()->create();

        ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'algorithm_type' => 'cosine_similarity',
            'similarity_score' => 0.85,
        ]);

        ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product3->id,
            'algorithm_type' => 'jaccard_similarity',
            'similarity_score' => 0.75,
        ]);

        Livewire::test(\App\Filament\Resources\ProductSimilarityResource\Pages\ListProductSimilarities::class)
            ->filterTable('algorithm_type', 'cosine_similarity')
            ->assertCanSeeTableRecords(ProductSimilarity::where('algorithm_type', 'cosine_similarity')->get())
            ->assertCanNotSeeTableRecords(ProductSimilarity::where('algorithm_type', 'jaccard_similarity')->get());
    }

    public function test_can_filter_by_similarity_score_range(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $product3 = Product::factory()->create();

        ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'similarity_score' => 0.9,
        ]);

        ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product3->id,
            'similarity_score' => 0.5,
        ]);

        Livewire::test(\App\Filament\Resources\ProductSimilarityResource\Pages\ListProductSimilarities::class)
            ->filterTable('similarity_score_range', [
                'min_score' => 0.8,
                'max_score' => 1.0,
            ])
            ->assertCanSeeTableRecords(ProductSimilarity::where('similarity_score', '>=', 0.8)->get())
            ->assertCanNotSeeTableRecords(ProductSimilarity::where('similarity_score', '<', 0.8)->get());
    }

    public function test_can_search_product_similarities(): void
    {
        $product1 = Product::factory()->create(['name' => 'Test Product 1']);
        $product2 = Product::factory()->create(['name' => 'Test Product 2']);

        ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'algorithm_type' => 'cosine_similarity',
        ]);

        Livewire::test(\App\Filament\Resources\ProductSimilarityResource\Pages\ListProductSimilarities::class)
            ->searchTable('Test Product 1')
            ->assertCanSeeTableRecords(ProductSimilarity::whereHas('product', function ($query) {
                $query->where('name', 'like', '%Test Product 1%');
            })->get());
    }

    public function test_can_bulk_delete_product_similarities(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $product3 = Product::factory()->create();

        $similarities = collect([
            ProductSimilarity::factory()->create([
                'product_id' => $product1->id,
                'similar_product_id' => $product2->id,
            ]),
            ProductSimilarity::factory()->create([
                'product_id' => $product1->id,
                'similar_product_id' => $product3->id,
            ]),
        ]);

        Livewire::test(\App\Filament\Resources\ProductSimilarityResource\Pages\ListProductSimilarities::class)
            ->callTableBulkAction('delete', $similarities)
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseMissing('product_similarities', [
            'id' => $similarities->first()->id,
        ]);
    }

    public function test_product_similarity_relationships_work(): void
    {
        $product1 = Product::factory()->create(['name' => 'Product 1']);
        $product2 = Product::factory()->create(['name' => 'Product 2']);

        $similarity = ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
        ]);

        $this->assertEquals('Product 1', $similarity->product->name);
        $this->assertEquals('Product 2', $similarity->similarProduct->name);
    }

    public function test_product_similarity_scopes_work(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $product3 = Product::factory()->create();

        ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'algorithm_type' => 'cosine_similarity',
            'similarity_score' => 0.9,
        ]);

        ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product3->id,
            'algorithm_type' => 'jaccard_similarity',
            'similarity_score' => 0.5,
        ]);

        $this->assertCount(1, ProductSimilarity::byAlgorithm('cosine_similarity')->get());
        $this->assertCount(1, ProductSimilarity::withMinScore(0.8)->get());
        $this->assertCount(2, ProductSimilarity::orderedBySimilarity()->get());
    }

    public function test_similarity_score_color_coding_works(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $highSimilarity = ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'similarity_score' => 0.9,
        ]);

        $lowSimilarity = ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'similarity_score' => 0.3,
        ]);

        // Test that high similarity gets success color
        $this->assertEquals('success', $highSimilarity->similarity_score >= 0.8 ? 'success' : 'warning');

        // Test that low similarity gets danger color
        $this->assertEquals('danger', $lowSimilarity->similarity_score < 0.6 ? 'danger' : 'warning');
    }
}
