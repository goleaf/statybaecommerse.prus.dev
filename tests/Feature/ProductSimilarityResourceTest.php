<?php

declare(strict_types=1);

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

        $this->actingAs(User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_product_similarities(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        ProductSimilarity::factory()->create([
            'product_id'         => $product1->id,
            'similar_product_id' => $product2->id,
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
                'product_id'         => $product1->id,
                'similar_product_id' => $product2->id,
                'calculation_data'   => ['key' => 'value'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_similarities', [
            'product_id'         => $product1->id,
            'similar_product_id' => $product2->id,
        ]);
    }

    public function test_can_edit_product_similarity(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $similarity = ProductSimilarity::factory()->create([
            'product_id'         => $product1->id,
            'similar_product_id' => $product2->id,
        ]);

        Livewire::test(\App\Filament\Resources\ProductSimilarityResource\Pages\EditProductSimilarity::class, [
            'record' => $similarity->getRouteKey(),
        ])
            ->fillForm([
                'calculation_data' => ['updated' => 'data'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_similarities', [
            'id'               => $similarity->id,
            'calculation_data' => json_encode(['updated' => 'data']),
        ]);
    }

    public function test_can_view_product_similarity(): void
    {
        $product1 = Product::factory()->create(['name' => 'Product A']);
        $product2 = Product::factory()->create(['name' => 'Product B']);

        $similarity = ProductSimilarity::factory()->create([
            'product_id'         => $product1->id,
            'similar_product_id' => $product2->id,
        ]);

        Livewire::test(\App\Filament\Resources\ProductSimilarityResource\Pages\ViewProductSimilarity::class, [
            'record' => $similarity->getRouteKey(),
        ])
            ->assertSuccessful();
    }

    public function test_can_search_product_similarities(): void
    {
        $product1 = Product::factory()->create(['name' => 'Test Product 1']);
        $product2 = Product::factory()->create(['name' => 'Test Product 2']);

        ProductSimilarity::factory()->create([
            'product_id'         => $product1->id,
            'similar_product_id' => $product2->id,
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
                'product_id'         => $product1->id,
                'similar_product_id' => $product2->id,
            ]),
            ProductSimilarity::factory()->create([
                'product_id'         => $product1->id,
                'similar_product_id' => $product3->id,
            ]),
        ]);

        Livewire::test(\App\Filament\Resources\ProductSimilarityResource\Pages\ListProductSimilarities::class)
            ->callTableBulkAction('delete', $similarities->pluck('id')->all())
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
            'product_id'         => $product1->id,
            'similar_product_id' => $product2->id,
        ]);

        $this->assertEquals('Product 1', $similarity->product->name);
        $this->assertEquals('Product 2', $similarity->similarProduct->name);
    }

    public function test_product_similarity_scopes_work(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        ProductSimilarity::factory()->create([
            'product_id'         => $product1->id,
            'similar_product_id' => $product2->id,
        ]);

        $this->assertCount(1, ProductSimilarity::recent()->get());
    }
}
