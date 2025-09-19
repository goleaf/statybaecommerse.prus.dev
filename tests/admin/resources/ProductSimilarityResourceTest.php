<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductSimilarity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductSimilarityResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_list_product_similarities(): void
    {
        ProductSimilarity::factory()->create();
        ProductSimilarity::factory()->create();

        $this
            ->get(route('filament.admin.resources.product-similarities.index'))
            ->assertOk();
    }

    public function test_can_create_product_similarity(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $similarityData = [
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'algorithm_type' => 'cosine_similarity',
            'similarity_score' => 0.85,
            'calculation_data' => ['feature_1' => 0.7, 'feature_2' => 0.9],
        ];

        Livewire::test('filament.admin.resources.product-similarities.create')
            ->fillForm($similarityData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_similarities', [
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
            'algorithm_type' => 'cosine_similarity',
            'similarity_score' => 0.85,
        ]);
    }

    public function test_can_view_product_similarity(): void
    {
        $similarity = ProductSimilarity::factory()->create();

        $this
            ->get(route('filament.admin.resources.product-similarities.view', $similarity))
            ->assertOk();
    }

    public function test_can_edit_product_similarity(): void
    {
        $similarity = ProductSimilarity::factory()->create();
        $newProduct = Product::factory()->create();

        $updateData = [
            'similar_product_id' => $newProduct->id,
            'similarity_score' => 0.95,
            'algorithm_type' => 'jaccard_similarity',
        ];

        Livewire::test('filament.admin.resources.product-similarities.edit', ['record' => $similarity->id])
            ->fillForm($updateData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_similarities', [
            'id' => $similarity->id,
            'similar_product_id' => $newProduct->id,
            'similarity_score' => 0.95,
            'algorithm_type' => 'jaccard_similarity',
        ]);
    }

    public function test_can_delete_product_similarity(): void
    {
        $similarity = ProductSimilarity::factory()->create();

        Livewire::test('filament.admin.resources.product-similarities.edit', ['record' => $similarity->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('product_similarities', [
            'id' => $similarity->id,
        ]);
    }

    public function test_can_filter_product_similarities_by_algorithm(): void
    {
        ProductSimilarity::factory()->create(['algorithm_type' => 'cosine_similarity']);
        ProductSimilarity::factory()->create(['algorithm_type' => 'jaccard_similarity']);

        Livewire::test('filament.admin.resources.product-similarities.index')
            ->filterTable('algorithm_type', 'cosine_similarity')
            ->assertCanSeeTableRecords(
                ProductSimilarity::where('algorithm_type', 'cosine_similarity')->get()
            );
    }

    public function test_can_filter_product_similarities_by_product(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        ProductSimilarity::factory()->create(['product_id' => $product1->id]);
        ProductSimilarity::factory()->create(['product_id' => $product2->id]);

        Livewire::test('filament.admin.resources.product-similarities.index')
            ->filterTable('product_id', $product1->id)
            ->assertCanSeeTableRecords(
                ProductSimilarity::where('product_id', $product1->id)->get()
            );
    }

    public function test_can_filter_product_similarities_by_score_range(): void
    {
        ProductSimilarity::factory()->create(['similarity_score' => 0.9]);
        ProductSimilarity::factory()->create(['similarity_score' => 0.5]);
        ProductSimilarity::factory()->create(['similarity_score' => 0.3]);

        Livewire::test('filament.admin.resources.product-similarities.index')
            ->filterTable('similarity_score_range', [
                'min_score' => 0.6,
                'max_score' => 1.0,
            ])
            ->assertCanSeeTableRecords(
                ProductSimilarity::where('similarity_score', '>=', 0.6)->get()
            );
    }

    public function test_can_use_tabs_to_filter_similarities(): void
    {
        ProductSimilarity::factory()->create(['similarity_score' => 0.9]);
        ProductSimilarity::factory()->create(['similarity_score' => 0.7]);
        ProductSimilarity::factory()->create(['similarity_score' => 0.4]);

        Livewire::test('filament.admin.resources.product-similarities.index')
            ->assertCanSeeTableRecords(ProductSimilarity::all());
    }

    public function test_product_similarity_relationships_work_correctly(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $similarity = ProductSimilarity::factory()->create([
            'product_id' => $product1->id,
            'similar_product_id' => $product2->id,
        ]);

        $this->assertInstanceOf(Product::class, $similarity->product);
        $this->assertEquals($product1->id, $similarity->product->id);

        $this->assertInstanceOf(Product::class, $similarity->similarProduct);
        $this->assertEquals($product2->id, $similarity->similarProduct->id);
    }

    public function test_can_bulk_delete_product_similarities(): void
    {
        $similarity1 = ProductSimilarity::factory()->create();
        $similarity2 = ProductSimilarity::factory()->create();

        Livewire::test('filament.admin.resources.product-similarities.index')
            ->callTableBulkAction('delete', [$similarity1->id, $similarity2->id]);

        $this->assertDatabaseMissing('product_similarities', ['id' => $similarity1->id]);
        $this->assertDatabaseMissing('product_similarities', ['id' => $similarity2->id]);
    }

    public function test_similarity_score_validation(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Test invalid similarity score (greater than 1)
        Livewire::test('filament.admin.resources.product-similarities.create')
            ->fillForm([
                'product_id' => $product1->id,
                'similar_product_id' => $product2->id,
                'algorithm_type' => 'cosine_similarity',
                'similarity_score' => 1.5,  // Invalid
            ])
            ->call('create')
            ->assertHasFormErrors(['similarity_score']);

        // Test invalid similarity score (negative)
        Livewire::test('filament.admin.resources.product-similarities.create')
            ->fillForm([
                'product_id' => $product1->id,
                'similar_product_id' => $product2->id,
                'algorithm_type' => 'cosine_similarity',
                'similarity_score' => -0.1,  // Invalid
            ])
            ->call('create')
            ->assertHasFormErrors(['similarity_score']);
    }

    public function test_calculated_at_is_set_automatically(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Livewire::test('filament.admin.resources.product-similarities.create')
            ->fillForm([
                'product_id' => $product1->id,
                'similar_product_id' => $product2->id,
                'algorithm_type' => 'cosine_similarity',
                'similarity_score' => 0.8,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $similarity = ProductSimilarity::latest()->first();
        $this->assertNotNull($similarity->calculated_at);
    }
}
