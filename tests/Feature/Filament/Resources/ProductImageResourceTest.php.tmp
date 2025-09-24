<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

final class ProductImageResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
        ]);

        // Set up storage
        Storage::fake('public');
    }

    public function test_can_list_product_images(): void
    {
        // Create test data
        ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => 'product-images/test-image.jpg',
            'alt_text' => 'Test image',
            'sort_order' => 1,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => ProductImageResource::class,
        ])
            ->assertCanSeeTableRecords(ProductImage::all());
    }

    public function test_can_create_product_image(): void
    {
        $file = UploadedFile::fake()->image('test-image.jpg', 800, 600);

        Livewire::test(CreateRecord::class, [
            'resource' => ProductImageResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'path' => $file,
                'alt_text' => 'Test product image',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_images', [
            'product_id' => $this->product->id,
            'alt_text' => 'Test product image',
            'sort_order' => 1,
        ]);
    }

    public function test_can_edit_product_image(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => 'product-images/test-image.jpg',
            'alt_text' => 'Original alt text',
            'sort_order' => 1,
        ]);

        Livewire::test(EditRecord::class, [
            'resource' => ProductImageResource::class,
            'record' => $image->getRouteKey(),
        ])
            ->fillForm([
                'alt_text' => 'Updated alt text',
                'sort_order' => 2,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_images', [
            'id' => $image->id,
            'alt_text' => 'Updated alt text',
            'sort_order' => 2,
        ]);
    }

    public function test_can_filter_by_product(): void
    {
        $product2 = Product::factory()->create();

        ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => 'product-images/image1.jpg',
        ]);

        ProductImage::factory()->create([
            'product_id' => $product2->id,
            'path' => 'product-images/image2.jpg',
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => ProductImageResource::class,
        ])
            ->filterTable('product_id', $this->product->id)
            ->assertCanSeeTableRecords(ProductImage::where('product_id', $this->product->id)->get())
            ->assertCanNotSeeTableRecords(ProductImage::where('product_id', $product2->id)->get());
    }

    public function test_can_sort_by_sort_order(): void
    {
        $image1 = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => 2,
        ]);

        $image2 = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => 1,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => ProductImageResource::class,
        ])
            ->sortTable('sort_order', 'asc')
            ->assertCanSeeTableRecords([$image2, $image1]);
    }

    public function test_can_sort_by_created_at(): void
    {
        $image1 = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'created_at' => now()->subDay(),
        ]);

        $image2 = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'created_at' => now(),
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => ProductImageResource::class,
        ])
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$image2, $image1]);
    }

    public function test_navigation_group_is_products(): void
    {
        $this->assertEquals(
            NavigationGroup::Products,
            ProductImageResource::getNavigationGroup()
        );
    }

    public function test_navigation_sort_is_14(): void
    {
        $this->assertEquals(14, ProductImageResource::getNavigationSort());
    }

    public function test_navigation_icon_is_photo(): void
    {
        $this->assertEquals('heroicon-o-photo', ProductImageResource::getNavigationIcon());
    }

    public function test_has_correct_pages(): void
    {
        $pages = ProductImageResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_form_validation_requires_product(): void
    {
        Livewire::test(CreateRecord::class, [
            'resource' => ProductImageResource::class,
        ])
            ->fillForm([
                'alt_text' => 'Test image',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasFormErrors(['product_id']);
    }

    public function test_can_bulk_delete_product_images(): void
    {
        $image1 = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => 'product-images/image1.jpg',
        ]);

        $image2 = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => 'product-images/image2.jpg',
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => ProductImageResource::class,
        ])
            ->callTableBulkAction('delete', [$image1, $image2])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseMissing('product_images', [
            'id' => $image1->id,
        ]);

        $this->assertDatabaseMissing('product_images', [
            'id' => $image2->id,
        ]);
    }

    public function test_can_upload_image_file(): void
    {
        $file = UploadedFile::fake()->image('product-image.jpg', 1200, 800);

        Livewire::test(CreateRecord::class, [
            'resource' => ProductImageResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'path' => $file,
                'alt_text' => 'Uploaded image',
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Verify file was stored
        Storage::disk('public')->assertExists('product-images/'.$file->hashName());
    }

    public function test_alt_text_is_optional(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');

        Livewire::test(CreateRecord::class, [
            'resource' => ProductImageResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'path' => $file,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_images', [
            'product_id' => $this->product->id,
            'alt_text' => null,
        ]);
    }

    public function test_sort_order_defaults_to_zero(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');

        Livewire::test(CreateRecord::class, [
            'resource' => ProductImageResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'path' => $file,
                'alt_text' => 'Test image',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_images', [
            'product_id' => $this->product->id,
            'sort_order' => 0,
        ]);
    }

    public function test_can_handle_multiple_images_for_same_product(): void
    {
        $file1 = UploadedFile::fake()->image('image1.jpg');
        $file2 = UploadedFile::fake()->image('image2.jpg');

        Livewire::test(CreateRecord::class, [
            'resource' => ProductImageResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'path' => $file1,
                'alt_text' => 'First image',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateRecord::class, [
            'resource' => ProductImageResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'path' => $file2,
                'alt_text' => 'Second image',
                'sort_order' => 2,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertEquals(2, ProductImage::where('product_id', $this->product->id)->count());
    }

    public function test_image_column_displays_correctly(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => 'product-images/test-image.jpg',
            'alt_text' => 'Test image',
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => ProductImageResource::class,
        ])
            ->assertCanSeeTableRecords([$image]);
    }
}
