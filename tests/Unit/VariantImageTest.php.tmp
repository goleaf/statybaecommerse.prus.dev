<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VariantImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productVariant = ProductVariant::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_can_create_variant_image(): void
    {
        $variantImage = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'image_path' => 'test-image.jpg',
            'alt_text' => 'Test Image',
            'sort_order' => 1,
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(VariantImage::class, $variantImage);
        $this->assertEquals($this->productVariant->id, $variantImage->variant_id);
        $this->assertEquals('test-image.jpg', $variantImage->image_path);
        $this->assertEquals('Test Image', $variantImage->alt_text);
        $this->assertEquals(1, $variantImage->sort_order);
        $this->assertTrue($variantImage->is_primary);
        $this->assertTrue($variantImage->is_active);
    }

    public function test_belongs_to_variant(): void
    {
        $variantImage = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
        ]);

        $this->assertInstanceOf(ProductVariant::class, $variantImage->variant);
        $this->assertEquals($this->productVariant->id, $variantImage->variant->id);
    }

    public function test_can_replicate_variant_image(): void
    {
        $originalImage = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_primary' => true,
        ]);

        $replicatedImage = $originalImage->replicate();
        $replicatedImage->is_primary = false;
        $replicatedImage->save();

        $this->assertNotEquals($originalImage->id, $replicatedImage->id);
        $this->assertEquals($originalImage->variant_id, $replicatedImage->variant_id);
        $this->assertEquals($originalImage->image_path, $replicatedImage->image_path);
        $this->assertEquals($originalImage->alt_text, $replicatedImage->alt_text);
        $this->assertFalse($replicatedImage->is_primary);
    }

    public function test_can_scope_primary_images(): void
    {
        // Create primary and non-primary images
        VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_primary' => true,
        ]);

        VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_primary' => false,
        ]);

        $primaryImages = VariantImage::primary()->get();

        $this->assertCount(1, $primaryImages);
        $this->assertTrue($primaryImages->first()->is_primary);
    }

    public function test_can_scope_active_images(): void
    {
        // Create active and inactive images
        VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_active' => true,
        ]);

        VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_active' => false,
        ]);

        $activeImages = VariantImage::active()->get();

        $this->assertCount(1, $activeImages);
        $this->assertTrue($activeImages->first()->is_active);
    }

    public function test_can_scope_by_variant(): void
    {
        $anotherVariant = ProductVariant::factory()->create();

        // Create images for different variants
        VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
        ]);

        VariantImage::factory()->create([
            'variant_id' => $anotherVariant->id,
        ]);

        $variantImages = VariantImage::forVariant($this->productVariant->id)->get();

        $this->assertCount(1, $variantImages);
        $this->assertEquals($this->productVariant->id, $variantImages->first()->variant_id);
    }

    public function test_can_order_by_sort_order(): void
    {
        // Create images with different sort orders
        $image3 = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'sort_order' => 3,
        ]);

        $image1 = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'sort_order' => 1,
        ]);

        $image2 = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'sort_order' => 2,
        ]);

        $orderedImages = VariantImage::orderBySortOrder()->get();

        $this->assertEquals($image1->id, $orderedImages[0]->id);
        $this->assertEquals($image2->id, $orderedImages[1]->id);
        $this->assertEquals($image3->id, $orderedImages[2]->id);
    }

    public function test_can_get_image_url(): void
    {
        $variantImage = VariantImage::factory()->create([
            'image_path' => 'variant-images/test-image.jpg',
        ]);

        $expectedUrl = asset('storage/variant-images/test-image.jpg');
        $this->assertEquals($expectedUrl, $variantImage->image_url);
    }

    public function test_can_get_file_size_formatted(): void
    {
        $variantImage = VariantImage::factory()->create([
            'file_size' => 1024,  // 1KB
        ]);

        $this->assertEquals('1.00 KB', $variantImage->formatted_file_size);
    }

    public function test_can_get_dimensions_formatted(): void
    {
        $variantImage = VariantImage::factory()->create([
            'dimensions' => '800x600',
        ]);

        $this->assertEquals('800 × 600', $variantImage->formatted_dimensions);
    }

    public function test_can_check_if_image_exists(): void
    {
        Storage::fake('public');

        $variantImage = VariantImage::factory()->create([
            'image_path' => 'variant-images/test-image.jpg',
        ]);

        // Image doesn't exist yet
        $this->assertFalse($variantImage->image_exists);

        // Create the file
        Storage::disk('public')->put('variant-images/test-image.jpg', 'fake content');

        $this->assertTrue($variantImage->image_exists);
    }

    public function test_can_get_image_metadata(): void
    {
        $variantImage = VariantImage::factory()->create([
            'file_size' => 2048,
            'dimensions' => '1200x800',
        ]);

        $metadata = $variantImage->getImageMetadata();

        $this->assertArrayHasKey('file_size', $metadata);
        $this->assertArrayHasKey('dimensions', $metadata);
        $this->assertEquals(2048, $metadata['file_size']);
        $this->assertEquals('1200x800', $metadata['dimensions']);
    }

    public function test_can_mark_as_primary(): void
    {
        $variantImage = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_primary' => false,
        ]);

        $variantImage->markAsPrimary();

        $this->assertTrue($variantImage->fresh()->is_primary);
    }

    public function test_can_unmark_as_primary(): void
    {
        $variantImage = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_primary' => true,
        ]);

        $variantImage->unmarkAsPrimary();

        $this->assertFalse($variantImage->fresh()->is_primary);
    }

    public function test_can_activate(): void
    {
        $variantImage = VariantImage::factory()->create([
            'is_active' => false,
        ]);

        $variantImage->activate();

        $this->assertTrue($variantImage->fresh()->is_active);
    }

    public function test_can_deactivate(): void
    {
        $variantImage = VariantImage::factory()->create([
            'is_active' => true,
        ]);

        $variantImage->deactivate();

        $this->assertFalse($variantImage->fresh()->is_active);
    }

    public function test_can_get_next_sort_order(): void
    {
        // Create images with different sort orders
        VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'sort_order' => 1,
        ]);

        VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'sort_order' => 3,
        ]);

        $nextSortOrder = VariantImage::getNextSortOrder($this->productVariant->id);

        $this->assertEquals(4, $nextSortOrder);
    }

    public function test_can_reorder_images(): void
    {
        // Create images with specific sort orders
        $image1 = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'sort_order' => 1,
        ]);

        $image2 = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'sort_order' => 2,
        ]);

        $image3 = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'sort_order' => 3,
        ]);

        // Reorder: move image3 to position 1
        VariantImage::reorderImages($this->productVariant->id, [
            $image3->id => 1,
            $image1->id => 2,
            $image2->id => 3,
        ]);

        $this->assertEquals(1, $image3->fresh()->sort_order);
        $this->assertEquals(2, $image1->fresh()->sort_order);
        $this->assertEquals(3, $image2->fresh()->sort_order);
    }

    public function test_can_get_primary_image_for_variant(): void
    {
        // Create primary and non-primary images
        $primaryImage = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_primary' => true,
        ]);

        VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_primary' => false,
        ]);

        $primary = VariantImage::getPrimaryForVariant($this->productVariant->id);

        $this->assertInstanceOf(VariantImage::class, $primary);
        $this->assertEquals($primaryImage->id, $primary->id);
        $this->assertTrue($primary->is_primary);
    }

    public function test_can_get_all_images_for_variant(): void
    {
        // Create multiple images for the variant
        $images = VariantImage::factory()->count(3)->create([
            'variant_id' => $this->productVariant->id,
        ]);

        $variantImages = VariantImage::getAllForVariant($this->productVariant->id);

        $this->assertCount(3, $variantImages);
        $this->assertTrue($variantImages->every(fn ($image) => $image->variant_id === $this->productVariant->id));
    }

    public function test_can_count_images_for_variant(): void
    {
        // Create multiple images for the variant
        VariantImage::factory()->count(5)->create([
            'variant_id' => $this->productVariant->id,
        ]);

        $count = VariantImage::countForVariant($this->productVariant->id);

        $this->assertEquals(5, $count);
    }

    public function test_can_soft_delete_variant_image(): void
    {
        $variantImage = VariantImage::factory()->create();

        $variantImage->delete();

        $this->assertSoftDeleted('variant_images', [
            'id' => $variantImage->id,
        ]);
    }

    public function test_can_restore_soft_deleted_variant_image(): void
    {
        $variantImage = VariantImage::factory()->create();
        $variantImage->delete();

        $variantImage->restore();

        $this->assertDatabaseHas('variant_images', [
            'id' => $variantImage->id,
            'deleted_at' => null,
        ]);
    }
}
