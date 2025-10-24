<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
        ]);

        Storage::fake('public');
    }

    public function test_product_image_can_be_created(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => 'product-images/test.jpg',
            'alt_text' => 'Test Image',
            'sort_order' => 1,
        ]);

        expect($image)->toBeInstanceOf(ProductImage::class);
        expect($image->product_id)->toBe($this->product->id);
        expect($image->path)->toBe('product-images/test.jpg');
        expect($image->alt_text)->toBe('Test Image');
        expect($image->sort_order)->toBe(1);
        expect($image->is_active)->toBeTrue();
    }

    public function test_product_image_has_correct_fillable_attributes(): void
    {
        $fillable = (new ProductImage)->getFillable();

        expect($fillable)->toContain('product_id');
        expect($fillable)->toContain('path');
        expect($fillable)->toContain('alt_text');
        expect($fillable)->toContain('sort_order');
        expect($fillable)->toContain('is_active');
    }

    public function test_product_image_casts_attributes_correctly(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => '5',
            'is_active' => '1',
        ]);

        expect($image->product_id)->toBeInt();
        expect($image->sort_order)->toBeInt()->toBe(5);
        expect($image->is_active)->toBeBool()->toBeTrue();
    }

    public function test_product_image_belongs_to_product(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
        ]);

        expect($image->product)->toBeInstanceOf(Product::class);
        expect($image->product->id)->toBe($this->product->id);
    }

    public function test_product_has_many_images(): void
    {
        $images = ProductImage::factory()->count(3)->create([
            'product_id' => $this->product->id,
        ]);

        $this->product->refresh();

        expect($this->product->images)->toHaveCount(3);
        expect($this->product->images->first())->toBeInstanceOf(ProductImage::class);
    }

    public function test_scope_active_returns_only_active_images(): void
    {
        ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'is_active' => true,
        ]);

        ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'is_active' => false,
        ]);

        $activeImages = ProductImage::withoutGlobalScope('App\Models\Scopes\ActiveScope')->active()->get();

        expect($activeImages)->toHaveCount(1);
        expect($activeImages->first()->is_active)->toBeTrue();
    }

    public function test_scope_for_product_returns_images_for_specific_product(): void
    {
        $otherProduct = Product::factory()->create([
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
        ]);

        ProductImage::factory()->count(2)->create([
            'product_id' => $this->product->id,
        ]);

        ProductImage::factory()->create([
            'product_id' => $otherProduct->id,
        ]);

        $productImages = ProductImage::forProduct($this->product->id)->get();

        expect($productImages)->toHaveCount(2);
        expect($productImages->every(fn($img) => $img->product_id === $this->product->id))->toBeTrue();
    }

    public function test_scope_ordered_returns_images_in_sort_order(): void
    {
        ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => 3,
        ]);

        ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => 1,
        ]);

        ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => 2,
        ]);

        $orderedImages = ProductImage::ordered()->get();

        expect($orderedImages[0]->sort_order)->toBe(1);
        expect($orderedImages[1]->sort_order)->toBe(2);
        expect($orderedImages[2]->sort_order)->toBe(3);
    }

    public function test_scope_primary_returns_first_image(): void
    {
        $firstImage = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => 1,
        ]);

        ProductImage::factory()->count(2)->create([
            'product_id' => $this->product->id,
            'sort_order' => 2,
        ]);

        $primaryImage = ProductImage::primary()->first();

        expect($primaryImage->id)->toBe($firstImage->id);
    }

    public function test_is_primary_returns_true_for_first_image(): void
    {
        $firstImage = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => 0,
        ]);

        $secondImage = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => 1,
        ]);

        expect($firstImage->isPrimary())->toBeTrue();
        expect($secondImage->isPrimary())->toBeFalse();
    }

    public function test_get_alt_text_or_default_returns_alt_text_when_set(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'alt_text' => 'Custom Alt Text',
        ]);

        expect($image->getAltTextOrDefault())->toBe('Custom Alt Text');
    }

    public function test_get_alt_text_or_default_generates_default_from_product_name(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'alt_text' => null,
        ]);

        // Load the relationship to test the default generation
        $image->load('product');

        expect($image->getAltTextOrDefault())->toBe('Test Product image');
    }

    public function test_get_alt_text_or_default_returns_generic_default_when_no_product(): void
    {
        $image = new ProductImage;
        $image->alt_text = null;

        expect($image->getAltTextOrDefault())->toBe('Product image');
    }

    public function test_url_accessor_returns_valid_url(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => 'product-images/test.jpg',
        ]);

        expect($image->url)->toBeString();
        expect($image->url)->toContain('test.jpg');
    }

    public function test_url_accessor_handles_absolute_urls(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => 'https://example.com/image.jpg',
        ]);

        expect($image->url)->toBe('https://example.com/image.jpg');
    }

    public function test_url_accessor_handles_relative_paths(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => '/images/product.jpg',
        ]);

        expect($image->url)->toContain('images/product.jpg');
    }

    public function test_full_path_accessor_returns_storage_path(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => 'product-images/test.jpg',
        ]);

        expect($image->full_path)->toContain('storage');
        expect($image->full_path)->toContain('product-images/test.jpg');
    }

    public function test_exists_on_disk_accessor_checks_file_existence(): void
    {
        // Set the default filesystem to the fake public disk
        config(['filesystems.default' => 'public']);

        $path = 'product-images/test.jpg';

        // Test non-existent file
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'path' => $path,
        ]);

        expect($image->exists_on_disk)->toBeFalse();

        // Test existing file
        Storage::disk('public')->put($path, 'test content');

        // Verify file was actually created on the fake disk
        $this->assertTrue(Storage::disk('public')->exists($path));

        // Create a new instance to check the file existence
        $imageWithFile = ProductImage::find($image->id);

        expect($imageWithFile->exists_on_disk)->toBeTrue();
    }

    public function test_factory_creates_valid_image(): void
    {
        $image = ProductImage::factory()->create();

        expect($image)->toBeInstanceOf(ProductImage::class);
        expect($image->product_id)->toBeInt();
        expect($image->path)->toBeString();
        expect($image->sort_order)->toBeInt();
    }

    public function test_factory_main_state_creates_main_image(): void
    {
        $image = ProductImage::factory()->main()->create();

        expect($image->sort_order)->toBe(1);
        expect($image->alt_text)->toContain('Main');
    }

    public function test_factory_thumbnail_state_creates_thumbnail(): void
    {
        $image = ProductImage::factory()->thumbnail()->create();

        expect($image->sort_order)->toBe(0);
        expect($image->alt_text)->toContain('Thumbnail');
    }

    public function test_can_mass_assign_allowed_attributes(): void
    {
        $image = ProductImage::create([
            'product_id' => $this->product->id,
            'path' => 'test/path.jpg',
            'alt_text' => 'Test',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        expect($image->product_id)->toBe($this->product->id);
        expect($image->path)->toBe('test/path.jpg');
        expect($image->alt_text)->toBe('Test');
        expect($image->sort_order)->toBe(5);
        expect($image->is_active)->toBeTrue();
    }

    public function test_uses_correct_table_name(): void
    {
        $image = new ProductImage;

        expect($image->getTable())->toBe('product_images');
    }

    public function test_has_timestamps(): void
    {
        $image = ProductImage::factory()->create();

        expect($image->created_at)->not->toBeNull();
        expect($image->updated_at)->not->toBeNull();
    }

    public function test_can_update_image_attributes(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'alt_text' => 'Original',
            'sort_order' => 1,
        ]);

        $image->update([
            'alt_text' => 'Updated',
            'sort_order' => 5,
        ]);

        expect($image->fresh()->alt_text)->toBe('Updated');
        expect($image->fresh()->sort_order)->toBe(5);
    }

    public function test_can_delete_image(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $imageId = $image->id;
        $image->delete();

        expect(ProductImage::find($imageId))->toBeNull();
    }

    public function test_cascade_delete_when_product_is_deleted(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $imageId = $image->id;
        $this->product->forceDelete();  // Use forceDelete because Product uses SoftDeletes

        expect(ProductImage::withoutGlobalScopes()->find($imageId))->toBeNull();
    }
}
