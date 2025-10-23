<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Jobs\ProcessExportJob;
use App\Models\Brand;
use App\Models\Export;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_products(): void
    {
        $product = Product::factory()->create([
            'name'         => 'Alpha Product',
            'status'       => 'published',
            'published_at' => now(),
            'is_visible'   => true,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertSee('Alpha Product')
            ->searchTable('Alpha Product')
            ->assertSee('Alpha Product');
    }

    /**
     * Ensure the new inline chart column is registered on the product list table.
     */
    public function test_list_page_includes_sales_sparkline_column(): void
    {
        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertTableColumnExists('sales_sparkline');
    }

    public function test_can_create_product_via_filament_form(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name'                => 'Test Product',
                'slug'                => 'test-product',
                'sku'                 => 'TEST-123',
                'price'               => 199.99,
                'status'              => 'draft',
                'is_visible'          => true,
                'stock_quantity'      => 10,
                'low_stock_threshold' => 2,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name'                => 'Test Product',
            'slug'                => 'test-product',
            'sku'                 => 'TEST-123',
            'is_visible'          => true,
            'stock_quantity'      => 10,
            'low_stock_threshold' => 2,
        ]);
    }

    public function test_can_edit_product_via_filament_form(): void
    {
        $product = Product::factory()->create([
            'name'         => 'Original Product',
            'slug'         => 'original-product',
            'sku'          => 'ORIG-001',
            'price'        => 99.99,
            'status'       => 'published',
            'published_at' => now(),
            'is_visible'   => true,
        ]);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'name'       => 'Updated Product',
                'price'      => 249.50,
                'status'     => 'published',
                'is_visible' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'id'         => $product->id,
            'name'       => 'Updated Product',
            'slug'       => 'updated-product',
            'status'     => 'published',
            'is_visible' => true,
            'price'      => 249.50,
        ]);
    }

    public function test_can_duplicate_product_from_edit_page(): void
    {
        $product = Product::factory()->create([
            'name'         => 'Duplicate Me',
            'slug'         => 'duplicate-me',
            'sku'          => 'DUP-001',
            'status'       => 'published',
            'published_at' => now(),
            'is_visible'   => true,
        ]);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->callAction('duplicate');

        $this->assertDatabaseHas('products', [
            'name'       => 'Duplicate Me (Copy)',
            'slug'       => 'duplicate-me-copy',
            'sku'        => 'DUP-001-COPY',
            'is_visible' => false,
            'status'     => 'published',
        ]);
    }

    public function test_can_delete_product_from_table(): void
    {
        $product = Product::factory()->create([
            'status'       => 'published',
            'published_at' => now(),
            'is_visible'   => true,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->callTableAction('delete', $product->id)
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_can_queue_product_export(): void
    {
        config()->set('filesystems.default', 'public');
        Storage::fake('public');
        Notification::fake();
        Bus::fake();

        $products = Product::factory()->count(2)->create();

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->callTableBulkAction('export_selected', $products, [
                'format'  => 'xlsx',
                'columns' => ['sku', 'name'],
            ])
            ->assertHasNoTableBulkActionErrors();

        $export = Export::query()->latest()->first();

        $this->assertNotNull($export);
        $this->assertSame('xlsx', $export->format);

        Bus::assertDispatched(ProcessExportJob::class, fn (ProcessExportJob $job): bool => $job->exportId === $export->getKey());
    }

    public function test_can_bulk_feature_products(): void
    {
        $products = Product::factory()->count(3)->create([
            'status'       => 'published',
            'published_at' => now(),
            'is_visible'   => true,
            'is_featured'  => false,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->callTableBulkAction('feature', $products)
            ->assertHasNoTableBulkActionErrors();

        foreach ($products as $product) {
            $this->assertDatabaseHas('products', [
                'id'          => $product->id,
                'is_featured' => true,
            ]);
        }
    }

    public function test_can_bulk_update_stock_levels(): void
    {
        $products = Product::factory()->count(2)->create([
            'stock_quantity'      => 5,
            'low_stock_threshold' => 1,
            'status'              => 'published',
            'published_at'        => now(),
            'is_visible'          => true,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->callTableBulkAction('update_stock', $products, [
                'stock_quantity'      => 25,
                'low_stock_threshold' => 4,
            ])
            ->assertHasNoTableBulkActionErrors();

        foreach ($products as $product) {
            $this->assertDatabaseHas('products', [
                'id'                  => $product->id,
                'stock_quantity'      => 25,
                'low_stock_threshold' => 4,
            ]);
        }
    }

    public function test_can_bulk_update_product_prices(): void
    {
        $products = Product::factory()->count(2)->create([
            'price'         => 100.00,
            'compare_price' => 150.00,
            'cost_price'    => 80.00,
            'status'        => 'published',
            'published_at'  => now(),
            'is_visible'    => true,
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
            'brand_id'     => $brand->id,
            'status'       => 'published',
            'published_at' => now(),
            'is_visible'   => true,
            'name'         => 'Filament Product',
        ]);
        $other = Product::factory()->create([
            'name'         => 'Other Product',
            'status'       => 'published',
            'published_at' => now(),
            'is_visible'   => true,
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
            'name'       => 'Hidden Draft Product',
            'status'     => 'draft',
            'is_visible' => false,
        ]);

        Product::factory()->create([
            'name'         => 'Visible Product',
            'status'       => 'published',
            'published_at' => now(),
            'is_visible'   => true,
        ]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertSee('Visible Product')
            ->assertDontSee('Hidden Draft Product');

        $this->assertNotNull(Product::withoutGlobalScopes()->find($draft->id));
    }

    public function test_product_images_are_uploaded_and_displayed(): void
    {
        config(['filesystems.default' => 'public']);
        Storage::fake('public');

        $image = UploadedFile::fake()->image('product.jpg');

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name'                => 'Image Product',
                'slug'                => 'image-product',
                'sku'                 => 'IMG-001',
                'price'               => 49.99,
                'status'              => 'draft',
                'is_visible'          => true,
                'stock_quantity'      => 5,
                'low_stock_threshold' => 1,
                'images'              => [$image],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::query()->with('images')->first();

        $this->assertNotNull($product);
        $this->assertCount(1, $product->images);

        $storedPath = $product->images->first()->path;

        Storage::disk('public')->assertExists($storedPath);
        $this->assertSame(Storage::disk('public')->url($storedPath), $product->main_image);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->assertSet('data.images', [$storedPath]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertTableColumnStateSet('main_image', $product->main_image, $product);
    }
}
