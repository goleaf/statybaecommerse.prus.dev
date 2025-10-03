<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $panel = Filament::getPanel('admin');
        $this->assertNotNull($panel, 'The admin panel must be registered for Filament tests.');

        Filament::setCurrentPanel($panel);

        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_products(): void
    {
        $product = Product::factory()->create([
            'name' => 'Alpha Product',
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertSee('Alpha Product')
            ->searchTable('Alpha Product')
            ->assertSee('Alpha Product');
    }

    public function test_can_create_product_via_filament_form(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Test Product',
                'slug' => 'test-product',
                'sku' => 'TEST-123',
                'price' => 199.99,
                'status' => 'draft',
                'is_visible' => true,
                'stock_quantity' => 10,
                'low_stock_threshold' => 2,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-123',
            'is_visible' => true,
            'stock_quantity' => 10,
            'low_stock_threshold' => 2,
        ]);
    }

    public function test_can_edit_product_via_filament_form(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Product',
            'slug' => 'original-product',
            'sku' => 'ORIG-001',
            'price' => 99.99,
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
        ]);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Product',
                'price' => 249.50,
                'status' => 'published',
                'is_visible' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'slug' => 'updated-product',
            'status' => 'published',
            'is_visible' => true,
            'price' => 249.50,
        ]);
    }

    public function test_can_duplicate_product_from_edit_page(): void
    {
        $product = Product::factory()->create([
            'name' => 'Duplicate Me',
            'slug' => 'duplicate-me',
            'sku' => 'DUP-001',
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
        ]);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->callAction('duplicate');

        $this->assertDatabaseHas('products', [
            'name' => 'Duplicate Me (Copy)',
            'slug' => 'duplicate-me-copy',
            'sku' => 'DUP-001-COPY',
            'is_visible' => false,
            'status' => 'published',
        ]);
    }

    public function test_can_delete_product_from_table(): void
    {
        $product = Product::factory()->create([
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->callTableAction('delete', $product->id)
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_can_bulk_feature_products(): void
    {
        $products = Product::factory()->count(3)->create([
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
            'is_featured' => false,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->callTableBulkAction('feature', $products)
            ->assertHasNoTableBulkActionErrors();

        foreach ($products as $product) {
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'is_featured' => true,
            ]);
        }
    }

    public function test_can_bulk_update_stock_levels(): void
    {
        $products = Product::factory()->count(2)->create([
            'stock_quantity' => 5,
            'low_stock_threshold' => 1,
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->callTableBulkAction('update_stock', $products, [
                'stock_quantity' => 25,
                'low_stock_threshold' => 4,
            ])
            ->assertHasNoTableBulkActionErrors();

        foreach ($products as $product) {
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'stock_quantity' => 25,
                'low_stock_threshold' => 4,
            ]);
        }
    }

    public function test_can_bulk_update_product_prices(): void
    {
        $products = Product::factory()->count(2)->create([
            'price' => 100.00,
            'compare_price' => 150.00,
            'cost_price' => 80.00,
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->callTableBulkAction('update_prices', $products, [
                'price_increase_percentage' => 10,
            ])
            ->assertHasNoTableBulkActionErrors();

        foreach ($products as $product) {
            $product->refresh();
            $this->assertSame(110.00, (float) $product->price);
            $this->assertSame(165.00, (float) $product->compare_price);
            $this->assertSame(88.00, (float) $product->cost_price);
        }
    }

    public function test_can_filter_products_by_brand(): void
    {
        $brand = Brand::factory()->create(['name' => 'Filament']);
        $matching = Product::factory()->create([
            'brand_id' => $brand->id,
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
            'name' => 'Filament Product',
        ]);
        $other = Product::factory()->create([
            'name' => 'Other Product',
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->filterTable('brand', $brand->id)
            ->assertSee('Filament Product')
            ->assertDontSee('Other Product');
    }

    public function test_draft_products_are_hidden_by_global_scopes(): void
    {
        $draft = Product::factory()->create([
            'name' => 'Hidden Draft Product',
            'status' => 'draft',
            'is_visible' => false,
        ]);

        Product::factory()->create([
            'name' => 'Visible Product',
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertSee('Visible Product')
            ->assertDontSee('Hidden Draft Product');

        $this->assertNotNull(Product::withoutGlobalScopes()->find($draft->id));
    }
}
