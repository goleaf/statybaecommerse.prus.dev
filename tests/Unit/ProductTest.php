<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Attribute;
use App\Models\Document;
use App\Models\Translations\ProductTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
        ]);

        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('TEST-001', $product->sku);
        $this->assertEquals(99.99, $product->price);
    }

    public function test_product_has_brand_relationship(): void
    {
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $product = Product::factory()->create(['brand_id' => $brand->id]);

        $this->assertInstanceOf(Brand::class, $product->brand);
        $this->assertEquals('Test Brand', $product->brand->name);
    }

    public function test_product_has_categories_relationship(): void
    {
        $product = Product::factory()->create();
        $category1 = Category::factory()->create(['name' => 'Category 1']);
        $category2 = Category::factory()->create(['name' => 'Category 2']);

        $product->categories()->attach([$category1->id, $category2->id]);

        $this->assertCount(2, $product->categories);
        $this->assertTrue($product->categories->contains($category1));
        $this->assertTrue($product->categories->contains($category2));
    }

    public function test_product_has_collections_relationship(): void
    {
        $product = Product::factory()->create();
        $collection1 = Collection::factory()->create(['name' => 'Collection 1']);
        $collection2 = Collection::factory()->create(['name' => 'Collection 2']);

        $product->collections()->attach([$collection1->id, $collection2->id]);

        $this->assertCount(2, $product->collections);
        $this->assertTrue($product->collections->contains($collection1));
        $this->assertTrue($product->collections->contains($collection2));
    }

    public function test_product_has_images_relationship(): void
    {
        $product = Product::factory()->create();
        $image1 = ProductImage::factory()->create(['product_id' => $product->id]);
        $image2 = ProductImage::factory()->create(['product_id' => $product->id]);

        $this->assertCount(2, $product->images);
        $this->assertTrue($product->images->contains($image1));
        $this->assertTrue($product->images->contains($image2));
    }

    public function test_product_has_variants_relationship(): void
    {
        $product = Product::factory()->create(['type' => 'variable']);
        $variant1 = ProductVariant::factory()->create(['product_id' => $product->id]);
        $variant2 = ProductVariant::factory()->create(['product_id' => $product->id]);

        $this->assertCount(2, $product->variants);
        $this->assertTrue($product->variants->contains($variant1));
        $this->assertTrue($product->variants->contains($variant2));
    }

    public function test_product_has_reviews_relationship(): void
    {
        $product = Product::factory()->create();
        $review1 = Review::factory()->create(['product_id' => $product->id]);
        $review2 = Review::factory()->create(['product_id' => $product->id]);

        $this->assertCount(2, $product->reviews);
        $this->assertTrue($product->reviews->contains($review1));
        $this->assertTrue($product->reviews->contains($review2));
    }

    public function test_product_has_attributes_relationship(): void
    {
        $product = Product::factory()->create();
        $attribute1 = Attribute::factory()->create(['name' => 'Color']);
        $attribute2 = Attribute::factory()->create(['name' => 'Size']);

        $product->attributes()->attach([
            $attribute1->id => ['value' => 'Red'],
            $attribute2->id => ['value' => 'Large'],
        ]);

        $this->assertCount(2, $product->attributes);
        $this->assertTrue($product->attributes->contains($attribute1));
        $this->assertTrue($product->attributes->contains($attribute2));
    }

    public function test_product_has_documents_relationship(): void
    {
        $product = Product::factory()->create();
        $document1 = Document::factory()->create([
            'documentable_type' => Product::class,
            'documentable_id' => $product->id,
        ]);
        $document2 = Document::factory()->create([
            'documentable_type' => Product::class,
            'documentable_id' => $product->id,
        ]);

        $this->assertCount(2, $product->documents);
        $this->assertTrue($product->documents->contains($document1));
        $this->assertTrue($product->documents->contains($document2));
    }

    public function test_product_has_translations_relationship(): void
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $translation = ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'locale' => 'en',
            'name' => 'Test Product EN',
        ]);

        $this->assertCount(1, $product->translations);
        $this->assertTrue($product->translations->contains($translation));
    }

    public function test_product_is_published_scope(): void
    {
        $publishedProduct = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $draftProduct = Product::factory()->create([
            'is_visible' => false,
            'status' => 'draft',
            'published_at' => null,
        ]);

        $publishedProducts = Product::published()->get();

        $this->assertTrue($publishedProducts->contains($publishedProduct));
        $this->assertFalse($publishedProducts->contains($draftProduct));
    }

    public function test_product_is_featured_scope(): void
    {
        $featuredProduct = Product::factory()->create(['is_featured' => true]);
        $regularProduct = Product::factory()->create(['is_featured' => false]);

        $featuredProducts = Product::featured()->get();

        $this->assertTrue($featuredProducts->contains($featuredProduct));
        $this->assertFalse($featuredProducts->contains($regularProduct));
    }

    public function test_product_is_visible_scope(): void
    {
        $visibleProduct = Product::factory()->create(['is_visible' => true]);
        $hiddenProduct = Product::factory()->create(['is_visible' => false]);

        $visibleProducts = Product::visible()->get();

        $this->assertTrue($visibleProducts->contains($visibleProduct));
        $this->assertFalse($visibleProducts->contains($hiddenProduct));
    }

    public function test_product_by_brand_scope(): void
    {
        $brand = Brand::factory()->create();
        $product1 = Product::factory()->create(['brand_id' => $brand->id]);
        $product2 = Product::factory()->create(['brand_id' => null]);

        $brandProducts = Product::byBrand($brand->id)->get();

        $this->assertTrue($brandProducts->contains($product1));
        $this->assertFalse($brandProducts->contains($product2));
    }

    public function test_product_by_category_scope(): void
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $product1->categories()->attach($category->id);

        $categoryProducts = Product::byCategory($category->id)->get();

        $this->assertTrue($categoryProducts->contains($product1));
        $this->assertFalse($categoryProducts->contains($product2));
    }

    public function test_product_in_stock_scope(): void
    {
        $inStockProduct = Product::factory()->create(['stock_quantity' => 10]);
        $outOfStockProduct = Product::factory()->create(['stock_quantity' => 0]);

        $inStockProducts = Product::inStock()->get();

        $this->assertTrue($inStockProducts->contains($inStockProduct));
        $this->assertFalse($inStockProducts->contains($outOfStockProduct));
    }

    public function test_product_low_stock_scope(): void
    {
        $lowStockProduct = Product::factory()->create([
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);
        $normalStockProduct = Product::factory()->create([
            'stock_quantity' => 20,
            'low_stock_threshold' => 10,
        ]);

        $lowStockProducts = Product::lowStock()->get();

        $this->assertTrue($lowStockProducts->contains($lowStockProduct));
        $this->assertFalse($lowStockProducts->contains($normalStockProduct));
    }

    public function test_product_is_published_method(): void
    {
        $publishedProduct = Product::factory()->create([
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $unpublishedProduct = Product::factory()->create([
            'is_visible' => false,
            'published_at' => now()->addDay(),
        ]);

        $this->assertTrue($publishedProduct->isPublished());
        $this->assertFalse($unpublishedProduct->isPublished());
    }

    public function test_product_is_in_stock_method(): void
    {
        $inStockProduct = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 10,
        ]);

        $outOfStockProduct = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 0,
        ]);

        $noStockManagementProduct = Product::factory()->create([
            'manage_stock' => false,
            'stock_quantity' => 0,
        ]);

        $this->assertTrue($inStockProduct->isInStock());
        $this->assertFalse($outOfStockProduct->isInStock());
        $this->assertTrue($noStockManagementProduct->isInStock());
    }

    public function test_product_is_low_stock_method(): void
    {
        $lowStockProduct = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);

        $normalStockProduct = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 20,
            'low_stock_threshold' => 10,
        ]);

        $noStockManagementProduct = Product::factory()->create([
            'manage_stock' => false,
            'stock_quantity' => 0,
        ]);

        $this->assertTrue($lowStockProduct->isLowStock());
        $this->assertFalse($normalStockProduct->isLowStock());
        $this->assertFalse($noStockManagementProduct->isLowStock());
    }

    public function test_product_get_stock_status_method(): void
    {
        $inStockProduct = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 20,
            'low_stock_threshold' => 10,
        ]);

        $lowStockProduct = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);

        $outOfStockProduct = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 0,
        ]);

        $noStockManagementProduct = Product::factory()->create([
            'manage_stock' => false,
        ]);

        $this->assertEquals('in_stock', $inStockProduct->getStockStatus());
        $this->assertEquals('low_stock', $lowStockProduct->getStockStatus());
        $this->assertEquals('out_of_stock', $outOfStockProduct->getStockStatus());
        $this->assertEquals('not_tracked', $noStockManagementProduct->getStockStatus());
    }

    public function test_product_decrease_stock_method(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 10,
        ]);

        $result = $product->decreaseStock(5);
        $product->refresh();

        $this->assertTrue($result);
        $this->assertEquals(5, $product->stock_quantity);

        $result = $product->decreaseStock(10);
        $product->refresh();

        $this->assertFalse($result);
        $this->assertEquals(5, $product->stock_quantity);
    }

    public function test_product_increase_stock_method(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 10,
        ]);

        $product->increaseStock(5);
        $product->refresh();

        $this->assertEquals(15, $product->stock_quantity);
    }

    public function test_product_available_quantity_method(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 10,
        ]);

        $noStockManagementProduct = Product::factory()->create([
            'manage_stock' => false,
        ]);

        $this->assertEquals(10, $product->availableQuantity());
        $this->assertEquals(999, $noStockManagementProduct->availableQuantity());
    }

    public function test_product_is_out_of_stock_method(): void
    {
        $inStockProduct = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 10,
        ]);

        $outOfStockProduct = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 0,
        ]);

        $this->assertFalse($inStockProduct->isOutOfStock());
        $this->assertTrue($outOfStockProduct->isOutOfStock());
    }

    public function test_product_is_variant_method(): void
    {
        $simpleProduct = Product::factory()->create(['type' => 'simple']);
        $variableProduct = Product::factory()->create(['type' => 'variable']);

        $this->assertFalse($simpleProduct->isVariant());
        $this->assertTrue($variableProduct->isVariant());
    }

    public function test_product_has_variants_method(): void
    {
        $simpleProduct = Product::factory()->create(['type' => 'simple']);
        $variableProduct = Product::factory()->create(['type' => 'variable']);
        ProductVariant::factory()->create(['product_id' => $variableProduct->id]);

        $this->assertFalse($simpleProduct->hasVariants());
        $this->assertTrue($variableProduct->hasVariants());
    }

    public function test_product_get_average_rating_attribute(): void
    {
        $product = Product::factory()->create();
        Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 4,
            'is_approved' => true,
        ]);
        Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
            'is_approved' => true,
        ]);

        $this->assertEquals(4.5, $product->average_rating);
    }

    public function test_product_get_reviews_count_attribute(): void
    {
        $product = Product::factory()->create();
        Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => true,
        ]);
        Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => false,
        ]);

        $this->assertEquals(1, $product->reviews_count);
    }

    public function test_product_get_related_products_method(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        $product = Product::factory()->create(['brand_id' => $brand->id]);
        $product->categories()->attach($category->id);

        $relatedProduct1 = Product::factory()->create(['brand_id' => $brand->id]);
        $relatedProduct2 = Product::factory()->create();
        $relatedProduct2->categories()->attach($category->id);

        $relatedProducts = $product->getRelatedProducts(2);

        $this->assertCount(2, $relatedProducts);
        $this->assertTrue($relatedProducts->contains($relatedProduct1));
        $this->assertTrue($relatedProducts->contains($relatedProduct2));
    }

    public function test_product_translation_methods(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);

        $translation = ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'locale' => 'en',
            'name' => 'English Name',
            'description' => 'English Description',
        ]);

        $this->assertEquals('English Name', $product->getTranslatedName('en'));
        $this->assertEquals('English Description', $product->getTranslatedDescription('en'));
        $this->assertEquals('Original Name', $product->getTranslatedName('lt'));
        $this->assertEquals('Original Description', $product->getTranslatedDescription('lt'));
    }

    public function test_product_has_translation_for_method(): void
    {
        $product = Product::factory()->create();
        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'locale' => 'en',
        ]);

        $this->assertTrue($product->hasTranslationFor('en'));
        $this->assertFalse($product->hasTranslationFor('fr'));
    }

    public function test_product_get_or_create_translation_method(): void
    {
        $product = Product::factory()->create(['name' => 'Original Name']);

        $translation = $product->getOrCreateTranslation('en');

        $this->assertInstanceOf(ProductTranslation::class, $translation);
        $this->assertEquals('en', $translation->locale);
        $this->assertEquals('Original Name', $translation->name);
    }

    public function test_product_update_translation_method(): void
    {
        $product = Product::factory()->create();
        $translation = ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'locale' => 'en',
        ]);

        $result = $product->updateTranslation('en', ['name' => 'Updated Name']);

        $this->assertTrue($result);
        $translation->refresh();
        $this->assertEquals('Updated Name', $translation->name);
    }

    public function test_product_delete_translation_method(): void
    {
        $product = Product::factory()->create();
        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'locale' => 'en',
        ]);

        $result = $product->deleteTranslation('en');

        $this->assertTrue($result);
        $this->assertFalse($product->hasTranslationFor('en'));
    }
}