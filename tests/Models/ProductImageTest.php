<?php declare(strict_types=1);

namespace Tests\Models;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_configuration_exposes_expected_fillable_casts_and_defaults(): void
    {
        // Instantiate the model to inspect its configuration without hitting the database.
        $model = new ProductImage();

        // Confirm the table is explicitly configured for clarity when testing joins.
        self::assertSame('product_images', $model->getTable());

        // Validate the fillable attributes to prevent accidental mass-assignment vulnerabilities.
        self::assertSame([
            'product_id',
            'path',
            'alt_text',
            'sort_order',
            'is_active',
        ], $model->getFillable());

        // Ensure the casts map the important attributes to strong native types.
        self::assertSame([
            'product_id' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ], $model->getCasts());

        // Check that images default to the active state when the attribute is not provided.
        self::assertTrue((bool) $model->getAttribute('is_active'));
    }

    public function test_product_relationship_returns_parent_model(): void
    {
        // Create a product and a related image to verify the belongs-to association.
        $product = Product::factory()->create();
        $image = ProductImage::factory()->for($product, 'product')->create();

        // Assert that the relationship resolves the owning product instance.
        self::assertTrue($product->is($image->product));
    }

    public function test_url_attribute_leverages_storage_urls_for_local_files(): void
    {
        // Fake the public disk so we can control the file system expectations.
        Storage::fake('public');
        config()->set('filesystems.default', 'public');

        // Seed a file on disk that the accessor should resolve to a URL.
        $path = 'product-images/example.jpg';
        Storage::disk('public')->put($path, 'image-contents');

        // Persist the model using the same path so that the accessor under test is exercised.
        $image = ProductImage::factory()->create(['path' => $path]);

        // The accessor should call back into the storage disk to build the public URL.
        self::assertSame(Storage::disk('public')->url($path), $image->fresh()->url);
    }

    public function test_url_attribute_preserves_absolute_urls(): void
    {
        // Create an image pointing at an existing CDN-style URL to ensure it is untouched.
        $absoluteUrl = 'https://cdn.example.test/assets/image.jpg';
        $image = ProductImage::factory()->create(['path' => $absoluteUrl]);

        // The accessor should not alter already absolute URLs when returning the value.
        self::assertSame($absoluteUrl, $image->fresh()->url);
    }

    public function test_full_path_attribute_returns_storage_location(): void
    {
        // Build an image instance without persisting it to inspect the computed attribute.
        $image = ProductImage::factory()->make(['path' => 'product-images/foo.jpg']);

        // The attribute should prefix the storage_path with the public disk directory.
        self::assertSame(storage_path('app/public/product-images/foo.jpg'), $image->full_path);
    }

    public function test_exists_on_disk_attribute_checks_configured_disk(): void
    {
        // Configure and fake the disk to control the existence checks performed by the accessor.
        Storage::fake('public');
        config()->set('filesystems.default', 'public');

        // Write a file to disk that matches the stored path so we can assert a positive check.
        $path = 'product-images/on-disk.jpg';
        Storage::disk('public')->put($path, 'image-contents');

        // Create the image pointing at the stored file so the accessor should report true.
        $image = ProductImage::factory()->create(['path' => $path]);
        self::assertTrue($image->fresh()->exists_on_disk);

        // Flip the path to a missing file and confirm the accessor reflects the absence.
        $missing = $image->fresh();
        $missing->path = 'product-images/missing.jpg';
        self::assertFalse($missing->exists_on_disk);
    }

    public function test_scopes_chain_for_active_product_and_ordering_filters(): void
    {
        // Create products that allow filtering the images by ownership.
        $product = Product::factory()->create();
        $otherProduct = Product::factory()->create();

        // Seed images with a mix of activity states and sort orders for the scope tests.
        $first = ProductImage::factory()->for($product, 'product')->create(['sort_order' => 1]);
        $second = ProductImage::factory()->for($product, 'product')->create(['sort_order' => 5]);
        ProductImage::factory()->for($product, 'product')->create([
            'is_active' => false,
            'sort_order' => 2,
        ]);
        ProductImage::factory()->for($otherProduct, 'product')->create(['sort_order' => 0]);

        // Chain the custom scopes and ensure we only receive the active images for the target product.
        $scopedIds = ProductImage::query()
            ->active()
            ->forProduct($product->getKey())
            ->ordered()
            ->pluck('id')
            ->all();

        // Confirm that the ordered scope respects the sort_order and id fallback for deterministic ordering.
        self::assertSame([$first->getKey(), $second->getKey()], $scopedIds);
    }

    public function test_primary_scope_limits_to_first_ordered_image(): void
    {
        // Create a product and populate multiple images with known ordering.
        $product = Product::factory()->create();
        $first = ProductImage::factory()->for($product, 'product')->create(['sort_order' => 1]);
        ProductImage::factory()->for($product, 'product')->create(['sort_order' => 3]);

        // Execute the primary scope which should return only the leading image in the ordered list.
        $primaryId = ProductImage::query()
            ->forProduct($product->getKey())
            ->primary()
            ->value('id');

        // Validate that the scope targets the expected identifier.
        self::assertSame($first->getKey(), $primaryId);
    }

    public function test_is_primary_detects_first_image_and_zero_sort_order(): void
    {
        // Create a product to share between the test fixtures.
        $product = Product::factory()->create();

        // Seed an image that leads the ordering despite not having a zero sort order.
        $leading = ProductImage::factory()->for($product, 'product')->create(['sort_order' => 2]);
        ProductImage::factory()->for($product, 'product')->create(['sort_order' => 5]);

        // Persist another image with an explicit zero sort order to test the short-circuit branch.
        $zeroSort = ProductImage::factory()->for($product, 'product')->create(['sort_order' => 0]);

        // The leading image should evaluate as primary because it appears first in the ordered scope.
        self::assertTrue($leading->fresh()->isPrimary());

        // The zero sort order image should also report as primary even without considering ordering queries.
        self::assertTrue($zeroSort->fresh()->isPrimary());
    }

    public function test_alt_text_accessor_falls_back_to_product_or_default_copy(): void
    {
        // Build a product with a recognizable name to use in the accessor output.
        $product = Product::factory()->create(['name' => 'Granite Tile']);

        // Create an image that contains explicit alt text so the accessor should surface the stored value.
        $withAlt = ProductImage::factory()->for($product, 'product')->create(['alt_text' => 'Custom alt text']);
        self::assertSame('Custom alt text', $withAlt->getAltTextOrDefault());

        // Create an image without alt text but ensure the relation is loaded for the product-specific fallback.
        $withoutAlt = ProductImage::factory()->for($product, 'product')->create(['alt_text' => null]);
        $withoutAlt->setRelation('product', $product);
        self::assertSame('Granite Tile image', $withoutAlt->getAltTextOrDefault());

        // Evaluate the generic fallback when no alt text or relation data is available.
        $standalone = ProductImage::factory()->make(['alt_text' => null]);
        self::assertSame('Product image', $standalone->getAltTextOrDefault());
    }
}
